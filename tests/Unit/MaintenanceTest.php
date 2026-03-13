<?php
namespace Tests\Unit;

use App\Models\Maintenance;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    public function testZerosOutWarrantyIfBlank()
    {
        $c = new Maintenance;
        $c->is_warranty = '';
        $this->assertTrue($c->is_warranty === 0);
        $c->is_warranty = '4';
        $this->assertTrue($c->is_warranty == 4);
    }

    public function testSetsCostsAppropriately()
    {
        $c = new Maintenance();
        $c->cost = '0.00';
        $this->assertTrue($c->cost === null);
        $c->cost = '9.54';
        $this->assertTrue($c->cost === 9.54);
        $c->cost = '9.50';
        $this->assertTrue($c->cost === 9.5);
    }

    public function testNullsOutNotesIfBlank()
    {
        $c = new Maintenance;
        $c->notes = '';
        $this->assertTrue($c->notes === null);
        $c->notes = 'This is a long note';
        $this->assertTrue($c->notes === 'This is a long note');
    }

    public function testNullsOutCompletionDateIfBlankOrInvalid()
    {
        $c = new Maintenance;
        $c->completion_date = '';
        $this->assertTrue($c->completion_date === null);
        $c->completion_date = '0000-00-00';
        $this->assertTrue($c->completion_date === null);
    }
}
