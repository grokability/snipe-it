<?php

namespace Tests\Unit;

use App\Models\Statuslabel;
use Tests\TestCase;

class StatuslabelTest extends TestCase
{
    public function test_rtd_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->rtd()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_pending_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->pending()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_archived_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->archived()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_out_for_repair_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->outForRepair()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_broken_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->broken()->create();
        $this->assertModelExists($statuslabel);
    }

    public function test_lost_statuslabel_add()
    {
        $statuslabel = Statuslabel::factory()->lost()->create();
        $this->assertModelExists($statuslabel);
    }
}
