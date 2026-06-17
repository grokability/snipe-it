<?php

namespace Tests\Unit\Services;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Consumable;
use App\Models\PredefinedKit;
use App\Models\Statuslabel;
use App\Models\User;
use App\Services\PredefinedKitCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PredefinedKitCheckoutServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_checkout_empty_kit_returns_no_errors(): void
    {
        $kit = PredefinedKit::factory()->create();
        $user = User::factory()->create();

        $result = (new PredefinedKitCheckoutService)->checkout(new Request(['note' => 'n']), $kit, $user);

        $this->assertArrayHasKey('errors', $result);
        $this->assertEmpty($result['errors']);
    }

    public function test_checkout_kit_with_items(): void
    {
        $kit = PredefinedKit::factory()->create();
        $user = User::factory()->create();

        $status = Statuslabel::factory()->create(['deployable' => 1, 'archived' => 0, 'pending' => 0]);
        $model = AssetModel::factory()->create();
        Asset::factory()->count(2)->for($model, 'model')->create(['status_id' => $status->id, 'assigned_to' => null]);

        $kit->models()->attach($model->id, ['quantity' => 1]);

        $result = (new PredefinedKitCheckoutService)->checkout(new Request(['note' => 'kit']), $kit, $user);

        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('assets', $result);
    }
}
