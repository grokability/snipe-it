<?php

namespace Tests\Feature\Checkouts\Api;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadOnCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_file_upload_is_stored_on_action_log_for_asset_checkout()
    {
        Storage::fake('local');

        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAssets()->assetsFiles()->create())
            ->post(route('api.asset.checkout', $asset), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertOk();

        $log = Actionlog::where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkout action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_accessory_checkout()
    {
        Storage::fake('local');

        $accessory = Accessory::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutAccessories()->accessoriesFiles()->create())
            ->post(route('api.accessories.checkout', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertOk();

        $log = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkout action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_license_checkout()
    {
        Storage::fake('local');

        $licenseSeat = LicenseSeat::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->licensesFiles()->create())
            ->post(route('api.licenses.checkout', $licenseSeat->license->id), [
                'target_type' => 'user',
                'assigned_to' => $user->id,
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertOk();

        $log = Actionlog::where('item_type', License::class)
            ->where('item_id', $licenseSeat->license->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkout action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_component_checkout()
    {
        Storage::fake('local');

        $component = Component::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutComponents()->componentsFiles()->create())
            ->post(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => 1,
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertOk();

        $log = Actionlog::where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkout action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_consumable_checkout()
    {
        Storage::fake('local');

        $consumable = Consumable::factory()->create();
        $user = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutConsumables()->consumablesFiles()->create())
            ->post(route('api.consumables.checkout', $consumable), [
                'assigned_to' => $user->id,
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertOk();

        $log = Actionlog::where('item_type', Consumable::class)
            ->where('item_id', $consumable->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkout action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }
}
