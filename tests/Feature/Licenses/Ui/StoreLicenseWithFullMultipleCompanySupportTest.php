<?php

namespace Tests\Feature\Licenses\Ui;

use App\Models\Category;
use App\Models\License;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting;
use Tests\TestCase;

class StoreLicenseWithFullMultipleCompanySupportTest extends TestCase
{
    use ProvidesDataForFullMultipleCompanySupportTesting;

    #[DataProvider('dataForFullMultipleCompanySupportTesting')]
    public function testAdheresToFullMultipleCompaniesSupportScoping($data)
    {
        ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAs($actor)
            ->post(route('licenses.store'), [
                'name' => 'My Cool License',
                'seats' => '1',
                'category_id' => Category::factory()->forLicenses()->create()->id,
                'company_id' => $company->id,
            ]);

        $license = License::where('name', 'My Cool License')->sole();

        $assertions($license);
    }
}
