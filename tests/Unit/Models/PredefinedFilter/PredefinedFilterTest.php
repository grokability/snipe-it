<?php
namespace Tests\Unit\Models\PredefinedFilter;

use App\Models\User;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use Database\Seeders\AssetSeeder;
use App\Models\Category;
use App\Models\PredefinedFilter;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

class PredefinedFilterTest extends TestCase {

  use RefreshDatabase;

  // protected \Illuminate\Support\Collection $assets;
    protected Builder $assets;
  protected User $user;

  protected function setUp(): void {
    parent::setUp();

    $user = User::create([
      'first_name' => 'Filter',
      'last_name'=> 'Predefined',
      'username' => 'filter',
      'email'=> 'predefined@filter.com',
      'password'=> Hash::make('filterfilter'),
    ]);

    $category = Category::create([
      'name' => 'category_for_test',
      'category_type' => 'asset',
      'checkin_email' => true,
      'created_by' => $user->id,
    ]);

    $assetModel = AssetModel::create([
      'created_by' => $user,
      'name' => 'asset_model_for_test',
      'category_id' => $category->id,
    ]);

    $statuslabel = Statuslabel::create([
      'name'      => 'statusLabel_for_test',
      'created_at' => '2025-08-12',
      'updated_at' => '2025-08-13',
      'created_by' => $user->id,
      'deleted_at' => null,
      'deployable' => 1,
      'pending' => 0,
      'archived' => 0,
    ]);

    Asset::create([
      "name"=> "asset_for_test01",
      "model_id"=> $assetModel->id,
      "status_id" => $statuslabel->id,
      'asset_tag' => 12312312301,
      "created_at" => "2024-08-10",
    ]);
    Asset::create([
      "name"=> "asset_for_test02",
      "model_id"=> $assetModel->id,
      "status_id" => $statuslabel->id,
      'asset_tag' => 12312312302,
      "created_at" => "2026-08-12",
    ]);
    Asset::create([
      "name"=> "asset_for_test03",
      "model_id"=> $assetModel->id,
      "status_id" => $statuslabel->id,
      'asset_tag' => 12312312303,
      "created_at" => "2026-08-12",
    ]);

    $this->user = $user;
    $this->assets = Asset::query();
  }

  public function testSetUp() {
    $this->assertCount(3, $this->assets->get());
  }

  public function testCreatedStart() {
    $assets = $this->assets;
    $this->assertCount(3, $assets->get());

    $predefinedFilter = PredefinedFilter::create([
      'name' => 'test_created_start',
      'created_by' => $this->user->id,
      'created_start' => '2025-08-11',
    ]);

    $assets = $predefinedFilter->filterAssets($assets);
    $this->assertCount(2, $assets->get());
  }

  public function testCreatedEnd() {
    $assets = $this->assets;
    $this->assertCount(3, $assets->get());
    $predefinedFilter = PredefinedFilter::create([
      'name' => 'test_created_end',
      'created_by' => $this->user->id,
      'created_end' => '2025-08-11',
    ]);

    $assets = $predefinedFilter->filterAssets($assets);
    $this->assertCount(1, $assets->get());
  }
}
