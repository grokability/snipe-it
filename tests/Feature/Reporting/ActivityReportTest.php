<?php

namespace Tests\Feature\Reporting;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ActivityReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_requires_permission_to_view_activity()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.activity.index'))
            ->assertForbidden();
    }

    public function test_can_view_activity_if_item_is_given_and_user_has_permissions()
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.activity.index',
                [
                    'item_type' => 'asset',
                    'item_id' => $asset->id,
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    public function test_can_view_activity_if_target_is_given_and_user_has_permissions()
    {

        $user = User::factory()->create();
        $user->update([
            'first_name' => 'Test Update',
        ]);
        $user->update([
            'first_name' => 'Test Update Again',
        ]);

        $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.activity.index',
                [
                    'target_type' => 'user',
                    'target_id' => $user->id,
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
    }

    public function test_null_company_upload_logs_visible_in_activity_report_with_fmcs_enabled()
    {
        // AssetModel and Company objects have no company_id column, so their upload logs always
        // get company_id = null. With FMCS active the scope previously applied
        // WHERE company_id IN (...) which excluded NULLs, hiding these logs from the activity report.
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $superUser = User::factory()->superuser()->create();

        $viewingUser = User::factory()
            ->canViewReports()
            ->forCompany($company)
            ->create();

        $model = AssetModel::factory()->create();

        // Superuser uploads a file to the AssetModel (log gets company_id = null)
        $this->actingAsForApi($superUser)
            ->post(
                route('api.files.store', ['object_type' => 'models', 'id' => $model->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();

        // Non-superuser with activity.view (reports.view) should see the uploaded log
        $this->actingAsForApi($viewingUser)
            ->getJson(route('api.activity.index', [
                'action_type' => 'uploaded',
                'item_type' => 'AssetModel',
                'item_id' => $model->id,
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    public function test_upload_logs_for_another_companys_asset_not_visible_in_activity_report_with_fmcs()
    {
        // Our null-company fix adds OR company_id IS NULL to action_log queries.
        // Verify this does NOT leak logs that have a real company_id belonging to a different company.
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $superUser = User::factory()->superuser()->create();
        $assetInCompanyA = Asset::factory()->create(['company_id' => $companyA->id]);

        $viewerInCompanyB = User::factory()
            ->canViewReports()
            ->forCompany($companyB)
            ->create();

        // Superuser uploads a file to company A's asset (log gets company_id = companyA->id)
        $this->actingAsForApi($superUser)
            ->post(
                route('api.files.store', ['object_type' => 'hardware', 'id' => $assetInCompanyA->id]),
                ['file' => [UploadedFile::fake()->create('test.jpg', 100)]]
            )
            ->assertOk();

        // User in company B should not see the upload log for company A's asset
        $this->actingAsForApi($viewerInCompanyB)
            ->getJson(route('api.activity.index', [
                'action_type' => 'uploaded',
                'item_type' => 'asset',
                'item_id' => $assetInCompanyA->id,
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());
    }

    public function test_records_are_scoped_to_company_when_multiple_company_support_enabled()
    {
        // $this->markTestIncomplete('This test returns strange results. Need to figure out why.');
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $superUser = User::factory()->superuser()->make();

        $userInCompanyA = User::factory()
            ->viewUsers()
            ->viewAssets()
            ->canViewReports()
            ->forCompany($companyA)
            ->create();

        $userInCompanyB = User::factory()
            ->viewUsers()
            ->viewAssets()
            ->canViewReports()
            ->forCompany($companyB)
            ->create();

        Asset::factory()->count(5)->create(['company_id' => $companyA->id]);
        Asset::factory()->count(4)->create(['company_id' => $companyB->id]);
        Asset::factory()->count(3)->create();

        Actionlog::factory()->userUpdated()->count(5)->create(['company_id' => $companyA->id]);
        Actionlog::factory()->userUpdated()->count(4)->create(['company_id' => $companyB->id]);
        Actionlog::factory()->userUpdated()->count(3)->create(['company_id' => $companyB->id]);

        // I don't love this, since it doesn't test that we're actually storing the company ID appropriately
        // but it's better than what we had
        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.activity.index', [
                'action_type' => 'update',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());

        $this->actingAsForApi($userInCompanyB)
            ->getJson(
                route('api.activity.index', [
                    'action_type' => 'update',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 7)->etc());

    }

    public function test_activity_report_normalizes_lowercase_camelcase_input()
    {
        // Regression test for the pre-existing lowercase-camelcase
        // Fatal Error. Helper::normalizeFullModelName uses ucwords(),
        // which only capitalizes the first letter of each space-
        // delimited word. A lowercase short name like `assetmodel`
        // (which FilterRequest accepts) used to normalize to the
        // nonexistent App\Models\Assetmodel and Fatal when
        // withTrashed()->find() called the class. ActivityItemType's
        // case-insensitive tryFromAny returns the canonical case now,
        // so the request succeeds cleanly.
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.activity.index', [
                'item_type' => 'assetmodel',
                'item_id' => 999999,
            ]))
            ->assertOk();
    }

    public function test_activity_report_rejects_mixed_types_when_caller_lacks_view_on_every_type(): void
    {
        // Regression pin for GHSA-rwxw-wvx8-cx83. Before the fix the
        // per-record fallback authorized only $itemClasses[0], but the
        // query filtered whereIn across every submitted type. A caller
        // holding assets.view (but not users.view or activity.view)
        // could read action logs for user records by prepending `asset`
        // in item_type[]: the authz check saw only Asset, the query
        // returned User rows. Fix walks every resolved class, so a
        // mixed asset+user submission from an assets-only user fails
        // on the User type.
        $victim = User::factory()->create();
        $victim->update(['first_name' => 'Test Update']);
        $attacker = User::factory()->viewAssets()->create();

        $this->actingAsForApi($attacker)
            ->getJson(route('api.activity.index', [
                'item_type' => ['asset', 'user'],
                'item_id' => $victim->id,
            ]))
            ->assertForbidden();
    }

    public function test_activity_report_rejects_mixed_target_types_when_caller_lacks_view_on_every_type(): void
    {
        // Companion to the item_type bypass test. Same root cause
        // against the target_type path: $targetClasses[0] was the
        // only class authorized but the query filtered whereIn on
        // target_type across every submitted class. Fix walks every
        // target_type too.
        $victim = User::factory()->create();
        $victim->update(['first_name' => 'Test Update']);
        $attacker = User::factory()->viewAssets()->create();

        $this->actingAsForApi($attacker)
            ->getJson(route('api.activity.index', [
                'target_type' => ['asset', 'user'],
                'target_id' => $victim->id,
            ]))
            ->assertForbidden();
    }

    public function test_activity_report_rejects_types_not_in_form_request_allowlist()
    {
        // FilterRequest already rejects arbitrary class names, but
        // Snipe-IT returns validation failures as HTTP 200 with body
        // status=error (project convention). Pinning that shape so a
        // refactor that changes either FilterRequest or the response
        // envelope shows up in tests before it ships.
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.activity.index', [
                'item_type' => 'NotARealClass',
                'item_id' => 1,
            ]))
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_sort_by_item_on_activity_report_orders_by_polymorphic_item_name()
    {
        // Issue #17288. The activity report's `item` column is
        // polymorphic across every entity on the install. The sort
        // joins each possible item_type table and COALESCEs their
        // per-type name expressions so rows order alphabetically by
        // the displayed item name regardless of type.
        $actor = User::factory()->superuser()->create();

        $zebra = Asset::factory()->create(['name' => 'Zebra Laptop']);
        $apple = \App\Models\Component::factory()->create(['name' => 'Apple Keyboard']);
        $monkey = \App\Models\License::factory()->create(['name' => 'Monkey License']);

        $zebraLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => $zebra->id,
        ]);
        $appleLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\Component::class,
            'item_id' => $apple->id,
        ]);
        $monkeyLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\License::class,
            'item_id' => $monkey->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'sort' => 'item',
                'order' => 'asc',
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->filter(fn ($id) => in_array($id, [$zebraLog->id, $appleLog->id, $monkeyLog->id]))
            ->values()
            ->all();

        $this->assertSame([$appleLog->id, $monkeyLog->id, $zebraLog->id], $ids);
    }

    public function test_sort_by_item_honors_last_first_name_display_format_for_user_rows()
    {
        // On a last-first install (Hungarian, CJK, etc.) the polymorphic
        // name sort for User rows reads "Doe John" rather than
        // "John Doe", so a user named John Doe orders alphabetically by
        // "D" rather than "J". display_name is user-authored so it ships
        // as-is regardless of locale. #17288 + #19626.
        $this->settings->set(['name_display_format' => 'last_first']);

        $actor = User::factory()->superuser()->create();

        $john = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'display_name' => null,
        ]);
        $apple = \App\Models\Component::factory()->create(['name' => 'Apple Keyboard']);
        $zebra = \App\Models\Component::factory()->create(['name' => 'Zebra Mouse']);

        $userLog = Actionlog::factory()->create([
            'action_type' => 'update',
            'item_type' => User::class,
            'item_id' => $john->id,
        ]);
        $appleLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\Component::class,
            'item_id' => $apple->id,
        ]);
        $zebraLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\Component::class,
            'item_id' => $zebra->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'sort' => 'item',
                'order' => 'asc',
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->filter(fn ($id) => in_array($id, [$userLog->id, $appleLog->id, $zebraLog->id]))
            ->values()
            ->all();

        // Apple Keyboard < Doe John < Zebra Mouse.
        // Without the last_first flip, the user would sort as "John Doe"
        // and land between Apple and Zebra at a different position.
        $this->assertSame([$appleLog->id, $userLog->id, $zebraLog->id], $ids);
    }

    public function test_item_type_filter_without_item_id_narrows_rows_and_uses_single_table_sort()
    {
        // Toolbar type filter: ?item_type=asset with no item_id should
        // return only asset-item rows, and the sort should take the
        // optimized single-table path (fewer joins) rather than the
        // 15-way polymorphic COALESCE. The returned ids are the only
        // assertion we can make here. The join-plan change is covered
        // by not regressing on existing tests. #17288.
        $actor = User::factory()->superuser()->create();

        $assetLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
        ]);
        $componentLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\Component::class,
            'item_id' => \App\Models\Component::factory()->create()->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'item_type' => 'asset',
                'sort' => 'item',
                'order' => 'asc',
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($assetLog->id, $ids);
        $this->assertNotContains($componentLog->id, $ids);
    }

    public function test_target_type_filter_without_target_id_narrows_rows()
    {
        $actor = User::factory()->superuser()->create();

        $userTargetLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'target_type' => User::class,
            'target_id' => User::factory()->create()->id,
        ]);
        $locationTargetLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'target_type' => \App\Models\Location::class,
            'target_id' => \App\Models\Location::factory()->create()->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'target_type' => 'user',
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($userTargetLog->id, $ids);
        $this->assertNotContains($locationTargetLog->id, $ids);
    }

    public function test_item_type_filter_accepts_multiple_values_as_array()
    {
        // Toolbar multi-select sends ?item_type[]=asset&item_type[]=component.
        // Controller whereIn() narrows to rows whose item is of any
        // chosen class. #17288.
        $actor = User::factory()->superuser()->create();

        $assetLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
        ]);
        $componentLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\Component::class,
            'item_id' => \App\Models\Component::factory()->create()->id,
        ]);
        $licenseLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\License::class,
            'item_id' => \App\Models\License::factory()->create()->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'item_type' => ['asset', 'component'],
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($assetLog->id, $ids);
        $this->assertContains($componentLog->id, $ids);
        $this->assertNotContains($licenseLog->id, $ids);
    }

    public function test_action_type_filter_accepts_multiple_values_as_array()
    {
        $actor = User::factory()->superuser()->create();

        $checkoutLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
        ]);
        $auditLog = Actionlog::factory()->create([
            'action_type' => 'audit',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
        ]);
        $createLog = Actionlog::factory()->create([
            'action_type' => 'create',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'action_type' => ['checkout', 'audit'],
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($checkoutLog->id, $ids);
        $this->assertContains($auditLog->id, $ids);
        $this->assertNotContains($createLog->id, $ids);
    }

    public function test_created_by_filter_accepts_multiple_values_as_array()
    {
        $actor = User::factory()->superuser()->create();
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $carol = User::factory()->create();

        $aliceLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_by' => $alice->id,
        ]);
        $bobLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_by' => $bob->id,
        ]);
        $carolLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_by' => $carol->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'created_by' => [$alice->id, $bob->id],
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($aliceLog->id, $ids);
        $this->assertContains($bobLog->id, $ids);
        $this->assertNotContains($carolLog->id, $ids);
    }

    public function test_created_by_filter_narrows_rows_to_rows_by_that_actor()
    {
        // Toolbar created_by filter: ?created_by=X returns only rows
        // whose created_by column matches. #17288.
        $actor = User::factory()->superuser()->create();
        $otherActor = User::factory()->create();

        $actorLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_by' => $otherActor->id,
        ]);
        $noiseLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_by' => User::factory()->create()->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'created_by' => $otherActor->id,
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($actorLog->id, $ids);
        $this->assertNotContains($noiseLog->id, $ids);
    }

    public function test_created_at_date_range_filter_narrows_rows_to_the_window()
    {
        // Toolbar date range: ?created_at_from=Y-m-d gates start-of-day
        // inclusive, ?created_at_to=Y-m-d gates end-of-day inclusive.
        // #17288.
        $actor = User::factory()->superuser()->create();

        $insideLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_at' => '2026-05-10 12:00:00',
        ]);
        $beforeLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_at' => '2026-04-30 23:59:59',
        ]);
        $afterLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'created_at' => '2026-05-16 00:00:00',
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', [
                'created_at_from' => '2026-05-01',
                'created_at_to' => '2026-05-15',
            ]))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($insideLog->id, $ids);
        $this->assertNotContains($beforeLog->id, $ids);
        $this->assertNotContains($afterLog->id, $ids);
    }

    public function test_search_matches_target_asset_tag_on_component_checkout()
    {
        // Component checkouts store item=Component and target=Asset.
        // Actionlog's $searchableRelations only walks item_id, so
        // searching by the target asset's tag missed these rows
        // before the advancedTextSearch() override on Actionlog.
        // Same fix applied here for the general reports.view path.
        // Positive-membership assertion because the built-in
        // item_id-keyed searchRelations may also match the asset's
        // own create log, and both matches are legitimate.
        $actor = User::factory()->superuser()->create();

        $component = \App\Models\Component::factory()->create();
        $targetAsset = Asset::factory()->create(['asset_tag' => 'TARGET-TAG-4242']);

        $matchingLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => \App\Models\Component::class,
            'item_id' => $component->id,
            'target_type' => Asset::class,
            'target_id' => $targetAsset->id,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', ['search' => 'TARGET-TAG-4242']))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($matchingLog->id, $ids);
    }

    public function test_search_matches_action_log_location_name()
    {
        // Activity Report eager-loads and shows the location on each
        // action_log row (e.g. checkouts to a location). Before adding
        // location to Actionlog's $searchableRelations, typing that
        // location's name into the report search silently returned
        // nothing.
        $actor = User::factory()->superuser()->create();

        $location = \App\Models\Location::factory()->create(['name' => 'Kraków Office']);
        $matchingLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'location_id' => $location->id,
        ]);
        $otherLog = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_type' => Asset::class,
            'item_id' => Asset::factory()->create()->id,
            'location_id' => null,
        ]);

        $ids = collect($this->actingAsForApi($actor)
            ->getJson(route('api.activity.index', ['search' => 'Kraków']))
            ->assertOk()
            ->json('rows'))
            ->pluck('id')
            ->all();

        $this->assertContains($matchingLog->id, $ids);
        $this->assertNotContains($otherLog->id, $ids);
    }
}
