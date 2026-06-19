<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\LabelsTransformer;
use App\Http\Transformers\MaintenancesTransformer;
use App\Models\Labels\Label;
use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre LabelsTransformer (antes 0%) y MaintenancesTransformer (antes 64%).
 */
class LabelsAndMaintenancesTransformerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_transform_labels(): void
    {
        $labels = Label::find(); // Collection de instancias Label

        $result = (new LabelsTransformer)->transformLabels($labels, $labels->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_transform_single_label(): void
    {
        $label = Label::find('DefaultLabel');

        $result = (new LabelsTransformer)->transformLabel($label);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
    }

    public function test_transform_maintenances(): void
    {
        $maintenances = Maintenance::factory()->count(2)->create();

        $result = (new MaintenancesTransformer)->transformMaintenances($maintenances, $maintenances->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals($maintenances->count(), $result['total']);
    }

    public function test_transform_single_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $result = (new MaintenancesTransformer)->transformMaintenance($maintenance);

        $this->assertSame($maintenance->id, $result['id']);
    }

    public function test_transform_maintenances_flat_and_report(): void
    {
        $maintenances = Maintenance::factory()->count(2)->create();

        $flat = (new MaintenancesTransformer)->transformMaintenancesFlat($maintenances, $maintenances->count());
        $this->assertArrayHasKey('rows', $flat);

        $report = (new MaintenancesTransformer)->transformMaintenanceForReport($maintenances->first());
        $this->assertIsArray($report);
    }
}
