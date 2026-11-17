<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteComponentsTest extends TestCase
{
    public function test_requires_delete_permission(): void
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->viewComponents()->create())
            ->post(route('components.bulkdelete.store'), ['ids' => [$component->id]])
            ->assertForbidden();

        $this->assertNull($component->fresh()->deleted_at);
    }

    public function test_soft_deletes_selected_components(): void
    {
        $one = Component::factory()->create();
        $two = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkdelete.store'), ['ids' => [$one->id, $two->id]])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('success');

        $this->assertNotNull($one->fresh()->deleted_at);
        $this->assertNotNull($two->fresh()->deleted_at);
    }

    public function test_skips_components_with_active_checkouts(): void
    {
        // Pin so a future refactor can't silently delete checked-out
        // components. isDeletable() returns false while any pivot row
        // has a positive assigned_qty, so the handler should route
        // this row into the error bucket instead of calling delete().
        $clean = Component::factory()->create();

        $withCheckouts = Component::factory()->create(['qty' => 10]);
        $asset = Asset::factory()->create();
        $withCheckouts->assets()->attach($asset->id, [
            'assigned_qty' => 1,
            'created_at' => now(),
            'created_by' => 1,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkdelete.store'), ['ids' => [$clean->id, $withCheckouts->id]])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('multi_error_messages');

        $this->assertNotNull($clean->fresh()->deleted_at);
        $this->assertNull($withCheckouts->fresh()->deleted_at);
    }

    public function test_unknown_ids_fall_through_to_error_bucket(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkdelete.store'), ['ids' => [999999]])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('multi_error_messages');
    }
}
