<?php
namespace Tests\Unit;

use App\Models\SnipeModel;
use Tests\TestCase;

class SnipeModelTest extends TestCase
{
    public function testSetsPurchaseDatesAppropriately()
    {
        $c = new SnipeModel;
        $c->purchase_date = '';
        $this->assertNull($c->purchase_date);
        $c->purchase_date = null;
        $this->assertNull($c->purchase_date);
        $c->purchase_date = '2016-03-25';
        $this->assertTrue($c->purchase_date === '2016-03-25');
        $this->assertEquals('2016-03-25', $c->purchase_date);
    }

    public function testSetsPurchaseCostsAppropriately()
    {
        $c = new SnipeModel;
        $c->purchase_cost = '';
        $this->assertTrue($c->purchase_cost == null);
        $c->purchase_cost = '0.00';
        $this->assertTrue($c->purchase_cost == 0.00);
        $c->purchase_cost = '9.54';
        $this->assertTrue($c->purchase_cost == 9.54);
        $c->purchase_cost = '9.50';
        $this->assertTrue($c->purchase_cost == 9.5);
    }

    public function testNullsBlankLocationIdsButNotOthers()
    {
        $c = new SnipeModel;
        $c->location_id = '';
        $this->assertTrue($c->location_id === null);
        $c->location_id = '5';
        $this->assertTrue($c->location_id == 5);
    }

    public function testNullsBlankCategoriesButNotOthers()
    {
        $c = new SnipeModel;
        $c->category_id = '';
        $this->assertTrue($c->category_id === null);
        $c->category_id = '1';
        $this->assertTrue($c->category_id == 1);
    }

    public function testNullsBlankSuppliersButNotOthers()
    {
        $c = new SnipeModel;
        $c->supplier_id = '';
        $this->assertTrue($c->supplier_id === null);
        $c->supplier_id = '4';
        $this->assertTrue($c->supplier_id == 4);
    }

    public function testNullsBlankDepreciationsButNotOthers()
    {
        $c = new SnipeModel;
        $c->depreciation_id = '';
        $this->assertTrue($c->depreciation_id === null);
        $c->depreciation_id = '4';
        $this->assertTrue($c->depreciation_id == 4);
    }

    public function testNullsBlankManufacturersButNotOthers()
    {
        $c = new SnipeModel;
        $c->manufacturer_id = '';
        $this->assertTrue($c->manufacturer_id === null);
        $c->manufacturer_id = '4';
        $this->assertTrue($c->manufacturer_id == 4);
    }
}
