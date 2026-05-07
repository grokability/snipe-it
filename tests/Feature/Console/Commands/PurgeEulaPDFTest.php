<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\User;
use Storage;
use Tests\TestCase;

class PurgeEulaPDFTest extends TestCase
{
    public function test_only_purges_acceptances_for_deleted_users(): void
    {
        $intervalDate = now()->subDays(30);

        $company = Company::factory()->create();
        $asset = Asset::factory()->create();
        $otherAsset = Asset::factory()->create();
        $softDeletedUser = User::factory()->create([
            'company_id' => $company->id,
        ]);
        $softDeletedUser->delete();

        $activeUser = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $acceptanceToPurge = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $softDeletedUser->id,
            'signature_filename' => 'signature-to-purge.png',
            'stored_eula_file' => 'eula-to-purge.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        $acceptanceToKeep = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $otherAsset->id,
            'assigned_to_id' => $activeUser->id,
            'signature_filename' => 'signature-to-keep.png',
            'stored_eula_file' => 'eula-to-keep.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        $this->artisan('snipeit:purge-eula-pdfs', [
            '--older-than-days' => 0,
            '--only-deleted-users' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertSoftDeleted('checkout_acceptances', [
            'id' => $acceptanceToPurge->id,
        ]);

        $this->assertDatabaseHas('checkout_acceptances', [
            'id' => $acceptanceToKeep->id,
        ]);
    }

    public function test_only_purges_records_for_the_given_company(): void
    {
        $intervalDate = now()->subDays(30);

        $targetCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $targetAsset = Asset::factory()->create();
        $otherAsset = Asset::factory()->create();

        $userInTargetCompany = User::factory()->create([
            'company_id' => $targetCompany->id,
        ]);
        $userInOtherCompany = User::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $targetAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $targetAsset->id,
            'assigned_to_id' => $userInTargetCompany->id,
            'signature_filename' => 'target-signature.png',
            'stored_eula_file' => 'target-eula.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        $otherAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $otherAsset->id,
            'assigned_to_id' => $userInOtherCompany->id,
            'signature_filename' => 'other-signature.png',
            'stored_eula_file' => 'other-eula.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        Storage::fake('local');

        Storage::put('private_uploads/signatures/target-signature.png', 'fake');
        Storage::put('private_uploads/eula-pdfs/target-eula.pdf', 'fake');

        Storage::put('private_uploads/signatures/other-signature.png', 'fake');
        Storage::put('private_uploads/eula-pdfs/other-eula.pdf', 'fake');

        $this->artisan('snipeit:purge-eula-pdfs', [
            '--older-than-days' => 0,
            '--company-id' => $targetCompany->id,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertSoftDeleted('checkout_acceptances', [
            'id' => $targetAcceptance->id,
        ]);

        $this->assertDatabaseHas('checkout_acceptances', [
            'id' => $otherAcceptance->id,
        ]);
    }

    public function test_only_purges_soft_deleted_users_for_the_given_company(): void
    {
        $intervalDate = now()->subDays(30);

        $targetCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $matchingAsset = Asset::factory()->create();
        $wrongCompanyAsset = Asset::factory()->create();
        $activeUserAsset = Asset::factory()->create();

        $matchingUser = User::factory()->create([
            'company_id' => $targetCompany->id,
        ]);
        $matchingUser->delete();

        $wrongCompanyUser = User::factory()->create([
            'company_id' => $otherCompany->id,
        ]);
        $wrongCompanyUser->delete();

        $activeUserInTargetCompany = User::factory()->create([
            'company_id' => $targetCompany->id,
        ]);
        Storage::fake('local');
        Storage::put('private_uploads/signatures/matching-signature.png', 'fake');
        Storage::put('private_uploads/eula-pdfs/matching-eula.pdf', 'fake');

        Storage::put('private_uploads/signatures/wrong-company-signature.png', 'fake');
        Storage::put('private_uploads/eula-pdfs/wrong-company-eula.pdf', 'fake');

        Storage::put('private_uploads/signatures/active-user-signature.png', 'fake');
        Storage::put('private_uploads/eula-pdfs/active-user-eula.pdf', 'fake');

        $matchingAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $matchingAsset->id,
            'assigned_to_id' => $matchingUser->id,
            'signature_filename' => 'matching-signature.png',
            'stored_eula_file' => 'matching-eula.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        $wrongCompanyAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $wrongCompanyAsset->id,
            'assigned_to_id' => $wrongCompanyUser->id,
            'signature_filename' => 'wrong-company-signature.png',
            'stored_eula_file' => 'wrong-company-eula.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        $activeUserAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $activeUserAsset->id,
            'assigned_to_id' => $activeUserInTargetCompany->id,
            'signature_filename' => 'active-user-signature.png',
            'stored_eula_file' => 'active-user-eula.pdf',
            'updated_at' => $intervalDate->copy()->subDay(),
        ]);

        $this->artisan('snipeit:purge-eula-pdfs', [
            '--older-than-days' => 0,
            '--company-id' => $targetCompany->id,
            '--only-deleted-users' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertSoftDeleted('checkout_acceptances', [
            'id' => $matchingAcceptance->id,
        ]);

        $this->assertDatabaseHas('checkout_acceptances', [
            'id' => $wrongCompanyAcceptance->id,
        ]);

        $this->assertDatabaseHas('checkout_acceptances', [
            'id' => $activeUserAcceptance->id,
        ]);
    }

    public function test_does_not_purge_recent_acceptances_even_for_soft_deleted_users(): void
    {
        $company = Company::factory()->create();

        $softDeletedUser = User::factory()->create([
            'company_id' => $company->id,
        ]);
        $softDeletedUser->delete();

        $recentAsset = Asset::factory()->create();

        Storage::fake('local');
        Storage::put('private_uploads/signatures/recent-signature.png', 'fake');
        Storage::put('private_uploads/eula-pdfs/recent-eula.pdf', 'fake');

        $recentAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $recentAsset->id,
            'assigned_to_id' => $softDeletedUser->id,
            'signature_filename' => 'recent-signature.png',
            'stored_eula_file' => 'recent-eula.pdf',
            'updated_at' => now()->subDay(), // <-- stays recent
        ]);

        $this->artisan('snipeit:purge-eula-pdfs', [
            '--older-than-days' => 0,
            '--only-deleted-users' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('checkout_acceptances', [
            'id' => $recentAcceptance->id,
        ]);
    }
}
