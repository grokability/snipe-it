<?php

namespace Tests\Feature\Locations\Ui;

use App\Models\Location;
use App\Models\User;
use App\Models\Actionlog;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateLocationsTest extends TestCase
{
    public function testPermissionRequiredToStoreLocation()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('locations.store'), [
                'name' => 'Test Location',
            ])
            ->assertStatus(403)
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('locations.edit', Location::factory()->create()))
            ->assertOk();
    }

    public function testUserCanEditLocations()
    {
        $location = Location::factory()->create(['name' => 'Test Location']);
        $this->assertTrue(Location::where('name', 'Test Location')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('locations.update', ['location' => $location]), [
                'name' => 'Test Location Edited',
                'notes' => 'Test Note Edited',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('locations.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertTrue(Location::where('name', 'Test Location Edited')->where('notes', 'Test Note Edited')->exists());
    }

    public function testUserCannotEditLocationsToMakeThemTheirOwnParent()
    {
        $location = Location::factory()->create();

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('locations.edit', $location))
            ->put(route('locations.update', $location), [
                'name' => 'Test Location',
                'parent_id' => $location->id,
            ])
            ->assertRedirect(route('locations.edit', ['location' => $location]));

        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(Location::where('name', 'Test Location')->exists());
    }

    public function testUserCannotEditLocationsWithInvalidParent()
    {
        $location = Location::factory()->create();
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->from(route('locations.edit', $location))
            ->put(route('locations.update', ['location' => $location]), [
                'name' => 'Test Location',
                'parent_id' => '100000000'
            ])
            ->assertRedirect(route('locations.edit', ['location' => $location->id]));

        $this->followRedirects($response)->assertSee(trans('general.error'));
        $this->assertFalse(Location::where('name', 'Test Location')->exists());
    }


    public function testFileIsUploadedAndLogged()
    {
        $location = Location::factory()->create();
        Storage::fake('local');
        $file = UploadedFile::fake()->image('file.jpg', 100, 100)->size(100);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('ui.files.store', ['object_type' => 'locations', 'id' => $location->id]), [
            'file' => [$file],
            'notes' => 'Test Upload',
        ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors();


        $location->refresh();

        $logentry = Actionlog::where('item_type', Location::class)
            ->where('item_id', $location->id)
            ->where('action_type', 'uploaded')
            ->first();

        $this->assertTrue(Actionlog::where('item_type', Location:: class)->where('item_id', $location->id)->where('filename', $logentry->filename)->exists());


        // Assert the file was stored...
        // This doesn't work with the way we handle files :( Should try to fix this.
        // Storage::disk('local')->assertExists($logentry->filename);

    }


}
