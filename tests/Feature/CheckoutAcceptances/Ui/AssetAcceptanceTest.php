<?php

namespace Tests\Feature\CheckoutAcceptances\Ui;

use App\Events\CheckoutAccepted;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\CustomField;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AcceptanceItemAcceptedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssetAcceptanceTest extends TestCase
{
    public function test_asset_checkout_accept_page_renders()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->get(route('account.accept.item', $checkoutAcceptance))
            ->assertViewIs('account.accept.create');
    }

    public function test_cannot_accept_asset_already_accepted()
    {
        Event::fake([CheckoutAccepted::class]);

        $checkoutAcceptance = CheckoutAcceptance::factory()->accepted()->create();

        $this->assertFalse($checkoutAcceptance->isPending());

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'my note',
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('error');

        Event::assertNotDispatched(CheckoutAccepted::class);
    }

    public function test_cannot_accept_asset_for_another_user()
    {
        Event::fake([CheckoutAccepted::class]);

        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->assertTrue($checkoutAcceptance->isPending());

        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'my note',
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('error');

        $this->assertTrue($checkoutAcceptance->fresh()->isPending());

        Event::assertNotDispatched(CheckoutAccepted::class);
    }

    public function test_user_can_accept_asset()
    {
        Event::fake([CheckoutAccepted::class]);

        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->assertTrue($checkoutAcceptance->isPending());

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'my note',
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        $checkoutAcceptance->refresh();

        $this->assertFalse($checkoutAcceptance->isPending());
        $this->assertNotNull($checkoutAcceptance->accepted_at);
        $this->assertNull($checkoutAcceptance->declined_at);

        Event::assertDispatched(CheckoutAccepted::class);
    }

    public function test_user_can_accept_asset_with_required_signature()
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is required for signature image generation.');
        }

        $settings = Setting::query()->firstOrFail();
        $settings->require_accept_signature = 1;
        $settings->save();
        Setting::$_cache = null;

        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $canvas = imagecreatetruecolor(24, 12);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagesavealpha($canvas, true);
        $ink = imagecolorallocate($canvas, 25, 25, 25);
        imageline($canvas, 2, 10, 21, 2, $ink);

        ob_start();
        imagepng($canvas);
        $signaturePng = (string) ob_get_clean();
        imagedestroy($canvas);

        $signatureOutput = 'data:image/png;base64,'.base64_encode($signaturePng);

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'signed in test',
                'signature_output' => $signatureOutput,
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        $checkoutAcceptance->refresh();

        $this->assertNotNull($checkoutAcceptance->signature_filename);
        $this->assertNotNull($checkoutAcceptance->stored_eula_file);
    }

    public function test_user_can_decline_asset()
    {
        Event::fake([CheckoutAccepted::class]);
        $this->settings->disableAlertEmail(); //otherwise it tries to send an email without having enough information

        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->assertTrue($checkoutAcceptance->isPending());

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
                'note' => 'my note',
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        $checkoutAcceptance->refresh();

        $this->assertFalse($checkoutAcceptance->isPending());
        $this->assertNull($checkoutAcceptance->accepted_at);
        $this->assertNotNull($checkoutAcceptance->declined_at);

        Event::assertNotDispatched(CheckoutAccepted::class);
    }

    public function test_action_logged_when_accepting_asset()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'my note',
            ]);

        $this->assertTrue(Actionlog::query()
            ->where([
                'action_type' => 'accepted',
                'target_id' => $checkoutAcceptance->assignedTo->id,
                'target_type' => User::class,
                'note' => 'my note',
                'item_type' => Asset::class,
                'item_id' => $checkoutAcceptance->checkoutable->id,
            ])
            ->whereNotNull('action_date')
            ->exists()
        );
    }

    public function test_action_logged_when_declining_asset()
    {
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
                'note' => 'my note',
            ]);

        $this->assertTrue(Actionlog::query()
            ->where([
                'action_type' => 'declined',
                'target_id' => $checkoutAcceptance->assignedTo->id,
                'target_type' => User::class,
                'note' => 'my note',
                'item_type' => Asset::class,
                'item_id' => $checkoutAcceptance->checkoutable->id,
            ])
            ->whereNotNull('action_date')
            ->exists()
        );
    }

    public function test_acceptance_email_includes_custom_fields_marked_show_in_email_and_not_encrypted(): void
    {
        Event::fake([CheckoutAccepted::class]);
        Notification::fake();
        $this->settings->enableAdminCC();

        $customField = CustomField::factory()->create([
            'name' => 'Cost Center',
            'show_in_email' => '1',
            'field_encrypted' => '0',
        ])->fresh();

        $asset = Asset::factory()->hasMultipleCustomFields([$customField])->create();
        $asset->{$customField->db_column} = 'ENG-42';
        $asset->save();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($asset, 'checkoutable')
            ->create();

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        Notification::assertSentTo(
            $checkoutAcceptance,
            function (AcceptanceItemAcceptedNotification $notification) {
                $rendered = $notification->toMail()->render();

                return str_contains($rendered, 'Cost Center')
                    && str_contains($rendered, 'ENG-42');
            }
        );
    }

    public function test_acceptance_note_cannot_inject_markdown_image_tag(): void
    {
        // Regression for GHSA (F41 / Adam Nurudini). A low-privilege user
        // could submit an acceptance note like `![x](/var/www/html/.env)`,
        // which Blade's HTML escape leaves intact (the chars are not HTML
        // metacharacters). CommonMark then expanded it into a real <img>
        // tag inside the outbound notification, and laravel-mail-auto-embed
        // resolved the src via file_get_contents() or curl, exfiltrating
        // arbitrary local files or issuing SSRF requests. The fix registers
        // a CommonMark extension that overrides the Image renderer so no
        // <img> tag survives markdown parsing.
        Event::fake([CheckoutAccepted::class]);
        Notification::fake();
        $this->settings->enableAdminCC();
        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $lfrPayload = '![x](/etc/hostname)';
        $ssrfPayload = '![y](http://169.254.169.254/latest/meta-data/)';
        $filePayload = '![z](file:///var/www/html/.env)';

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => "{$lfrPayload} {$ssrfPayload} {$filePayload}",
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        Notification::assertSentTo(
            $checkoutAcceptance,
            function (AcceptanceItemAcceptedNotification $notification) {
                $rendered = $notification->toMail()->render();

                // None of the payload targets should survive as an <img src>
                // in the rendered email. laravel-mail-auto-embed resolves any
                // surviving img src via file_get_contents() (LFR) or curl (SSRF).
                // Legitimate header/logo <img> tags with app-controlled src
                // paths are unaffected. This test targets only user-controlled
                // src values that Adam's F41 report demonstrated.
                foreach (['/etc/hostname', '169.254.169.254', '/var/www/html/.env', 'file://'] as $payload) {
                    $this->assertStringNotContainsString(
                        $payload,
                        $rendered,
                        "Rendered mail contains attacker-controlled path \"{$payload}\" - CommonMark image markdown was not blocked."
                    );
                }

                return true;
            }
        );
    }

    public function test_admin_can_complete_sign_in_place_acceptance_and_is_redirected_to_selected_destination()
    {
        Event::fake([CheckoutAccepted::class]);

        $assignee = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($asset, 'checkoutable')
            ->create();

        $this->actingAs($admin)
            ->withSession([
                'sign_in_place_acceptance_id' => $checkoutAcceptance->id,
                'sign_in_place_item_id' => $asset->id,
                'sign_in_place_resource_type' => 'Assets',
                'redirect_option' => 'target',
                'checkout_to_type' => 'user',
            ])
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => 'signed in person',
            ])
            ->assertRedirect(route('users.show', $assignee));

        $this->assertNotNull($checkoutAcceptance->refresh()->accepted_at);
        Event::assertDispatched(CheckoutAccepted::class);
    }

    public function test_stale_sign_in_place_post_on_already_accepted_item_redirects_to_intended_destination()
    {
        $assignee = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->accepted()
            ->for($assignee, 'assignedTo')
            ->for($asset, 'checkoutable')
            ->create();

        $this->actingAs($admin)
            ->withSession([
                'sign_in_place' => true,
                'redirect_option' => 'target',
                'checkout_to_type' => 'user',
            ])
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
            ])
            ->assertRedirect(route('users.show', $assignee));
    }

    public function test_stale_sign_in_place_post_with_missing_assignee_does_not_throw_route_error()
    {
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $assignee = User::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->accepted()
            ->for($assignee, 'assignedTo')
            ->for($asset, 'checkoutable')
            ->create();

        CheckoutAcceptance::whereKey($checkoutAcceptance->id)->update(['assigned_to_id' => null]);

        $this->actingAs($admin)
            ->withSession([
                'sign_in_place' => true,
                'redirect_option' => 'target',
                'checkout_to_type' => 'user',
            ])
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('error');
    }

    public function test_sign_in_place_acceptance_page_uses_checkout_flow_breadcrumbs()
    {
        $assignee = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $asset = Asset::factory()->create();

        $checkoutAcceptance = CheckoutAcceptance::factory()
            ->pending()
            ->for($assignee, 'assignedTo')
            ->for($asset, 'checkoutable')
            ->create();

        $response = $this->actingAs($admin)
            ->withSession([
                'sign_in_place_acceptance_id' => $checkoutAcceptance->id,
                'sign_in_place_item_id' => $asset->id,
                'sign_in_place_resource_type' => 'Assets',
            ])
            ->get(route('account.accept.item', $checkoutAcceptance));

        $response->assertOk()
            ->assertSeeInOrder([
                trans('general.assets'),
                $asset->display_name,
                trans('general.checkout'),
                sprintf('%s for %s', trans('general.sign_in_place'), $assignee->display_name),
            ], false)
            ->assertSee(route('hardware.index'), false)
            ->assertSee(route('hardware.show', $asset), false)
            ->assertDontSee(route('users.show', $assignee), false)
            ->assertDontSee(route('hardware.checkout.create', $asset), false);
    }

    public function test_oversized_note_is_rejected_before_persistence_or_notification()
    {
        // Regression for the DoS reported by PizzaStev3 (Ahmed Mohammed).
        // An unbounded `note` on the acceptance store endpoint was reaching
        // synchronous CommonMark rendering in the decline notification and
        // burning per-request PHP-worker CPU (40k bytes ~= 847ms, 80k ~= 2.6s).
        // Server-side `max:1000` on the note field closes this at the input
        // boundary regardless of parser version. Primary fix is the
        // league/commonmark 2.9.0 bump; this test locks in the input-side guard.
        Event::fake([CheckoutAccepted::class]);
        Notification::fake();

        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();
        $oversizedNote = 'F9'.str_repeat(' ', 40_000).'éZ';

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->from(route('account.accept.item', $checkoutAcceptance))
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'declined',
                'note' => $oversizedNote,
            ])
            ->assertRedirect(route('account.accept.item', $checkoutAcceptance))
            ->assertSessionHasErrors('note');

        $this->assertTrue($checkoutAcceptance->fresh()->isPending(), 'Acceptance state should not advance when note validation fails.');
        $this->assertNull($checkoutAcceptance->fresh()->declined_at);
        $this->assertNull($checkoutAcceptance->fresh()->accepted_at);

        Event::assertNotDispatched(CheckoutAccepted::class);
        Notification::assertNothingSent();
    }

    public function test_normal_length_note_still_works()
    {
        // Negative side of the oversized-note regression: a normal short note
        // must still round-trip through the acceptance flow. Guards against
        // over-tightening the max: rule below reasonable UX.
        Event::fake([CheckoutAccepted::class]);

        $checkoutAcceptance = CheckoutAcceptance::factory()->pending()->create();

        $this->actingAs($checkoutAcceptance->assignedTo)
            ->post(route('account.store-acceptance', $checkoutAcceptance), [
                'asset_acceptance' => 'accepted',
                'note' => str_repeat('a', 500),
            ])
            ->assertRedirectToRoute('account.accept')
            ->assertSessionHas('success');

        $this->assertFalse($checkoutAcceptance->fresh()->isPending());

        Event::assertDispatched(CheckoutAccepted::class);
    }
}
