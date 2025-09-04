<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Traits\HasCustomFields;
use Illuminate\Support\Collection;
use Tests\Support\InitializesSettings;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;



class HasCustomFieldsTraitTest extends TestCase
{
    use InitializesSettings;

    //seems bonkers, but Assets needs it? (for currency calculation?)

    public function testAssetSchema()
    {
        $asset = Asset::factory()->withComplicatedCustomFields()->create();

        $this->assertEquals($asset->model->fieldset->fields->count(), 3,'Custom Fieldset should have exactly 3 custom fields');
        $this->assertTrue(Schema::hasColumn('assets','_snipeit_mac_address_explicit_2'),'Assets table should have MAC address column');
        $this->assertTrue(Schema::hasColumn('assets','_snipeit_plain_text_3'),'Assets table should have MAC address column');
        $this->assertTrue(Schema::hasColumn('assets','_snipeit_date_4'),'Assets table should have MAC address column');
    }
    public function testRequired()
    {
        $asset = Asset::factory()->withComplicatedCustomFields()->create();
        $this->assertFalse($asset->save(),'save() should fail due to required text field');
    }

    public function testFormat()
    {
        $asset = Asset::factory()->withComplicatedCustomFields()->make();
        $asset->_snipeit_plain_text_3 = 'something';
        $asset->_snipeit_mac_address_explicit_2 = 'fartsssssss';
        $this->assertFalse($asset->save(), 'should fail due to bad MAC address');
    }

    public function testDate()
    {
//        \Log::error("uh, what the heck is going on here?!");
        $asset = Asset::factory()->withComplicatedCustomFields()->make();
        $asset->_snipeit_plain_text_3 = 'some text';
        $asset->_snipeit_date_4 = '1/2/2023';
//        $asset->save();
//        dd($asset);
        $this->assertFalse($asset->save(),'Should fail due to incorrectly formatted date.');
    }

    public function testSaveMinimal()
    {
        $asset = Asset::factory()->withComplicatedCustomFields()->make();
        $asset->_snipeit_plain_text_3 = "some text";
        $this->assertTrue($asset->save(),"Asset should've saved okay, the one required field was filled out");
    }

    public function testSaveMaximal()
    {
        $asset = Asset::factory()->withComplicatedCustomFields()->make();
        $asset->_snipeit_plain_text_3 = "some text";
        $asset->_snipeit_date_4 = "2023-01-02";
        $asset->_snipeit_mac_address_explicit_2 = "ff:ff:ff:ff:ff:ff";
        $this->assertTrue($asset->save(),"Asset should've saved okay, the one required field was filled out, and so were the others");
    }

    public function testJsonPost()
    {
        //FIXME - this is in the wrong place and it might just be genuinley wrong?
        $this->markTestIncomplete();
        $asset = Asset::factory()->withComplicatedCustomFields()->make();
        $response = $this->postJson('/api/v1/hardware', [

        ]);
        $response->assertStatus(200);
    }

}
