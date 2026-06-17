<?php

namespace Tests\Unit\Observers;

use App\Models\Accessory;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

/**
 * Dispara created/updated/deleting de los observers de inventario creando,
 * actualizando y eliminando cada modelo (los observers registran action logs).
 */
class InventoryObserversTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_accessory_observer_lifecycle(): void
    {
        $accessory = Accessory::factory()->create();   // created()
        $accessory->update(['name' => 'Updated Accessory']); // updated()
        $accessory->delete();                            // deleting()

        $this->assertSoftDeleted('accessories', ['id' => $accessory->id]);
    }

    public function test_component_observer_lifecycle(): void
    {
        $component = Component::factory()->create();
        $component->update(['name' => 'Updated Component']);
        $component->delete();

        $this->assertSoftDeleted('components', ['id' => $component->id]);
    }

    public function test_consumable_observer_lifecycle(): void
    {
        $consumable = Consumable::factory()->create();
        $consumable->update(['name' => 'Updated Consumable']);
        $consumable->delete();

        $this->assertSoftDeleted('consumables', ['id' => $consumable->id]);
    }
}
