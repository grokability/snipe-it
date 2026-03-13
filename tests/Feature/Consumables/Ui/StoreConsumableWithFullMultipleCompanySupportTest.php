<?php

namespace Tests\Feature\Consumables\Ui;

use App\Models\Category;
use App\Models\Consumable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class StoreConsumableWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testAdheresToFullMultipleCompaniesSupportScoping($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($actor)
            ->post(route('consumables.store'), [
                'name' => 'My Cool Consumable',
                'category_id' => Category::factory()->forConsumables()->create()->id,
                'company_id' => $company->id,
            ]);

        $consumable = Consumable::where('name', 'My Cool Consumable')->sole();

        $assertions($consumable);
    }
}
