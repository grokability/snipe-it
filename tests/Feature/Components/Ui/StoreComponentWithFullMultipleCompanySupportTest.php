<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Category;
use App\Models\Component;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class StoreComponentWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testAdheresToFullMultipleCompaniesSupportScoping($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($actor)
            ->post(route('components.store'), [
                'name' => 'My Cool Component',
                'qty' => '1',
                'category_id' => Category::factory()->create()->id,
                'company_id' => $company->id,
            ]);

        $component = Component::where('name', 'My Cool Component')->sole();

        $assertions($component);
    }
}
