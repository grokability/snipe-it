<?php

namespace Tests\Feature\Groups\Ui;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;

class UpdateGroupTest extends TestCase
{
    public function testPageRenders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('groups.edit', Group::factory()->create()->id))
            ->assertOk();
    }

    public function testUserCanEditGroups()
    {
        $group = Group::factory()->create(['name' => 'Test Group']);
        $this->assertTrue(Group::where('name', 'Test Group')->exists());

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->put(route('groups.update', ['group' => $group]), [
                'name' => 'Test Group Edited',
                'notes' => 'Test Note Edited',
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('groups.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertTrue(Group::where('name', 'Test Group Edited')->where('notes', 'Test Note Edited')->exists());
    }
}
