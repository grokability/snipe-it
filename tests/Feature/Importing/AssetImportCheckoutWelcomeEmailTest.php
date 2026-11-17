<?php

namespace Tests\Feature\Importing;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Import;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

/**
 * Pin the welcome-email behavior for non-user imports that check items
 * out to users. Send-welcome is honored by asset / accessory / consumable
 * / license importers via the shared Importer::maybeSendWelcomeEmail
 * helper. The email fires ONLY when the target user was newly created by
 * this row (wasRecentlyCreated). Existing-user matches do not retrigger.
 */
class AssetImportCheckoutWelcomeEmailTest extends TestCase
{
    use CleansUpImportFiles;

    private function importCsv(string $csv, array $mappings, array $extraPayload = []): void
    {
        Category::factory()->create(['category_type' => 'asset']);
        Location::factory()->create();
        Statuslabel::factory()->create();
        Company::factory()->create();
        AssetModel::factory()->create();

        $importer = User::factory()->canImport()->create();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('welcome.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), array_merge([
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => $mappings,
            ], $extraPayload))
            ->assertSuccessful();
    }

    #[Test]
    public function fires_when_checkout_creates_a_new_user_and_the_flag_is_on(): void
    {
        Notification::fake();

        Asset::factory()->create(['asset_tag' => 'WELCOME-001', 'assigned_to' => null]);

        $csv = "asset tag,checkout user,first name,last name,email\n";
        $csv .= "WELCOME-001,welcome_new,Welcome,New,welcome.new@example.org\n";

        $this->importCsv(
            $csv,
            [
                'asset tag' => 'asset_tag',
                'checkout user' => 'checkout_user',
                'first name' => 'first_name',
                'last name' => 'last_name',
                'email' => 'email',
            ],
            ['send-welcome' => true],
        );

        $newUser = User::where('username', 'welcome_new')->firstOrFail();
        Notification::assertSentTo($newUser, WelcomeNotification::class);
    }

    #[Test]
    public function does_not_fire_for_existing_users_even_when_the_flag_is_on(): void
    {
        Notification::fake();

        $existing = User::factory()->create([
            'username' => 'existing_user',
            'email' => 'existing@example.org',
        ]);
        Asset::factory()->create(['asset_tag' => 'WELCOME-002', 'assigned_to' => null]);

        $csv = "asset tag,checkout user\n";
        $csv .= "WELCOME-002,{$existing->username}\n";

        $this->importCsv(
            $csv,
            [
                'asset tag' => 'asset_tag',
                'checkout user' => 'checkout_user',
            ],
            ['send-welcome' => true],
        );

        Notification::assertNotSentTo($existing, WelcomeNotification::class);
    }

    #[Test]
    public function does_not_fire_when_the_flag_is_off_even_for_a_new_user(): void
    {
        Notification::fake();

        Asset::factory()->create(['asset_tag' => 'WELCOME-003', 'assigned_to' => null]);

        $csv = "asset tag,checkout user,first name,last name,email\n";
        $csv .= "WELCOME-003,welcome_off,Welcome,Off,welcome.off@example.org\n";

        $this->importCsv(
            $csv,
            [
                'asset tag' => 'asset_tag',
                'checkout user' => 'checkout_user',
                'first name' => 'first_name',
                'last name' => 'last_name',
                'email' => 'email',
            ],
            // no send-welcome key
        );

        $newUser = User::where('username', 'welcome_off')->firstOrFail();
        Notification::assertNotSentTo($newUser, WelcomeNotification::class);
    }
}
