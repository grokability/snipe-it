<?php

namespace Database\Factories;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Depreciation;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

class AssetModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssetModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_by' => User::factory()->superuser(),
            'name' => $this->faker->catchPhrase(),
            'category_id' => Category::factory(),
            'model_number' => $this->faker->creditCardNumber(),
            'notes' => 'Created by demo seeder',
            'require_serial' => 0,

        ];
    }

    public function mbp13Model()
    {
        return $this->state(function () {
            return [
                'name' => 'Macbook Pro 13"',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'mbp.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function mbpAirModel()
    {
        return $this->state(function () {
            return [
                'name' => 'Macbook Air',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'macbookair.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function surfaceModel()
    {
        return $this->state(function () {
            return [
                'name' => 'Surface',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Microsoft')->first() ?? Manufacturer::factory()->microsoft();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'surface.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function xps13Model()
    {
        return $this->state(function () {
            return [
                'name' => 'XPS 13',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Dell')->first() ?? Manufacturer::factory()->dell();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'xps.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function zenbookModel()
    {
        return $this->state(function () {
            return [
                'name' => 'ZenBook UX310',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Asus')->first() ?? Manufacturer::factory()->asus();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'zenbook.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function spectreModel()
    {
        return $this->state(function () {
            return [
                'name' => 'Spectre',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'HP')->first() ?? Manufacturer::factory()->hp();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'spectre.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function yogaModel()
    {
        return $this->state(function () {
            return [
                'name' => 'Yoga 910',
                'category_id' => function () {
                    return Category::where('name', 'Laptops')->first() ?? Category::factory()->assetLaptopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Lenovo')->first() ?? Manufacturer::factory()->lenovo();
                },
                'eol' => '36',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'yoga.png',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function macproModel()
    {
        return $this->state(function () {
            return [
                'name' => 'iMac Pro',
                'category_id' => function (){
                    return Category::where('name', 'Desktops')->first() ?? Category::factory()->assetDesktopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'eol' => '24',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'imacpro.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function lenovoI5Model()
    {
        return $this->state(function () {
            return [
                'name' => 'Lenovo Intel Core i5',
                'category_id' => function () {
                    return Category::where('name', 'Desktops')->first() ?? Category::factory()->assetDesktopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Lenovo')->first() ?? Manufacturer::factory()->lenovo();
                },
                'eol' => '24',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'lenovoi5.png',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function optiplexModel()
    {
        return $this->state(function () {
            return [
                'name' => 'OptiPlex',
                'category_id' => function (){
                    return Category::where('name', 'Desktops')->first() ?? Category::factory()->assetDesktopCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Dell')->first() ?? Manufacturer::factory()->dell();
                },
                'model_number' => '5040 (MRR81)',
                'eol' => '24',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'optiplex.jpg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Laptops and Desktops')->first() ?? CustomFieldset::factory()->computer();
                },
            ];
        });
    }

    public function polycomModel()
    {
        return $this->state(function () {
            return [
                'name' => 'SoundStation 2',
                'category_id' => function () {
                    return Category::where('name', 'VOIP Phones')->first() ?? Category::factory()->assetVoipCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Polycom')->first() ?? Manufacturer::factory()->polycom();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'soundstation.jpg',
            ];
        });
    }

    public function polycomcxModel()
    {
        return $this->state(function () {
            return [
                'name' => 'Polycom CX3000 IP Conference Phone',
                'category_id' => function () {
                    return Category::where('name', 'VOIP Phones')->first() ?? Category::factory()->assetVoipCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Polycom')->first() ?? Manufacturer::factory()->polycom();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'cx3000.png',
            ];
        });
    }

    public function ipadModel()
    {
        return $this->state(function () {
            return [
                'name' => 'iPad Pro',
                'category_id' => function () {
                    return Category::where('name', 'Tablets')->first() ?? Category::factory()->assetTabletCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'ipad.jpg',
            ];
        });
    }

    public function tab3Model()
    {
        return $this->state(function () {
            return [
                'name' => 'Tab3',
                'category_id' => function () {
                    return Category::where('name', 'Tablets')->first() ?? Category::factory()->assetTabletCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Lenovo')->first() ?? Manufacturer::factory()->lenovo();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'tab3.png',
            ];
        });
    }

    public function iphone11Model()
    {
        return $this->state(function () {
            return [
                'name' => 'iPhone 11',
                'category_id' => function () {
                    return Category::where('name', 'Mobile Phones')->first() ?? Category::factory()->assetMobileCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Mobile Phone Depreciation')->first() ?? Depreciation::factory()->mobilePhones();
                },
                'image' => 'iphone11.jpeg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Mobile Devices')->first() ?? CustomFieldset::factory()->mobile();
                },
            ];
        });
    }

    public function iphone12Model()
    {
        return $this->state(function () {
            return [
                'name' => 'iPhone 12',
                'category_id' => function () {
                    return Category::where('name', 'Mobile Phones')->first() ?? Category::factory()->assetMobileCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Apple')->first() ?? Manufacturer::factory()->apple();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Computer Depreciation')->first() ?? Depreciation::factory()->computer();
                },
                'image' => 'iphone12.jpeg',
                'fieldset_id' => function () {
                    return CustomFieldset::where('name', 'Mobile Devices')->first() ?? CustomFieldset::factory()->mobile();
                },
            ];
        });
    }

    public function ultrafine()
    {
        return $this->state(function () {
            return [
                'name' => 'Ultrafine 4k',
                'category_id' => function () {
                    return Category::where('name', 'Displays')->first() ?? Category::factory()->assetDisplayCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'LG')->first() ?? Manufacturer::factory()->lg();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Display Depreciation')->first() ?? Depreciation::factory()->display();
                },
                'image' => 'ultrafine.jpg',
            ];
        });
    }

    public function ultrasharp()
    {
        return $this->state(function () {
            return [
                'name' => 'Ultrasharp U2415',
                'category_id' => function () {
                    return Category::where('name', 'Displays')->first() ?? Category::factory()->assetDisplayCategory();
                },
                'manufacturer_id' => function () {
                    return Manufacturer::where('name', 'Dell')->first() ?? Manufacturer::factory()->dell();
                },
                'eol' => '12',
                'depreciation_id' => function () {
                    return Depreciation::where('name', 'Display Depreciation')->first() ?? Depreciation::factory()->display();
                },
                'image' => 'ultrasharp.jpg',
            ];
        });
    }

    public function hasEncryptedCustomField(CustomField $field = null)
    {
        return $this->state(function () use ($field) {
            return [
                'fieldset_id' => CustomFieldset::factory()->hasEncryptedCustomField($field),
            ];
        });
    }

    public function hasMultipleCustomFields(array $fields = null)
    {
        return $this->state(function () use ($fields) {
            return [
                'fieldset_id' => CustomFieldset::factory()->hasMultipleCustomFields($fields),
            ];
        });
    }

    public function doesNotRequireAcceptance()
    {
        return $this->state(function () {
            return [
                'category_id' => Category::factory()->doesNotRequireAcceptance(),
            ];
        });
    }
}
