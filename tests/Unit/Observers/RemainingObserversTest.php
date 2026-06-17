<?php

namespace Tests\Unit\Observers;

use App\Models\AssetModel;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Dispara created/updated/deleting de los observers restantes
 * (User, Location, AssetModel, License, Maintenance).
 */
class RemainingObserversTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_user_observer_lifecycle(): void
    {
        $user = User::factory()->create();
        $user->update(['jobtitle' => 'Engineer']);
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_location_observer_lifecycle(): void
    {
        $location = Location::factory()->create();
        $location->update(['name' => 'New HQ']);
        $location->delete();

        $this->assertSoftDeleted('locations', ['id' => $location->id]);
    }

    public function test_asset_model_observer_lifecycle(): void
    {
        $model = AssetModel::factory()->create();
        $model->update(['name' => 'Updated Model']);
        $model->delete();

        $this->assertSoftDeleted('models', ['id' => $model->id]);
    }

    public function test_license_observer_lifecycle(): void
    {
        $license = License::factory()->create();
        $license->update(['name' => 'Updated License']);
        $license->delete();

        $this->assertSoftDeleted('licenses', ['id' => $license->id]);
    }

    public function test_maintenance_observer_lifecycle(): void
    {
        $maintenance = Maintenance::factory()->create();
        $maintenance->update(['notes' => 'updated notes']);
        $maintenance->delete();

        $this->assertNull(Maintenance::find($maintenance->id));
    }
}
