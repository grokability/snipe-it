<?php

namespace Tests\Feature\Licenses\Ui;

use App\Models\Category;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class CreateLicenseTest extends TestCase
{
    public function test_permission_required_to_create_license()
    {
        $license = License::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('licenses.create', $license))
            ->assertForbidden();
    }

    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('licenses.create'))
            ->assertOk();
    }

    public function test_license_without_purchase_date_fails_validation()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('licenses.create'))
            ->post(route('licenses.store'), [
                'name' => 'Test Invalid License',
                'seats' => '10',
                'category_id' => Category::factory()->forLicenses()->create()->id,
                'depreciation_id' => Depreciation::factory()->create()->id,
            ]);
        $response->assertStatus(302);
        $response->assertRedirect(route('licenses.create'));
        $response->assertInvalid(['purchase_date']);
        $response->assertSessionHasErrors(['purchase_date']);
        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(License::where('name', 'Test Invalid License')->exists());

    }

    public function test_license_create()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('licenses.create'))
            ->post(route('licenses.store'), [
                'name' => 'Test Valid License',
                'seats' => '10',
                'category_id' => Category::factory()->forLicenses()->create()->id,
            ]);
        $response->assertStatus(302);
        $license = License::where('name', 'Test Valid License')->sole();
        $this->assertNotNull($license);
        // $license->assetlog()->has_one_of_();
        $this->assertDatabaseHas('action_logs', ['action_type' => 'create', 'item_id' => $license->id, 'item_type' => License::class]);
        $this->assertDatabaseHas('action_logs', ['action_type' => 'add seats', 'item_id' => $license->id, 'item_type' => License::class]);
        $this->assertEquals($license->licenseseats()->count(), 10);
        // test log entries? Sure.

    }

    public function test_too_many_seats_license_create()
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('licenses.create'))
            ->post(route('licenses.store'), [
                'name' => 'Test Valid License',
                'seats' => '100000',
                'category_id' => Category::factory()->forLicenses()->create()->id,
            ]);
        $response->assertStatus(302);
        $license = License::where('name', 'Test Valid License')->first();
        $this->assertNull($license);
        // $license->assetlog()->has_one_of_();
        //        $this->assertDatabaseMissing('action_logs', ['action_type' => 'create', 'item_id' => $license->id, 'item_type' => License::class]);
        //        $this->assertDatabaseMissing('action_logs', ['action_type' => 'add seats', 'item_id' => $license->id, 'item_type' => License::class]);
        // test log entries? Sure.

    }
}
