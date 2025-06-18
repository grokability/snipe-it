<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => null,
            'model_id' => AssetModel::factory(),
            'rtd_location_id' => Location::factory(),
            'serial' => $this->faker->uuid(),
            'status_id' => function () {
                return Statuslabel::where('name', 'Ready to Deploy')->first() ?? Statuslabel::factory()->rtd()->create(['name' => 'Ready to Deploy']);
            },
            'created_by' => User::factory()->superuser(),
            'asset_tag' => $this->faker->unixTime('now'),
            'notes'   => 'Created by DB seeder',
            'purchase_date' => $this->faker->dateTimeBetween('-1 years', 'now', date_default_timezone_get())->format('Y-m-d'),
            'purchase_cost' => $this->faker->randomFloat(2, '299.99', '2999.99'),
            'order_number' => (string) $this->faker->numberBetween(1000000, 50000000),
            'supplier_id' => Supplier::factory(),
            'requestable' => $this->faker->boolean(),
            'assigned_to' => null,
            'assigned_type' => null,
            'next_audit_date' => null,
            'last_checkout' => null,
            'asset_eol_date' => null
        ];
    }
   
    
    public function configure()
    {
        return $this->afterMaking(function (Asset $asset) {
            // calculates the EOL date most of the time, but sometimes sets a random date so we have some explicits
            // the explicit boolean gets set in the saving() method on the observer 
            $asset->asset_eol_date = $this->faker->boolean(5) 
                ? CarbonImmutable::parse($asset->purchase_date)->addMonths(rand(0, 20))->format('Y-m-d')
                : CarbonImmutable::parse($asset->purchase_date)->addMonths($asset->model->eol)->format('Y-m-d');
        });
    }

    public function laptopMbp()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Macbook Pro 13"')->first() ?? AssetModel::factory()->mbp13Model();
                },
            ];
        });
    }

    public function laptopMbpPending()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Macbook Pro 13"')->first() ?? AssetModel::factory()->mbp13Model();
                },
                'status_id' => function () {
                    return Statuslabel::where('name', 'Pending')->first() ?? Statuslabel::factory()->pending()->make(['name' => 'Pending']);
                },
            ];
        });
    }

    public function laptopMbpArchived()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Macbook Pro 13"')->first() ?? AssetModel::factory()->mbp13Model();
                },
                'status_id' => function () {
                    return Statuslabel::where('name', 'Archived')->first() ?? Statuslabel::factory()->archived()->make(['name' => 'Archived']);
                },
            ];
        });
    }

    public function laptopAir()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Macbook Air')->first() ?? AssetModel::factory()->mbpAirModel();
                },
            ];
        });
    }

    public function laptopSurface()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Surface')->first() ?? AssetModel::factory()->surfaceModel();
                },
            ];
        });
    }

    public function laptopXps()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'XPS 13')->first() ?? AssetModel::factory()->xps13Model();
                },
            ];
        });
    }

    public function laptopSpectre()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Spectre')->first() ?? AssetModel::factory()->spectreModel();
                },
            ];
        });
    }

    public function laptopZenbook()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'ZenBook UX310')->first() ?? AssetModel::factory()->zenbookModel();
                },
            ];
        });
    }

    public function laptopYoga()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Yoga 910')->first() ?? AssetModel::factory()->yogaModel();
                },
            ];
        });
    }

    public function desktopMacpro()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'iMac Pro')->first() ?? AssetModel::factory()->macproModel();
                },
            ];
        });
    }

    public function desktopLenovoI5()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Lenovo Intel Core i5')->first() ?? AssetModel::factory()->lenovoI5Model();
                },
            ];
        });
    }

    public function desktopOptiplex()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'OptiPlex')->first() ?? AssetModel::factory()->optiplexModel();
                },
            ];
        });
    }

    public function confPolycom()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'SoundStation 2')->first() ?? AssetModel::factory()->polycomModel();
                },
            ];
        });
    }

    public function confPolycomcx()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Polycom CX3000 IP Conference Phone')->first() ?? AssetModel::factory()->polycomcxModel();
                },
            ];
        });
    }

    public function tabletIpad()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'iPad Pro')->first() ?? AssetModel::factory()->ipadModel();
                },
            ];
        });
    }

    public function tabletTab3()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Tab3')->first() ?? AssetModel::factory()->tab3Model();
                },
            ];
        });
    }

    public function phoneIphone11()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'iPhone 11')->first() ?? AssetModel::factory()->iphone11Model();
                },
            ];
        });
    }

    public function phoneIphone12()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'iPhone 12')->first() ?? AssetModel::factory()->iphone12Model();
                },
            ];
        });
    }

    public function ultrafine()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Ultrafine 4k')->first() ?? AssetModel::factory()->ultrafine();
                },
            ];
        });
    }

    public function ultrasharp()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Ultrasharp U2415')->first() ?? AssetModel::factory()->ultrasharp();
                },
            ];
        });
    }

    public function assignedToUser(User $user = null)
    {
        return $this->state(function () use ($user) {
            return [
                'assigned_to' => $user->id ?? User::factory(),
                'assigned_type' => User::class,
                'last_checkout' => now()->subDay(),
            ];
        });
    }

    public function assignedToLocation(Location $location = null)
    {
        return $this->state(function () use ($location) {
            return [
                'assigned_to' => $location->id ?? Location::factory(),
                'assigned_type' => Location::class,
            ];
        });
    }

    public function assignedToAsset()
    {
        return $this->state(function () {
            return [
                'model_id' => 1,
                'assigned_to' => Asset::factory(),
                'assigned_type' => Asset::class,
            ];
        });
    }

    public function requiresAcceptance()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Macbook Pro 13')->first() ?? AssetModel::factory()->mbp13Model();
                },
            ];
        });
    }

    public function doesNotRequireAcceptance()
    {
        return $this->state(function () {
            return [
                'model_id' => AssetModel::factory()->doesNotRequireAcceptance(),
            ];
        });
    }

    public function deleted()
    {
        return $this->state(function () {
            return [
                'model_id' => function () {
                    return AssetModel::where('name', 'Macbook Pro 13')->first() ?? AssetModel::factory()->mbp13Model();
                },
                'deleted_at' => $this->faker->dateTime(),
            ];
        });
    }

    public function requestable()
    {
        $id = Statuslabel::factory()->create([
            'archived'   => false,
            'deployable' => true,
            'pending'    => true,
        ])->id;
        return $this->state(['status_id' => $id, 'requestable' => true]);
    }

    public function nonrequestable()
    {
        $id = Statuslabel::factory()->create([
            'archived'   => true,
            'deployable' => false,
            'pending'    => false,
        ])->id;
        return $this->state(['status_id' => $id, 'requestable' => false]);
    }

    public function noPurchaseOrEolDate()
    {
        return $this->afterCreating(function (Asset $asset) {
            $asset->update([
                'purchase_date' => null,
                'asset_eol_date' => null
            ]);
        });
    }

  
    public function hasEncryptedCustomField(CustomField $field = null)
    {
        return $this->state(function () use ($field) {
            return [
                'model_id' => AssetModel::factory()->hasEncryptedCustomField($field),
            ];
        });
    }

    public function hasMultipleCustomFields(array $fields = null): self
    {
        return $this->state(function () use ($fields) {
            return [
                'model_id' => AssetModel::factory()->hasMultipleCustomFields($fields),
            ];
        });
    }

    /**
     * This allows bypassing model level validation if you want to purposefully
     * create an asset in an invalid state. Validation is turned back on
     * after the model is created via the factory.
     * @return AssetFactory
     */
    public function canBeInvalidUponCreation()
    {
        return $this->afterMaking(function (Asset $asset) {
            $asset->setValidating(false);
        })->afterCreating(function (Asset $asset) {
            $asset->setValidating(true);
        });
    }
}
