<?php

namespace Tests\Feature\Checkins\Ui;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadOnCheckinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_file_upload_is_stored_on_action_log_for_asset_checkin()
    {
        Storage::fake('local');

        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->checkinAssets()->assetsFiles()->create())
            ->post(route('hardware.checkin.store', $asset), [
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasNoErrors();

        $log = Actionlog::where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkin action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_accessory_checkin()
    {
        Storage::fake('local');

        $accessory = Accessory::factory()->checkedOutToUser()->create();

        $this->actingAs(User::factory()->checkinAccessories()->accessoriesFiles()->create())
            ->post(route('accessories.checkin.store', $accessory->checkouts->first()->id), [
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasNoErrors();

        $log = Actionlog::where('item_type', Accessory::class)
            ->where('item_id', $accessory->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkin action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_license_checkin()
    {
        Storage::fake('local');

        $licenseSeat = LicenseSeat::factory()->assignedToUser()->create();

        $this->actingAs(User::factory()->checkinLicenses()->licensesFiles()->create())
            ->post(route('licenses.checkin.save', [$licenseSeat->id, 'backto' => 'user']), [
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasNoErrors();

        $log = Actionlog::where('item_type', License::class)
            ->where('item_id', $licenseSeat->license_id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkin action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }

    public function test_file_upload_is_stored_on_action_log_for_component_checkin()
    {
        Storage::fake('local');

        $component = Component::factory()->checkedOutToAsset()->create();
        $componentAsset = DB::table('components_assets')->where('component_id', $component->id)->first();

        $this->actingAs(User::factory()->checkinComponents()->componentsFiles()->create())
            ->post(route('components.checkin.store', $componentAsset->id), [
                'checkin_qty' => 1,
                'file' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasNoErrors();

        $log = Actionlog::where('item_type', Component::class)
            ->where('item_id', $component->id)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'No checkin action log entry found');
        $this->assertNotNull($log->filename, 'Filename was not saved to action log');
    }
}
