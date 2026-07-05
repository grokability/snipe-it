<?php

namespace Tests\Feature\Licenses\Ui;

use App\Models\Actionlog;
use App\Models\Category;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class UpdateLicenseTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.edit', License::factory()->create()->id))
            ->assertOk();
    }

    public function test_can_update_license_seats()
    {
        $admin = User::factory()->superuser()->create();
        $license_category = Category::factory()->forLicenses()->create()->id;
        $response = $this->actingAs($admin)
            ->from(route('licenses.create'))
            ->post(route('licenses.store'), [
                'name' => 'Test Update License',
                'seats' => '9999',
                'category_id' => $license_category,
            ]);
        $response->assertStatus(302);
        $license = License::where('name', 'Test Update License')->sole();
        $this->assertNotNull($license);

        $this->actingAs($admin)
            ->put(route('licenses.update', $license->id), [
                'name' => 'Test Update License',
                'seats' => '19999',
                'category_id' => $license_category,
            ])
            ->assertStatus(302);

        $license->refresh();
        $this->assertEquals($license->licenseseats()->count(), $license->seats);
        $this->assertEquals($license->licenseseats()->count(), 19999);
    }

    public function test_cannot_update_license_seats_too_much()
    {
        $admin = User::factory()->superuser()->create();
        $license_category = Category::factory()->forLicenses()->create()->id;
        $response = $this->actingAs($admin)
            ->from(route('licenses.create'))
            ->post(route('licenses.store'), [
                'name' => 'Test Update License',
                'seats' => '9999',
                'category_id' => $license_category,
            ]);
        $response->assertStatus(302);
        $license = License::where('name', 'Test Update License')->sole();
        $this->assertNotNull($license);

        $this->actingAs($admin)
            ->put(route('licenses.update', $license->id), [
                'name' => 'Test Update License',
                'seats' => '29999',
                'category_id' => $license_category,
            ])
            ->assertStatus(302);

        $license->refresh();
        $this->assertEquals($license->licenseseats()->count(), $license->seats);
        $this->assertEquals($license->licenseseats()->count(), 9999);
    }

    public function test_can_remove_license_seats()
    {
        $admin = User::factory()->superuser()->create();
        $license_category = Category::factory()->forLicenses()->create()->id;
        $response = $this->actingAs($admin)
            ->from(route('licenses.create'))
            ->post(route('licenses.store'), [
                'name' => 'Test Remove License Seats',
                'seats' => '9999',
                'category_id' => $license_category,
            ]);
        $response->assertStatus(302);
        $license = License::where('name', 'Test Remove License Seats')->sole();
        $this->assertNotNull($license);

        $this->actingAs($admin)
            ->put(route('licenses.update', $license->id), [
                'name' => 'Test Remove License Seats',
                'seats' => '5000',
                'category_id' => $license_category,
            ])
            ->assertStatus(302);

        $license->refresh();
        $this->assertEquals($license->licenseseats()->count(), $license->seats);
        $this->assertEquals($license->licenseseats()->count(), 5000);
    }

    public function test_update_logs_changed_fields_in_log_meta()
    {
        $license = License::factory()->create(['name' => 'Old Name', 'seats' => 5]);

        $this->actingAs(User::factory()->editLicenses()->create())
            ->put(route('licenses.update', $license), [
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
        $license = License::factory()->create([
            'name' => 'Same Name',
            'seats' => 5,
            'license_email' => null,
            'notes' => null,
            'order_number' => null,
            'purchase_date' => null,
            'reassignable' => 0,
            'serial' => null,
            'supplier_id' => null,
        ]);

        $before = Actionlog::where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->count();

        $this->actingAs(User::factory()->editLicenses()->create())
            ->put(route('licenses.update', $license), [
                'name' => 'Same Name',
                'seats' => 5,
                'category_id' => $license->category_id,
            ]);

        $after = Actionlog::where('item_type', License::class)
            ->where('item_id', $license->id)
            ->where('action_type', 'update')
            ->count();

        $this->assertEquals($before, $after, 'A spurious log entry was created for a no-op update');
    }
}
