<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CreateUserTool;
use App\Models\Company;
use App\Models\Department;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CreateUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->createUsers()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CreateUserTool)->handle(new Request($args));
    }

    public function test_creates_user_with_required_fields()
    {
        $content = $this->handle([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jane.doe.mcp',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('users', ['username' => 'jane.doe.mcp', 'first_name' => 'Jane']);
    }

    public function test_response_includes_new_user_id_and_username()
    {
        $content = $this->handle([
            'first_name' => 'Test',
            'username' => 'new.user.mcp',
        ])->getStructuredContent();

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('username', $content);
        $this->assertEquals('new.user.mcp', $content['username']);
        $this->assertIsInt($content['id']);
    }

    public function test_hashes_password_when_provided()
    {
        $this->handle([
            'first_name' => 'Secure',
            'username' => 'secure.user.mcp',
            'password' => 'secret1234',
        ]);

        $user = User::where('username', 'secure.user.mcp')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret1234', $user->password));
    }

    public function test_creates_user_with_no_password_when_omitted()
    {
        $this->handle([
            'first_name' => 'Nopass',
            'username' => 'nopass.user.mcp',
        ]);

        $user = User::where('username', 'nopass.user.mcp')->first();
        $this->assertNotNull($user);
        $this->assertFalse(Hash::check('', $user->password));
    }

    public function test_creates_user_with_email_and_contact_fields()
    {
        $this->handle([
            'first_name' => 'Contact',
            'username' => 'contact.user.mcp',
            'email' => 'contact@example.com',
            'phone' => '555-1234',
            'jobtitle' => 'Engineer',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'contact.user.mcp',
            'email' => 'contact@example.com',
            'phone' => '555-1234',
            'jobtitle' => 'Engineer',
        ]);
    }

    public function test_creates_user_with_department_and_location()
    {
        $department = Department::factory()->create();
        $location = Location::factory()->create();

        $this->handle([
            'first_name' => 'Located',
            'username' => 'located.user.mcp',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'located.user.mcp',
            'department_id' => $department->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_returns_error_when_username_missing()
    {
        $this->assertTrue($this->handle([
            'first_name' => 'No Username',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_first_name_missing()
    {
        $this->assertTrue($this->handle([
            'username' => 'no.firstname.mcp',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_username_already_exists()
    {
        User::factory()->create(['username' => 'duplicate.mcp']);

        $response = $this->handle([
            'first_name' => 'Dupe',
            'username' => 'duplicate.mcp',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_activates_user_by_default()
    {
        $this->handle([
            'first_name' => 'Active',
            'username' => 'default.active.mcp',
        ]);

        $this->assertDatabaseHas('users', ['username' => 'default.active.mcp', 'activated' => 1]);
    }

    public function test_can_create_deactivated_user()
    {
        $this->handle([
            'first_name' => 'Inactive',
            'username' => 'inactive.user.mcp',
            'activated' => false,
        ]);

        $this->assertDatabaseHas('users', ['username' => 'inactive.user.mcp', 'activated' => 0]);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle([
            'first_name' => 'Blocked',
            'username' => 'blocked.user.mcp',
        ])->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['username' => 'blocked.user.mcp']);
    }

    public function test_superadmin_can_assign_group_ids()
    {
        $this->actingAs(User::factory()->superuser()->create());
        $group = Group::factory()->create();

        $content = $this->handle([
            'first_name' => 'GroupedUser',
            'username' => 'grouped.user.mcp',
            'group_ids' => [$group->id],
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $user = User::where('username', 'grouped.user.mcp')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->groups->contains($group->id));
    }

    public function test_non_superadmin_cannot_assign_group_ids()
    {
        $group = Group::factory()->create();

        $response = $this->handle([
            'first_name' => 'Denied',
            'username' => 'denied.groups.mcp',
            'group_ids' => [$group->id],
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_cross_tenant_company_id_is_rejected_without_persisting_user(): void
    {
        // GHSA-p8wf-r947-vw7x. Prior behavior called Company::getIdForCurrentUser
        // only AFTER $user->save(), so a company-scoped caller asking for a
        // company outside their scope left an orphan account on the DB. The
        // REST equivalent (Api/UsersController::store) resolves the company
        // before save and rejects pre-write. This test pins the MCP tool to
        // match.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();
        $this->actingAs($actor);

        $response = $this->handle([
            'first_name' => 'Cross',
            'username' => 'cross.tenant.mcp',
            'password' => 'secret123456',
            'company_id' => $companyB->id,
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['username' => 'cross.tenant.mcp']);
    }

    public function test_floater_mode_blocks_companied_non_super_from_creating_floater(): void
    {
        // #19200 gate: under FMCS + null_company_is_floater, a non-superuser
        // with their own pivot memberships cannot create an uncompanied
        // account that would become a floater. Reproduces the primary attack
        // path in the report where the caller submits no company_id and the
        // old code wrote the user with an empty pivot.
        $this->settings->enableFloaterMode();

        $companyA = Company::factory()->create();
        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();
        $this->actingAs($actor);

        $response = $this->handle([
            'first_name' => 'Floater',
            'username' => 'floater.attempt.mcp',
            'password' => 'secret123456',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['username' => 'floater.attempt.mcp']);
    }

    public function test_strict_fmcs_blocks_companied_non_super_from_creating_uncompanied_user(): void
    {
        // #19192 gate: under strict FMCS (floater mode OFF), a non-superuser
        // with their own pivot memberships cannot create a user whose pivot
        // would end up empty, because such a record is immediately invisible
        // to the creator's scope.
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();
        $this->actingAs($actor);

        $response = $this->handle([
            'first_name' => 'Nocompany',
            'username' => 'nocompany.attempt.mcp',
            'password' => 'secret123456',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('users', ['username' => 'nocompany.attempt.mcp']);
    }

    public function test_superuser_can_create_uncompanied_user_under_floater_mode(): void
    {
        // Positive control for the floater gate: superusers are exempt and
        // still able to deliberately create a floater.
        $this->settings->enableFloaterMode();

        $this->actingAs(User::factory()->superuser()->create());

        $content = $this->handle([
            'first_name' => 'Super',
            'username' => 'super.floater.mcp',
            'password' => 'secret123456',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $user = User::where('username', 'super.floater.mcp')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->companies()->exists(), 'Superuser-created floater should have no pivot entries.');
    }

    public function test_same_company_creation_still_succeeds(): void
    {
        // Positive control: the normal happy path still works. A company-
        // scoped user manager creates a new user in their own company.
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $actor = User::factory()->createUsers()->forCompany($companyA->id)->create();
        $this->actingAs($actor);

        $content = $this->handle([
            'first_name' => 'Legit',
            'username' => 'legit.tenant.mcp',
            'password' => 'secret123456',
            'company_id' => $companyA->id,
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $user = User::where('username', 'legit.tenant.mcp')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->companies->contains($companyA->id));
    }
}
