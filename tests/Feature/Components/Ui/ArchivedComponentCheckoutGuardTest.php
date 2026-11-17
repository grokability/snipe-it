<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class ArchivedComponentCheckoutGuardTest extends TestCase
{
    public function test_checkout_create_screen_blocks_archived_component(): void
    {
        $component = Component::factory()->archived()->create(['qty' => 5]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.checkout.show', ['componentID' => $component->id]))
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.component')]));
    }

    public function test_checkout_store_blocks_archived_component(): void
    {
        $component = Component::factory()->archived()->create(['qty' => 5]);
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.checkout.store', ['componentID' => $component->id]), [
                'asset_id' => $asset->id,
                'assigned_qty' => 1,
            ])
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('error', trans('general.cannot_checkout_archived', ['item_type' => trans('general.component')]));

        // No pivot row created.
        $this->assertSame(0, $component->assets()->count());
    }

    public function test_checkout_create_screen_allows_non_archived_component(): void
    {
        $component = Component::factory()->create(['qty' => 5]);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.checkout.show', ['componentID' => $component->id]))
            ->assertOk();
    }
}
