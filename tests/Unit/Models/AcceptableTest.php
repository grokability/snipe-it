<?php

namespace Tests\Unit\Models;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AcceptableTest extends TestCase
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    #[DataProvider('modelDeletions')]
    public function test_deleting_an_item_clears_only_its_pending_acceptances(string $modelClass, bool $forceDelete): void
    {
        /**
         * @SuppressWarnings(PHPMD.ElseExpression)
         */
        [$item, $other] = Model::withoutEvents(fn () => [
            $modelClass::factory()->create(),
            $modelClass::factory()->create(),
        ]);
        $pending = $this->acceptanceFor($item);
        $accepted = $this->acceptanceFor($item, ['accepted_at' => now()]);
        $declined = $this->acceptanceFor($item, ['declined_at' => now()]);
        $unrelated = $this->acceptanceFor($other);


        if ($forceDelete) {
            $item->forceDelete();
        } else {
            $item->delete();
        }

        $this->assertSoftDeleted($pending);
        $this->assertNotSoftDeleted($accepted);
        $this->assertNotSoftDeleted($declined);
        $this->assertNotSoftDeleted($unrelated);
    }

    /**
     * @return array<string, array{class-string<Model>, bool}>
     */
    public static function modelDeletions(): array
    {
        $cases = [];
        foreach ([Asset::class, Accessory::class, Component::class, Consumable::class, LicenseSeat::class] as $modelClass) {
            $cases[class_basename($modelClass).' soft delete'] = [$modelClass, false];
            $cases[class_basename($modelClass).' force delete'] = [$modelClass, true];
        }

        return $cases;
    }

    public function test_deletion_does_not_clear_another_item_type_with_the_same_id(): void
    {
        [$asset, $consumable] = Model::withoutEvents(function (): array {
            $asset = Asset::factory()->create();

            return [$asset, Consumable::factory()->create(['id' => $asset->id])];
        });
        $pending = $this->acceptanceFor($consumable);

        $asset->delete();

        $this->assertNotSoftDeleted($pending);
    }

    /**
     * @param  array{accepted_at?: \DateTimeInterface, declined_at?: \DateTimeInterface}  $attributes
     */
    protected function acceptanceFor(Model $item, array $attributes = []): CheckoutAcceptance
    {
        return CheckoutAcceptance::factory()
            ->withoutActionLog()
            ->for($item, 'checkoutable')
            ->create($attributes);
    }
}
