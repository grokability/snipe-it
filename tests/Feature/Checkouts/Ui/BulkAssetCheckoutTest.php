<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

class BulkAssetCheckoutTest extends TestCase
{
    public function testRequiresPermission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [1],
                'checkout_to_type' => 'user',
                'assigned_user' => 1,
                'assigned_asset' => null,
                'checkout_at' => null,
                'expected_checkin' => null,
                'note' => null,
            ])
            ->assertForbidden();
    }

    public function testCanBulkCheckoutAssets()
    {
        Mail::fake();

        $assets = Asset::factory()->requiresAcceptance()->count(2)->create();
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $checkoutAt = now()->subWeek()->format('Y-m-d');
        $expectedCheckin = now()->addWeek()->format('Y-m-d');

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->followingRedirects()
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'assigned_asset' => null,
                'checkout_at' => $checkoutAt,
                'expected_checkin' => $expectedCheckin,
                'note' => null,
            ])
            ->assertOk();

        $assets = $assets->fresh();

        $assets->each(function ($asset) use ($expectedCheckin, $checkoutAt, $user) {
            $asset->assignedTo()->is($user);
            $asset->last_checkout = $checkoutAt;
            $asset->expected_checkin = $expectedCheckin;
            $this->assertHasTheseActionLogs($asset, ['create', 'checkout']); //Note: '$this' gets auto-bound in closures, so this does work.
        });

        Mail::assertSent(CheckoutAssetMail::class, 2);
        Mail::assertSent(CheckoutAssetMail::class, function (CheckoutAssetMail $mail) {
            return $mail->hasTo('someone@example.com');
        });
    }

    public function testHandleMissingModelBeingIncluded()
    {
        Mail::fake();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    Asset::factory()->requiresAcceptance()->create()->id,
                    9999999,
                ],
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create(['email' => 'someone@example.com'])->id,
                'assigned_asset' => null,
                'checkout_at' => null,
                'expected_checkin' => null,
                'note' => null,
            ])
            ->assertSessionHas('error', trans_choice('admin/hardware/message.multi-checkout.error', 2));

        try {
            Mail::assertNotSent(CheckoutAssetMail::class);
        } catch (ExpectationFailedException $e) {
            $this->fail('Asset checkout email was sent when the entire checkout failed.');
        }
    }

    public static function checkoutTargets()
    {
        yield 'Checkout to user' => [
            function () {
                return [
                    'type' => 'user',
                    'target' => User::factory()->forCompany()->create(),
                ];
            }
        ];

        yield 'Checkout to asset' => [
            function () {
                return [
                    'type' => 'asset',
                    'target' => Asset::factory()->forCompany()->create(),
                ];
            }
        ];

        yield 'Checkout to location' => [
            function () {
                return [
                    'type' => 'location',
                    'target' => Location::factory()->forCompany()->create(),
                ];
            }
        ];
    }

    #[DataProvider('checkoutTargets')]
    public function test_adheres_to_full_multiple_company_support($data)
    {
        ['type' => $type, 'target' => $target] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        // create two companies
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        // create an asset for each company
        $assetForCompanyA = Asset::factory()->for($companyA)->create();
        $assetForCompanyB = Asset::factory()->for($companyB)->create();

        $this->assertNull($assetForCompanyA->assigned_to, 'Asset should not be assigned before attempting this test case.');
        $this->assertNull($assetForCompanyB->assigned_to, 'Asset should not be assigned before attempting this test case.');

        // attempt to bulk checkout both items to the target
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    $assetForCompanyA->id,
                    $assetForCompanyB->id,
                ],
                'checkout_to_type' => $type,
                "assigned_$type" => $target->id,
            ]);

        // ensure bulk checkout is blocked
        $this->assertNull($assetForCompanyA->fresh()->assigned_to, 'Asset was checked out across companies.');
        $this->assertNull($assetForCompanyB->fresh()->assigned_to, 'Asset was checked out across companies.');

        // ensure redirected back
        $response->assertRedirectToRoute('hardware.bulkcheckout.show');
    }

    #[DataProvider('checkoutTargets')]
    public function test_prevents_checkouts_of_checked_out_items($data)
    {
        ['type' => $type, 'target' => $target] = $data();

        $asset = Asset::factory()->create();
        $checkedOutAsset = Asset::factory()->assignedToUser()->create();
        $existingUserId = $checkedOutAsset->assigned_to;

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    $asset->id,
                    $checkedOutAsset->id,
                ],
                'checkout_to_type' => $type,
                "assigned_$type" => $target->id,
            ]);

        $this->assertEquals(
            $existingUserId,
            $checkedOutAsset->fresh()->assigned_to,
            'Asset was checked out when it should have been prevented.'
        );

        // ensure redirected back
        $response->assertRedirectToRoute('hardware.bulkcheckout.show');
    }
}
