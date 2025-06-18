<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->catchPhrase(),
            'category_type' => 'asset',
            'checkin_email' => true,
            'eula_text' => $this->faker->paragraph(),
            'require_acceptance' => false,
            'use_default_eula' => false,
            'created_by' => User::factory()->superuser(),
            'notes'   => 'Created by DB seeder',
        ];
    }

    // usage: Category::factory()->assetLaptopCategory();
    public function assetLaptopCategory()
    {
        return $this->state([
            'name' => 'Laptops',
            'category_type' => 'asset',
            'require_acceptance' => true,
        ]);
    }

    // usage: Category::factory()->assetDesktopCategory();
    public function assetDesktopCategory()
    {
        return $this->state([
            'name' => 'Desktops',
            'category_type' => 'asset',
            'require_acceptance' => true,
        ]);
    }

    // usage: Category::factory()->assetDisplayCategory();
    public function assetDisplayCategory()
    {
        return $this->state([
            'name' => 'Displays',
            'category_type' => 'asset',
        ]);
    }

     // usage: Category::factory()->assetTabletCategory();
     public function assetTabletCategory()
     {
         return $this->state([
             'name' => 'Tablets',
             'category_type' => 'asset',
         ]);
     }

     // usage: Category::factory()->assetMobileCategory();
     public function assetMobileCategory()
     {
         return $this->state([
             'name' => 'Mobile Phones',
             'category_type' => 'asset',
         ]);
     }

     // usage: Category::factory()->assetConferenceCategory();
     public function assetConferenceCategory()
     {
         return $this->state([
             'name' => 'Conference Phones',
             'category_type' => 'asset',
         ]);
     }


     // usage: Category::factory()->assetVoipCategory();
     public function assetVoipCategory()
     {
         return $this->state([
             'name' => 'VOIP Phones',
             'category_type' => 'asset',
         ]);
     }

     // usage: Category::factory()->accessoryKeyboardCategory();
     public function accessoryKeyboardCategory()
     {
         return $this->state([
             'name' => 'Keyboards',
             'category_type' => 'accessory',
         ]);
     }


     // usage: Category::factory()->accessoryMouseCategory();
     public function accessoryMouseCategory()
     {
         return $this->state([
             'name' => 'Mouse',
             'category_type' => 'accessory',
         ]);
     }

     // usage: Category::factory()->componentHddCategory();
     public function componentHddCategory()
     {
         return $this->state([
             'name' => 'HDD/SSD',
             'category_type' => 'component',
         ]);
     }

     // usage: Category::factory()->componentRamCategory();
     public function componentRamCategory()
     {
         return $this->state([
             'name' => 'RAM',
             'category_type' => 'component',
         ]);
     }

     // usage: Category::factory()->consumablePaperCategory();
     public function consumablePaperCategory()
     {
         return $this->state([
             'name' => 'Printer Paper',
             'category_type' => 'consumable',
         ]);
     }

     // usage: Category::factory()->consumableInkCategory();
     public function consumableInkCategory()
     {
         return $this->state([
             'name' => 'Printer Ink',
             'category_type' => 'consumable',
         ]);
     }

     // usage: Category::factory()->licenseGraphicsCategory();
     public function licenseGraphicsCategory()
     {
         return $this->state([
             'name' => 'Graphics Software',
             'category_type' => 'license',
         ]);
     }

     // usage: Category::factory()->licenseGraphicsCategory();
     public function licenseOfficeCategory()
     {
         return $this->state([
             'name' => 'Office Software',
             'category_type' => 'license',
         ]);
     }

    public function forAccessories()
    {
        return $this->state([
            'category_type' => 'accessory',
        ]);
    }

    public function forAssets()
    {
        return $this->state([
            'category_type' => 'asset',
        ]);
    }

    public function forLicenses()
    {
        return $this->state([
            'category_type' => 'license',
        ]);
    }

    public function forComponents()
    {
        return $this->state([
            'category_type' => 'component',
        ]);
    }

    public function forConsumables()
    {
        return $this->state([
            'category_type' => 'consumable',
        ]);
    }

    public function doesNotRequireAcceptance()
    {
        return $this->state([
            'require_acceptance' => false,
        ]);
    }
}
