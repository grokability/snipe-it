<?php

namespace Tests\Unit\Observers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Dispara el metodo restoring() de los observers (User, Location) restaurando
 * un registro borrado logicamente, y updating() con cambios de varios campos.
 */
class ObserverRestoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_user_restoring(): void
    {
        $user = User::factory()->create();
        $user->delete();
        $user->restore();

        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_user_updating_multiple_fields(): void
    {
        $user = User::factory()->create();
        $user->update(['first_name' => 'New', 'last_name' => 'Name', 'email' => 'new@example.com', 'jobtitle' => 'Lead']);

        $this->assertEquals('New', $user->fresh()->first_name);
    }

    public function test_location_restoring(): void
    {
        $location = Location::factory()->create();
        $location->delete();
        $location->restore();

        $this->assertNull($location->fresh()->deleted_at);
    }
}
