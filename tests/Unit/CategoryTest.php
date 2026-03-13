<?php
namespace Tests\Unit;

use App\Models\Category;
use App\Models\AssetModel;
use App\Models\Asset;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    public function testFailsEmptyValidation()
    {
        // An Asset requires a name, a qty, and a category_id.
        $a = Category::create();
        $this->assertFalse($a->isValid());

        $fields = [
             'name' => 'name',
             'category_type' => 'category type',
         ];
        $errors = $a->getErrors();
        foreach ($fields as $field => $fieldTitle) {
            $this->assertEquals($errors->get($field)[0], "The $fieldTitle field is required.");
        }
    }

    public function testACategoryCanHaveAssets()
    {
       $category = Category::factory()->assetDesktopCategory()->create();

       // Generate 5 models via factory
       $models =  AssetModel::factory()
            ->count(5)
            ->create(
                [
                    'category_id' => $category->id
                ]
        );

        

        // Loop through the models and create 2 assets in each model
       $models->each(function ($model) {
            //dd($model);
            $asset = Asset::factory()
            ->count(2)
            ->create(
                [
                    'model_id' => $model->id,
                ]
            );
            //dd($asset);
        });

        $this->assertCount(5, $category->models);
        $this->assertCount(5, $category->models);
        $this->assertEquals(10, $category->itemCount());
    }
}
