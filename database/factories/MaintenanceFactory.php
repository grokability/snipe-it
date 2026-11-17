<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Maintenance>
     */
    protected $model = Maintenance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    /**
     * Short, realistic maintenance names so seeded / demo rows look
     * plausible in the list, calendar, and other views instead of the
     * Faker `sentence(3)` output ("Ipsum quos aut voluptatum.") that
     * was there before.
     */
    private const NAMES = [
        'Battery replacement',
        'Screen repair',
        'Keyboard replacement',
        'Fan replacement',
        'Firmware update',
        'OS reinstall',
        'Cleaning',
        'RAM upgrade',
        'Hard drive replacement',
        'Warranty repair',
        'Annual service',
        'Diagnostic check',
        'Screen calibration',
        'Cable replacement',
        'Charging port repair',
    ];

    /**
     * Define the model's default state.
     *
     * Default state is a plain-active maintenance (no expected end,
     * no completion) so existing feature tests that call
     * ->factory()->create() and rely on that shape still pass. The
     * ->realistic() state below is what the seeder uses for
     * demo/dev data — anchored dates, expected end, and sometimes
     * completed_at populated so the calendar and list views show a
     * plausible mix.
     *
     * @return array
     */
    public function definition()
    {
        $maintenanceType = MaintenanceType::factory()->create();

        return [
            // Set location_id to rtd_location_id on the generated asset so
            // seeded maintenance rows point at assets with a real location,
            // matching what snipeit:sync-asset-locations would have set.
            //
            // Use item_id + item_type (the polymorphic FK) rather than the
            // legacy asset_id so callers can override the target with their
            // own asset instance and skip the default factory-created one.
            // A supplied asset_id routes through the model's
            // setAssetIdAttribute mutator at save time. The closure below
            // detects that up front and skips the default asset spawn so
            // we don't leak a spurious asset row for cross-cutting counts
            // to trip over (assetsPastEol on the Needs Attention widget,
            // source_id collisions in the calendar events API test, etc.).
            'item_id' => function (array $attributes) {
                if (array_key_exists('asset_id', $attributes)) {
                    return null;
                }

                return Asset::factory()->laptopZenbook()->afterMaking(function (Asset $asset) {
                    if ($asset->location_id === null) {
                        $asset->location_id = $asset->rtd_location_id;
                    }
                });
            },
            'item_type' => Asset::class,
            'supplier_id' => Supplier::factory(),
            'maintenance_type_id' => $maintenanceType->id,
            'asset_maintenance_type' => $maintenanceType->name,
            'name' => $this->faker->randomElement(self::NAMES),
            'start_date' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'is_warranty' => $this->faker->boolean(),
            'notes' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'cost' => $this->faker->randomFloat(2, 5, 500),
            'created_by' => User::factory()->superuser(),
            'image' => $this->faker->numberBetween(1, 11).'.png',
        ];
    }

    /**
     * Seeder-friendly state that produces a maintenance with
     * start_date anchored around today, an expected_completion_date
     * a few days out, and a ~40% chance of already being completed
     * (for past-start rows only). Produces a plausible mix on the
     * calendar / list views so demo installs don't render as
     * "everything from 1974".
     */
    public function realistic(): static
    {
        return $this->state(function () {
            $startDate = $this->faker->dateTimeBetween('-6 months', '+3 months');
            $startCarbon = \Carbon\Carbon::instance($startDate);
            $expectedEnd = (clone $startCarbon)->addDays($this->faker->numberBetween(1, 21));

            $isCompletable = $startCarbon->isPast() && $this->faker->boolean(40);
            $completedAt = $isCompletable
                ? $this->faker->dateTimeBetween($startCarbon, min($expectedEnd, \Carbon\Carbon::now()))
                : null;

            return [
                'start_date' => $startCarbon->format('Y-m-d H:i:s'),
                'expected_completion_date' => $expectedEnd->format('Y-m-d H:i:s'),
                'completed_at' => $completedAt?->format('Y-m-d H:i:s'),
            ];
        });
    }
}
