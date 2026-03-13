<?php

namespace Tests\Feature\Console;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use App\Models\Actionlog;
use Tests\TestCase;


class MergeUsersTest extends TestCase
{
    public function testAssetsAreTransferredOnUserMerge()
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        Asset::factory()->count(3)->assignedToUser($user1)->create();
        Asset::factory()->count(3)->assignedToUser($user_to_merge_into)->create();

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        $this->assertEquals(6, $user_to_merge_into->refresh()->assets->count());
        $this->assertEquals(0, $user1->refresh()->assets->count());

    }

    public function testLicensesAreTransferredOnUserMerge(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        LicenseSeat::factory()->count(3)->create(['assigned_to' => $user1->id]);
        LicenseSeat::factory()->count(3)->create(['assigned_to' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->licenses->count());

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        $this->assertEquals(6, $user_to_merge_into->refresh()->licenses->count());
        $this->assertEquals(0, $user1->refresh()->licenses->count());

    }

    public function testAccessoriesTransferredOnUserMerge(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        Accessory::factory()->count(3)->checkedOutToUser($user1)->create();
        Accessory::factory()->count(3)->checkedOutToUser($user_to_merge_into)->create();

        $this->assertEquals(3, $user_to_merge_into->refresh()->accessories->count());

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        $this->assertEquals(6, $user_to_merge_into->refresh()->accessories->count());
        $this->assertEquals(0, $user1->refresh()->accessories->count());

    }

    public function testConsumablesTransferredOnUserMerge(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        Consumable::factory()->count(3)->checkedOutToUser($user1)->create();
        Consumable::factory()->count(3)->checkedOutToUser($user_to_merge_into)->create();

        $this->assertEquals(3, $user_to_merge_into->refresh()->consumables->count());

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        $this->assertEquals(6, $user_to_merge_into->refresh()->consumables->count());
        $this->assertEquals(0, $user1->refresh()->consumables->count());

    }

    public function testFilesAreTransferredOnUserMerge(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user1->id]);
        Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->uploads->count());

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        $this->assertEquals(6, $user_to_merge_into->refresh()->uploads->count());
        $this->assertEquals(0, $user1->refresh()->uploads->count());

    }

    public function testAcceptancesAreTransferredOnUserMerge(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user1->id]);
        Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->acceptances->count());

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        $this->assertEquals(6, $user_to_merge_into->refresh()->acceptances->count());
        $this->assertEquals(0, $user1->refresh()->acceptances->count());

    }

    public function testUserUpdateHistoryIsTransferredOnUserMerge(): void
    {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user_to_merge_into = User::factory()->create(['username' => 'user1@example.com']);

        Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user1->id, 'item_id' => $user1->id]);
        Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user_to_merge_into->id, 'item_id' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->userlog->count());

        $this->artisan('snipeit:merge-users')->assertExitCode(0);

        // This needs to be more than the otherwise expected because the merge action itself is logged for the two merging users
        $this->assertEquals(7, $user_to_merge_into->refresh()->userlog->count());
        $this->assertEquals(1, $user1->refresh()->userlog->count());

    }


}
