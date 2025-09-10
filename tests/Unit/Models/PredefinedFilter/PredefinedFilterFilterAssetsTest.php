<?php
namespace Tests\Unit\Models\PredefinedFilter;

use App\Models\Asset;
use App\Models\PredefinedFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User; 
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

    /** @test */
    public function test_it_returns_all_assets_when_filter_data_is_null() 
    {
        $a = Asset::factory()->create();
        $b = Asset::factory()->create();
        
        $filter = PredefinedFilter::create([
            'name' =>   'null_filter',
            'created_by' => $this->user->id,
            'filter_data' => [], 
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
            'name'  => 'ignore_empty_values',
            'created_by' => $this->user->id,
            'filter_data' => [
                'company_id'    =>  '',
                'status_id'     => null,
                'model_id'  =>  [], 
                'custome_fields'    =>  [], 

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
            'name'  => 'unkown_keys',
            'created_by' => $this->user->id,
            'filter_data' => [
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
            'name'       => 'cast_check',
            'created_by' => $this->user->id,
            'filter_data'=> ['status_id' => [1,2,3]],
        ]);

        $this->assertIsArray($filter->filter_data);
        $this->assertEquals([1,2,3], $filter->filter_data['status_id']);
    }

    /** @test  */
    public function it_filters_by_company_id_scalar()
    {
       $user = \App\Models\User::factory()->create();
       
        $keep1 = \App\Models\Asset::factory()->create(['company_id' => 1]);
        $drop1 = \App\Models\Asset::factory()->create(['company_id' => 2]);

        $filter = \App\Models\PredefinedFilter::create([
            'name'  => 'company_scalar',
            'created_by' => $user->id,
            'filter_data' => ['company_id' => 1],  
        ]);    
        $q = \App\Models\Asset::query();
        $filter->filterAssets($q);
        $ids = $q->pluck('id');

        $this->assertTrue($ids->contains($keep1->id));
        $this->assertFalse($ids->contains($drop1->id));
    }  
}
