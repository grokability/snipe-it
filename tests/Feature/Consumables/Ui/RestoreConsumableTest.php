<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class RestoreConsumableTest extends TestCase
{
    public function test_single_restore_requires_delete_permission(): void
    {
        $consumable = Consumable::factory()->create();
        $consumable->delete();

        $this->actingAs(User::factory()->viewConsumables()->create())
            ->post(route('consumables.restore', ['consumableId' => $consumable->id]))
            ->assertForbidden();

        $this->assertNotNull($consumable->fresh()->deleted_at);
    }

    public function test_single_restore_brings_consumable_back(): void
    {
        $consumable = Consumable::factory()->create();
        $consumable->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.restore', ['consumableId' => $consumable->id]))
            ->assertRedirect(route('consumables.show', $consumable));

        $this->assertNull($consumable->fresh()->deleted_at);
    }

    public function test_single_restore_rejects_already_live_consumable(): void
    {
        $consumable = Consumable::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.restore', ['consumableId' => $consumable->id]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($consumable->fresh()->deleted_at);
    }

    public function test_bulk_restore_brings_selected_consumables_back(): void
    {
        $one = Consumable::factory()->create();
        $two = Consumable::factory()->create();
        $one->delete();
        $two->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('consumables.bulkedit.show'), [
                'ids' => [$one->id, $two->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect(route('consumables.index', ['status' => 'Deleted']))
            ->assertSessionHas('success');

        $this->assertNull($one->fresh()->deleted_at);
        $this->assertNull($two->fresh()->deleted_at);
    }

    public function test_bulk_restore_blocks_cross_company_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editConsumables()->deleteConsumables()->viewConsumables()->make());
        $foreign = Consumable::factory()->for($companyB)->create();
        $foreign->delete();

        $this->actingAs($actor)
            ->post(route('consumables.bulkedit.show'), [
                'ids' => [$foreign->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect();

        $this->assertNotNull($foreign->fresh()->deleted_at);
    }
}
