<?php

namespace Tests\Feature\Reporting;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use League\Csv\Reader;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class UnacceptedAssetReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro(
            'assertSeeTextInStreamedResponse',
            function (string $needle) {
                Assert::assertTrue(
                    collect(Reader::createFromString($this->streamedContent())->getRecords())
                        ->pluck(0)
                        ->contains($needle)
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertDontSeeTextInStreamedResponse',
            function (string $needle) {
                Assert::assertFalse(
                    collect(Reader::createFromString($this->streamedContent())->getRecords())
                        ->pluck(0)
                        ->contains($needle)
                );

                return $this;
            }
        );
    }


    public function testPermissionRequiredToViewUnacceptedAssetReport()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports/unaccepted_assets'))
            ->assertForbidden();
    }

    public function testUserCanListUnacceptedAssets()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('reports/unaccepted_assets'))
            ->assertOk();
    }
    
}
