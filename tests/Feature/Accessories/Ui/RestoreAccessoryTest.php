<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class RestoreAccessoryTest extends TestCase
{
    public function test_single_restore_requires_delete_permission(): void
    {
        $accessory = Accessory::factory()->create();
        $accessory->delete();

        $this->actingAs(User::factory()->viewAccessories()->create())
            ->post(route('accessories.restore', ['accessoryId' => $accessory->id]))
            ->assertForbidden();

        $this->assertNotNull($accessory->fresh()->deleted_at);
    }

    public function test_single_restore_brings_accessory_back(): void
    {
        $accessory = Accessory::factory()->create();
        $accessory->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.restore', ['accessoryId' => $accessory->id]))
            ->assertRedirect(route('accessories.show', $accessory));

        $this->assertNull($accessory->fresh()->deleted_at);
    }

    public function test_single_restore_rejects_already_live_accessory(): void
    {
        $accessory = Accessory::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.restore', ['accessoryId' => $accessory->id]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($accessory->fresh()->deleted_at);
    }

    public function test_bulk_restore_brings_selected_accessories_back(): void
    {
        $one = Accessory::factory()->create();
        $two = Accessory::factory()->create();
        $one->delete();
        $two->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.bulkedit.show'), [
                'ids' => [$one->id, $two->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect(route('accessories.index', ['status' => 'Deleted']))
            ->assertSessionHas('success');

        $this->assertNull($one->fresh()->deleted_at);
        $this->assertNull($two->fresh()->deleted_at);
    }

    public function test_bulk_restore_blocks_cross_company_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editAccessories()->deleteAccessories()->viewAccessories()->make());
        $foreign = Accessory::factory()->for($companyB)->create();
        $foreign->delete();

        $this->actingAs($actor)
            ->post(route('accessories.bulkedit.show'), [
                'ids' => [$foreign->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect();

        $this->assertNotNull($foreign->fresh()->deleted_at);
    }
}
