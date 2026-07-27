<?php

namespace Tests\Feature\Account;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class GetStoredEulaTest extends TestCase
{
    public function test_requires_authentication(): void
    {
        // Give the setup middleware a user so it doesn't short-circuit
        // us to /setup before auth has a chance to run.
        User::factory()->create();

        $this->get(route('profile.storedeula.download', ['filename' => 'anything.pdf']))
            ->assertRedirect(route('login'));
    }

    public function test_nonexistent_filename_redirects_with_error(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('profile.storedeula.download', ['filename' => 'does-not-exist.pdf']))
            ->assertRedirect(route('home'));
    }

    /**
     * Regression for #19344: a regular end user without users.view
     * still needs to be able to download the EULA PDF for an asset
     * they themselves accepted. We can't verify the actual file bytes
     * come back without wiring a real file into storage_path(), so we
     * assert on the distinct downstream signal: authorization succeeds
     * (no 403), storage lookup misses, controller redirects back with
     * the "file does not exist" error. That's the exact response an
     * end user would see if their EULA PDF had been purged.
     */
    public function test_end_user_can_reach_download_flow_for_their_own_accepted_eula(): void
    {
        $endUser = User::factory()->create();
        $asset = Asset::factory()->create();
        $filename = 'accepted-user-'.uniqid().'.pdf';

        Actionlog::factory()->create([
            'action_type' => 'accepted',
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $endUser->id,
            'target_type' => User::class,
            'filename' => $filename,
        ]);

        $this->assertFalse($endUser->hasAccess('users.view'));

        $this->from(route('account'))
            ->actingAs($endUser)
            ->get(route('profile.storedeula.download', ['filename' => $filename]))
            ->assertRedirect(route('account'))
            ->assertSessionHas('error', trans('general.file_does_not_exist'));
    }

    public function test_end_user_cannot_download_someone_elses_eula(): void
    {
        $acceptingUser = User::factory()->create();
        $otherEndUser = User::factory()->create();
        $asset = Asset::factory()->create();
        $filename = 'someone-elses-'.uniqid().'.pdf';

        Actionlog::factory()->create([
            'action_type' => 'accepted',
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $acceptingUser->id,
            'target_type' => User::class,
            'filename' => $filename,
        ]);

        $this->actingAs($otherEndUser)
            ->get(route('profile.storedeula.download', ['filename' => $filename]))
            ->assertForbidden();
    }

    public function test_user_with_users_view_permission_can_reach_download_flow_for_any_eula(): void
    {
        $acceptingUser = User::factory()->create();
        $manager = User::factory()->viewUsers()->create();
        $asset = Asset::factory()->create();
        $filename = 'manager-download-'.uniqid().'.pdf';

        Actionlog::factory()->create([
            'action_type' => 'accepted',
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            'target_id' => $acceptingUser->id,
            'target_type' => User::class,
            'filename' => $filename,
        ]);

        $this->from(route('account'))
            ->actingAs($manager)
            ->get(route('profile.storedeula.download', ['filename' => $filename]))
            ->assertRedirect(route('account'))
            ->assertSessionHas('error', trans('general.file_does_not_exist'));
    }

    public function test_filename_that_matches_a_non_acceptance_log_row_is_rejected(): void
    {
        $endUser = User::factory()->create();
        $filename = 'not-a-eula-'.uniqid().'.pdf';

        // A log row with the same filename but action_type != accepted
        // (e.g. a general file upload) must not be treated as an EULA
        // the end user is entitled to.
        Actionlog::factory()->create([
            'action_type' => 'uploaded',
            'item_id' => $endUser->id,
            'item_type' => User::class,
            'target_id' => $endUser->id,
            'target_type' => User::class,
            'filename' => $filename,
        ]);

        $this->actingAs($endUser)
            ->get(route('profile.storedeula.download', ['filename' => $filename]))
            ->assertRedirect(route('home'));
    }
}
