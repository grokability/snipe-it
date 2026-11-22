<?php

namespace Tests\Feature\Scim;

use App\Models\Actionlog;
use App\Models\SCIMUser;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ScimUserActionlogTypeTest extends TestCase
{
    public function test_scim_created_user_log_reports_user_item_type()
    {
        Passport::actingAs(User::factory()->superuser()->create());

        $this->postJson('/scim/v2/Users', [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
            'userName' => 'scim-created@example.com',
            'name' => ['givenName' => 'Test', 'familyName' => 'SCIM'],
            'active' => true,
        ])->assertCreated();

        $log = Actionlog::where('item_type', SCIMUser::class)
            ->where('action_type', 'create')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('user', $log->itemType());
    }

    public function test_scim_user_target_reports_user_target_type()
    {
        $log = new Actionlog(['target_type' => SCIMUser::class]);

        $this->assertSame('user', $log->targetType());
    }
}
