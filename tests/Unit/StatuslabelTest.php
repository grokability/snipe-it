<?php
namespace Tests\Unit;

use App\Models\Statuslabel;
use Tests\TestCase;

class StatuslabelTest extends TestCase
{
    public function testRTDStatuslabelAdd()
    {
        $statuslabel = Statuslabel::factory()->rtd()->create();
        $this->assertModelExists($statuslabel);
    }

    public function testPendingStatuslabelAdd()
    {
        $statuslabel = Statuslabel::factory()->pending()->create();
        $this->assertModelExists($statuslabel);
    }

    public function testArchivedStatuslabelAdd()
    {
        $statuslabel = Statuslabel::factory()->archived()->create();
        $this->assertModelExists($statuslabel);
    }

    public function testOutForRepairStatuslabelAdd()
    {
        $statuslabel = Statuslabel::factory()->outForRepair()->create();
        $this->assertModelExists($statuslabel);
    }

    public function testBrokenStatuslabelAdd()
    {
        $statuslabel = Statuslabel::factory()->broken()->create();
        $this->assertModelExists($statuslabel);
    }

    public function testLostStatuslabelAdd()
    {
        $statuslabel = Statuslabel::factory()->lost()->create();
        $this->assertModelExists($statuslabel);
    }
}
