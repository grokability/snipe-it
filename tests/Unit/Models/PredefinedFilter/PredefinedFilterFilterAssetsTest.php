<?php
namespace Tests\Unit\Models\PredefinedFilter;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Manufacturer;
use App\Models\PredefinedFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User; 
use PhpParser\Node\Expr\FuncCall;
use Tests\TestCase;

class PredefinedFilterFilterAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    } 

    //A

    /** @test */
    public function test_it_returns_all_assets_when_filter_data_is_null() 
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();
        
        $filter = PredefinedFilter::create([
            'name'          =>   'null_filter',
            'created_by'    => $this->user->id,
            'filter_data'   => [], 
        ]);
        
        $query = Asset::query();
        $filter -> filterAssets($query);
        $resultIds = $query->pluck('id') ;

        $this->assertTrue($resultIds->contains($a->id));
        $this->assertTrue($resultIds->contains($b->id));
        $this->assertCount(2, $resultIds);
    } 

    /** @test */
    public function it_returns_all_assets_when_filter_data_is_empty_array()
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();

        $filter = PredefinedFilter::create([
            'name'       => 'empty_array_filter',
            'created_by' => $this->user->id,
            'filter_data'=> [],
        ]);

        $query   = Asset::query();
        $filter->filterAssets($query);
        $resultIds = $query->pluck('id');

        $this->assertTrue($resultIds->contains($a->id));
        $this->assertTrue($resultIds->contains($b->id));
        $this->assertCount(2, $resultIds);
    }

    /** @test  */
    public function it_ignores_empty_strings_nulls_and_empty_arrays_in_filter_data()
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();

        $filter = PredefinedFilter::create([
            'name'          => 'ignore_empty_values',
            'created_by'    => $this->user->id,
            'filter_data'   => [
                'company_id'        => '',
                'status_id'         => null,
                'model_id'          => [], 
                'custome_fields'    => [], 

            ],  
        ]); 

        $query   = Asset::query();
        $filter->filterAssets($query);
        $resultIds = $query->pluck('id');

        $this->assertTrue($resultIds->contains($a->id));
        $this->assertTrue($resultIds->contains($b->id));
        $this->assertCount(2, $resultIds);
    } 

    /** @test */
    public function it_ignores_unknown_filter_keys_without_throwing()
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();

        $filter = PredefinedFilter::create([
            'name'          => 'unkown_keys',
            'created_by'    => $this->user->id,
            'filter_data'   => [
                'totally_unkown_key'    =>  'whatever',
                'another_strange_key'   => ['x', 'y'], 
            ], 
        ]); 
        
        $query   = Asset::query();
        $filter->filterAssets($query);
        $resultIds = $query->pluck('id');

        $this->assertTrue($resultIds->contains($a->id));
        $this->assertTrue($resultIds->contains($b->id));
        $this->assertCount(2, $resultIds);
    }  

        /** @test */
    public function it_casts_filter_data_to_array()
    {
        $filter = PredefinedFilter::create([
            'name'          => 'cast_check',
            'created_by'    => $this->user->id,
            'filter_data'   => ['status_id' => [1,2,3]],
        ]);

        $this->assertIsArray($filter->filter_data);
        $this->assertEquals([1,2,3], $filter->filter_data['status_id']);
    }

    //B
    /** @test  */
    public function it_filters_by_company_id_scalar()
    {
       $user = User::factory()->create();
       
        $keep1 = Asset::factory()->create(['company_id' => 1]);
        $drop1 = Asset::factory()->create(['company_id' => 2]);

        $filter = PredefinedFilter::create([
            'name'          => 'company_scalar',
            'created_by'    => $user->id,
            'filter_data'   => ['company_id' => 1],  
        ]);    
        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep1->id));
        $this->assertFalse($ids->contains($drop1->id));
    }  

    /** @test */
    public function it_filers_by_company_id_array()
    {
        $user = User::factory()->create();

        $keep1 = Asset::factory()->create(['company_id' => 1]);
        $keep2 = Asset::factory()->create(['company_id' => 3]);
        $drop1 = Asset::factory()->create(['company_id' => 4]);

        $filter = PredefinedFilter::create([
            'name'          => 'company_scalar',
            'created_by'    => $user->id,
            'filter_data'   =>['company_id' => [1,3]],  
        ]);
        
        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');
        
        $this->assertTrue($ids->contains($keep1->id)); 
        $this->assertTrue($ids->contains($keep2->id));
        $this->assertFalse($ids->contains($drop1->id));
             
    } 
    
    /** @test */
    public function it_filters_by_status_id_scalar()
    {
        $user = User::factory()->create();

        $statusKeep = \App\Models\Statuslabel::factory()->create();
        $statusDrop = \App\Models\Statuslabel::factory()->create();

        $keep = Asset::factory()->create(['status_id' => $statusKeep->id]);
        $drop = Asset::factory()->create(['status_id' => $statusDrop->id]);

        $filter = PredefinedFilter::create([
            'name'          => 'status_scalar',
            'created_by'    => $user->id,
            'filter_data'   => ['status_id' => $statusKeep->id],
        ]);

        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep->id));
        $this->assertFalse($ids->contains($drop->id));
    }

    /** @test */
    public function it_filters_by_status_id_array()
    {
        $user = User::factory()->create();

        $st1 = \App\Models\Statuslabel::factory()->create();
        $st2 = \App\Models\Statuslabel::factory()->create();
        $st3 = \App\Models\Statuslabel::factory()->create();

        $keep1 = Asset::factory()->create(['status_id' => $st1->id]);
        $keep2 = Asset::factory()->create(['status_id' => $st2->id]);
        $drop  = Asset::factory()->create(['status_id' => $st3->id]);

        $filter = PredefinedFilter::create([
            'name'        => 'status_array',
            'created_by'  => $user->id,
            'filter_data' => ['status_id' => [$st1->id, $st2->id]],
        ]);

        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep1->id));
        $this->assertTrue($ids->contains($keep2->id));
        $this->assertFalse($ids->contains($drop->id));
    }
    /** @test */   
    public function it_filters_by_model_id_scalar()
    {
        $user = User::factory()->create();
       
        $m1 = AssetModel::factory()->create();
        $m2 = AssetModel::factory()->create();
        $m3 = AssetModel::factory()->create();

        $keepScalar = Asset::factory()->create(['model_id' => $m1->id]); 
        $dropScalar = Asset::factory()->create(['model_id' => $m2->id]); 

        $filterScalar = PredefinedFilter::create([
            'name'  => 'model_scalar',
            'created_by' => $user->id,
            'filter_data' => ['model_id' => $m1->id], 
        ]); 

        $q1 = Asset::query();
        $filterScalar->filterAssets($q1);
        $ids1 = $q1->pluck('id');

        $this->assertTrue($ids1->contains($keepScalar->id));
        $this->assertFalse($ids1->contains($dropScalar->id));


    } 
    /** @test */   
    public function it_filters_by_model_id_array(){

        $user = User::factory()->create();
        
        $m2 = AssetModel::factory()->create();
        $m3 = AssetModel::factory()->create();

        $keepArr1 = Asset::factory()->create(['model_id' => $m2->id]);
        $keepArr2 = Asset::factory()->create(['model_id' => $m3->id]);
        $dropArr  = Asset::factory()->create();

        $filterArray = PredefinedFilter::create([
            'name'        => 'model_array',
            'created_by'  => $user->id,
            'filter_data' => ['model_id' => [$m2->id, $m3->id]],
        ]);

        $q2 = Asset::query();
        $filterArray->filterAssets($q2);
        $ids2 = $q2->pluck('id');

        $this->assertTrue($ids2->contains($keepArr1->id));
        $this->assertTrue($ids2->contains($keepArr2->id));
        $this->assertFalse($ids2->contains($dropArr->id));
    }

    /** @test */
    public function it_combines_multiple_id_filters_with_and_logic()
    {
        $user = User::factory()->create();

        $stKeep = \App\Models\Statuslabel::factory()->create();
        $stOther = \App\Models\Statuslabel::factory()->create();

        $keep = Asset::factory()->create(['company_id' => 1, 'status_id' => $stKeep->id]);
        $dropCompany = Asset::factory()->create(['company_id' => 2, 'status_id' => $stKeep->id]);
        $dropStatus = Asset::factory()->create(['company_id' => 1, 'status_id' => $stOther->id]);

        $filter = PredefinedFilter::create([
            'name'        => 'and_logic_company_status',
            'created_by'  => $user->id,
            'filter_data' => [
                'company_id' => 1,
                'status_id'  => [$stKeep->id],
            ],
        ]);

        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep->id));
        $this->assertFalse($ids->contains($dropCompany->id));
        $this->assertFalse($ids->contains($dropStatus->id));
    }

    /** @test */
    public function it_filters_by_manufacturer_id_with_join()
    {
       $user = User::factory()->create();
       
       $manufacturer1 = Manufacturer::factory()->create();
       $manufacturer2 = Manufacturer::factory()->create();
       
       $model1 = AssetModel::factory()->create(['manufacturer_id' => $manufacturer1->id]);
       $model2 = AssetModel::factory()->create(['manufacturer_id' => $manufacturer2->id]);

       $keep = Asset::factory()->create(['model_id' => $model1->id]);
       $drop = Asset::factory()->create(['model_id' => $model2->id]);

       $filter = PredefinedFilter::create([
            'name' => 'filer_by_manufaktur',
            'created_by' => $user->id,
            'filter_data' => ['manufacturer_id' => $manufacturer1->id],
       ]);

       $q = Asset::query();
       $filter->filterAssets($q);
       $ids = $q->pluck('assets.id');
       
       $this->assertTrue($ids->contains($keep->id));
       $this->assertFalse($ids->contains($drop->id));
    } 
    
    /** @test */
    public function it_filters_by_created_at_date_range_insclusive()
    {
        $user = User::factory()->create();

        $in = Asset::factory()->create(['created_at' => '2025-01-15']);
        $out1 = Asset::factory()->create(['created_at' => '2024-12-31']);
        $out2 = Asset::factory()->create(['created_at' => '2025-02-01']);

        $filter = PredefinedFilter::create([
            'name'  =>  'filter_by_date',
            'created_by' => $user->id,
            'filter_data' => [
                'created_at_start' => '2025-01-01',
                'created_at_end' => '2025-01-31',        
            ],
        ]);

        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($in->id));
        $this->assertFalse($ids->contains($out1->id));
        $this->assertFalse($ids->contains($out2->id));
    }

    /** @test */
    public function it_filters_by_name_with_like_operator() {

        $user = User::factory()->create();

        $keep = Asset::factory()->create(['name' => 'Dell Latitude 7420']);
        $drop = Asset::factory()->create(['name' => 'HP ProBook 450']);

        $filter = PredefinedFilter::create([
            'name'  =>  'filter_by_date',
            'created_by' => $user->id,
            'filter_data' => ['name' => 'Latitude'],
        ]);

        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep->id));
        $this->assertFalse($ids->contains($drop->id));
    }
    
    /** @test */
    public function it_filter_by_multiple_custom_fields_and_logic()
    {
        $user = User::factory()->create();

        $keep = Asset::factory()->create([
            'asset_tag' => 'TAG-001',
            'serial'    => 'SN-AAA',
        ]);

        $drop1 = Asset::factory()->create([
            'asset_tag' => 'TAG-001',
            'serial'    => 'SN-WRONG',
        ]);

        $drop2 = Asset::factory()->create([
            'asset_tag' => 'TAG-XYZ',
            'serial'    => 'SN-AAA',
        ]);

        $filter = PredefinedFilter::create([
            'name' => 'filter_by_custom:fields',
            'created_by' => $user->id,
            'filter_data' => [
                'custom_fields' => [
                    'asset_tag' => 'TAG-001',
                    'serial'    => 'SN-AAA',
                ],
            ],
        ]);
        
        $q = Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep->id));
        $this->assertFalse($ids->contains($drop1->id));
        $this->assertFalse($ids->contains($drop2->id));
    }
}