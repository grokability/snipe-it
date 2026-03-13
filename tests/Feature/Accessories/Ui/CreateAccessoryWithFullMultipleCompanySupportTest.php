<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Category;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class CreateAccessoryWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testAdheresToFullMultipleCompaniesSupportScoping($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($actor)
            ->post(route('accessories.store'), [
                'redirect_option' => 'index',
                'name' => 'My Cool Accessory',
                'qty' => '1',
                'category_id' => Category::factory()->create()->id,
                'company_id' => $company->id,
            ]);

        $accessory = Accessory::withoutGlobalScopes()->where([
            'name' => 'My Cool Accessory',
        ])->sole();

        $assertions($accessory);
    }
}
