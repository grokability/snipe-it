<?php
namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Statuslabel;
use Tests\TestCase;

class StatusLabelQueryTest extends TestCase
{
    // Status labels
    public function testFilterAssetStatusLabelEmptyString()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        // Act
        $filter = [
            'status_label' => '',
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
    }

    public function testFilterAssetStatusLabelStringComplete()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        // Act
        $filter = [
            'status_label' => $statusPending->name,
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetStatusLabelStringPartial()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        // Act
        $queryString = substr($statusPending->name, 0, floor(strlen($statusPending->name) / 2));
        $filter = [
            'status_label' => $queryString,
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetStatusLabelArraySingle()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        // Act
        $filter = [
            'status_label' => [$statusPending->name],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetStatusLabelArrayMultiple()
    {
        // Arrange:
        $statusArchived = Statuslabel::factory()->create();
        $statusBroken = Statuslabel::factory()->create();
        $statusDeployed = Statuslabel::factory()->create();
        $statusPending = Statuslabel::factory()->create();
        $statusReadyToDeploy = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusBroken->id,
        ]);

        $assetC = Asset::factory()->create([
            'status_id' => $statusDeployed->id,
        ]);

        $assetD = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetE = Asset::factory()->create([
            'status_id' => $statusReadyToDeploy->id,
        ]);

        // Act
        $filter = [
            'status_label' => [$statusPending->name, $statusDeployed->name],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetC));
        $this->assertTrue($results->contains($assetD));
        $this->assertFalse($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
        $this->assertFalse($results->contains($assetE));
    }

    public function testFilterAssetStatusLabelId()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        // Act
        $filter = [
            'status_label' => $statusPending->id,
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetStatusLabelIdArraySingle()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        // Act
        $filter = [
            'status_label' => [$statusPending->id],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertFalse($results->contains($assetB));
    }

    public function testFilterAssetStatusLabelIdAndNameArray()
    {
        // Arrange:
        $statusPending = Statuslabel::factory()->create();
        $statusArchived = Statuslabel::factory()->create();
        $statusBroken = Statuslabel::factory()->create();

        $assetA = Asset::factory()->create([
            'status_id' => $statusPending->id,
        ]);

        $assetB = Asset::factory()->create([
            'status_id' => $statusArchived->id,
        ]);

        $assetC = Asset::factory()->create([
            'status_id' => $statusBroken->id,
        ]);

        // Act
        $filter = [
            'status_label' => [$statusPending->id, $statusArchived->name],
        ];

        $results = Asset::query()->byFilter($filter)->get();

        // Assert
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($assetA));
        $this->assertTrue($results->contains($assetB));
        $this->assertFalse($results->contains($assetC));
    }
}