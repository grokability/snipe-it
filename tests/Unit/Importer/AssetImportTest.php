<?php

namespace Tests\Unit\Importer;

use App\Importer\AssetImporter;
use App\Models\Statuslabel;
use Tests\TestCase;
use function Livewire\invade;

class AssetImportTest extends TestCase
{
    public function test_uses_first_deployable_status_label_as_default_if_one_exists()
    {
        Statuslabel::truncate();

        $pendingStatusLabel = Statuslabel::factory()->pending()->create();
        $readyToDeployStatusLabel = Statuslabel::factory()->readyToDeploy()->create();

        $importer = new AssetImporter('assets.csv');

        $this->assertEquals(
            $readyToDeployStatusLabel->id,
            invade($importer)->defaultStatusLabelId
        );
    }

    public function test_uses_first_status_label_as_default_if_deployable_status_label_does_not_exist()
    {
        Statuslabel::truncate();

        $statusLabel = Statuslabel::factory()->pending()->create();

        $importer = new AssetImporter('assets.csv');

        $this->assertEquals(
            $statusLabel->id,
            invade($importer)->defaultStatusLabelId
        );
    }

    public function test_creates_default_status_label_if_one_does_not_exist()
    {
        Statuslabel::truncate();

        $this->assertEquals(0, Statuslabel::count());

        $importer = new AssetImporter('assets.csv');

        $this->assertEquals(1, Statuslabel::count());

        $this->assertEquals(
            Statuslabel::first()->id,
            invade($importer)->defaultStatusLabelId
        );
    }
}
