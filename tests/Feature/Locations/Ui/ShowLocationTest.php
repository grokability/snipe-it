<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class ShowLocationTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.show', Location::factory()->create()))
            ->assertOk();
    }

    public function test_denies_access_to_regular_user()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('locations.show', Location::factory()->create()))
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function test_denies_print_access_to_regular_user()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('locations.print_all_assigned', Location::factory()->create()))
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function test_page_renders_for_super_admin()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.print_all_assigned', Location::factory()->create()))
            ->assertOk();
    }

    public function test_print_all_assigned_renders_accessory_name_for_location_checkouts()
    {
        // Regression guard for #19093: Location::assignedAccessories()
        // is a morphMany onto accessories_checkout, so the collection
        // items are AccessoryCheckout instances, not Accessory. The
        // print template used to read $checkout->name (which doesn't
        // exist on AccessoryCheckout) and quietly produced blank rows.
        // Reach through ->accessory to render the real attributes.
        $location = Location::factory()->create();
        $accessory = \App\Models\Accessory::factory()->create([
            'name' => 'DistinctBluetoothMouse-19093',
            'model_number' => 'MDL-19093',
        ]);
        \App\Models\AccessoryCheckout::factory()->create([
            'accessory_id' => $accessory->id,
            'assigned_to' => $location->id,
            'assigned_type' => Location::class,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.print_all_assigned', $location))
            ->assertOk()
            ->assertSee('DistinctBluetoothMouse-19093')
            ->assertSee('MDL-19093');
    }

    public function test_show_page_includes_parent_location_breadcrumb_hierarchy()
    {
        $grandparent = Location::factory()->create(['name' => 'Grandparent Breadcrumb Location']);
        $parent = Location::factory()->create([
            'name' => 'Parent Breadcrumb Location',
            'parent_id' => $grandparent->id,
        ]);
        $child = Location::factory()->create([
            'name' => 'Child Breadcrumb Location',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.show', $child))
            ->assertOk()
            ->assertSeeInOrder([
                route('locations.show', $grandparent),
                route('locations.show', $parent),
                route('locations.show', $child),
            ]);
    }

    public function test_show_page_info_panel_includes_parent_location_hierarchy_without_current_location()
    {
        $grandparent = Location::factory()->create(['name' => 'Grandparent Info Panel Location']);
        $parent = Location::factory()->create([
            'name' => 'Parent Info Panel Location',
            'parent_id' => $grandparent->id,
        ]);
        $child = Location::factory()->create([
            'name' => 'Child Info Panel Location',
            'parent_id' => $parent->id,
        ]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.show', $child));

        $response->assertOk()
            ->assertSeeInOrder([
                route('locations.show', $grandparent),
                route('locations.show', $parent),
            ]);

        $responseContent = $response->getContent();

        $this->assertStringNotContainsString(
            '<a href="'.route('locations.show', $child).'">'.$child->display_name.'</a>',
            $responseContent
        );
    }
}
