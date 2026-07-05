<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\Actionlog;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class UpdateLicenseTest extends TestCase
{
    public function test_update_logs_changed_fields_in_log_meta()
    {
        $license = License::factory()->create(['name' => 'Old Name', 'seats' => 5]);

        $this->actingAsForApi(User::factory()->editLicenses()->create())
            ->patchJson(route('api.licenses.update', $license), [
                'name' => 'New Name',
                'seats' => 10,
                'category_id' => $license->category_id,
            ]);

        $log = Actionlog::where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'No update log entry was created');
        $this->assertNotNull($log->log_meta, 'log_meta was not stored');

        $meta = json_decode($log->log_meta, true);
        $this->assertEquals('Old Name', $meta['name']['old']);
        $this->assertEquals('New Name', $meta['name']['new']);
    }

    public function test_no_op_update_does_not_create_log_entry()
    {
        $license = License::factory()->create(['name' => 'Same Name']);

        $before = Actionlog::where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->count();

        $this->actingAsForApi(User::factory()->editLicenses()->create())
            ->patchJson(route('api.licenses.update', $license), [
                'name' => 'Same Name',
                'category_id' => $license->category_id,
            ]);

        $after = Actionlog::where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->count();

        $this->assertEquals($before, $after, 'A spurious log entry was created for a no-op update');
    }
}
