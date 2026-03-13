<?php

namespace Tests\Feature\Users\Ui;

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
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        Asset::factory()->count(3)->assignedToUser($user1)->create();
        Asset::factory()->count(3)->assignedToUser($user2)->create();
        Asset::factory()->count(3)->assignedToUser($user_to_merge_into)->create();

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertEquals(9, $user_to_merge_into->refresh()->assets->count());
        $this->assertEquals(0, $user1->refresh()->assets->count());
        $this->assertEquals(0, $user2->refresh()->assets->count());

    }

    public function testLicensesAreTransferredOnUserMerge()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        LicenseSeat::factory()->count(3)->create(['assigned_to' => $user1->id]);
        LicenseSeat::factory()->count(3)->create(['assigned_to' => $user2->id]);
        LicenseSeat::factory()->count(3)->create(['assigned_to' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->licenses->count());

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertEquals(9, $user_to_merge_into->refresh()->licenses->count());
        $this->assertEquals(0, $user1->refresh()->licenses->count());
        $this->assertEquals(0, $user2->refresh()->licenses->count());

    }

    public function testAccessoriesTransferredOnUserMerge()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        Accessory::factory()->count(3)->checkedOutToUser($user1)->create();
        Accessory::factory()->count(3)->checkedOutToUser($user2)->create();
        Accessory::factory()->count(3)->checkedOutToUser($user_to_merge_into)->create();

        $this->assertEquals(3, $user_to_merge_into->refresh()->accessories->count());

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertEquals(9, $user_to_merge_into->refresh()->accessories->count());
        $this->assertEquals(0, $user1->refresh()->accessories->count());
        $this->assertEquals(0, $user2->refresh()->accessories->count());

    }

    public function testConsumablesTransferredOnUserMerge()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        Consumable::factory()->count(3)->checkedOutToUser($user1)->create();
        Consumable::factory()->count(3)->checkedOutToUser($user2)->create();
        Consumable::factory()->count(3)->checkedOutToUser($user_to_merge_into)->create();

        $this->assertEquals(3, $user_to_merge_into->refresh()->consumables->count());

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertEquals(9, $user_to_merge_into->refresh()->consumables->count());
        $this->assertEquals(0, $user1->refresh()->consumables->count());
        $this->assertEquals(0, $user2->refresh()->consumables->count());

    }

    public function testFilesAreTransferredOnUserMerge()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user1->id]);
        Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user2->id]);
        Actionlog::factory()->count(3)->filesUploaded()->create(['item_id' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->uploads->count());

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertEquals(9, $user_to_merge_into->refresh()->uploads->count());
        $this->assertEquals(0, $user1->refresh()->uploads->count());
        $this->assertEquals(0, $user2->refresh()->uploads->count());

    }

    public function testAcceptancesAreTransferredOnUserMerge()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user1->id]);
        Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user2->id]);
        Actionlog::factory()->count(3)->acceptedSignature()->create(['target_id' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->acceptances->count());

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');
        $this->assertEquals(9, $user_to_merge_into->refresh()->acceptances->count());
        $this->assertEquals(0, $user1->refresh()->acceptances->count());
        $this->assertEquals(0, $user2->refresh()->acceptances->count());

    }

    public function testUserUpdateHistoryIsTransferredOnUserMerge()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user_to_merge_into = User::factory()->create();

        Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user1->id, 'item_id' => $user1->id]);
        Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user2->id, 'item_id' => $user2->id]);
        Actionlog::factory()->count(3)->userUpdated()->create(['target_id' => $user_to_merge_into->id, 'item_id' => $user_to_merge_into->id]);

        $this->assertEquals(3, $user_to_merge_into->refresh()->userlog->count());

        $response = $this->actingAs(User::factory()->editUsers()->viewUsers()->create())
            ->post(route('users.merge.save', $user1->id),
                [
                    'ids_to_merge' => [$user1->id, $user2->id],
                    'merge_into_id' => $user_to_merge_into->id
                ])
            ->assertStatus(302)
            ->assertRedirect(route('users.index'));

        $this->followRedirects($response)->assertSee('Success');

        // This needs to be 2 more than the otherwise expected because the merge action itself is logged for the two merging users
        $this->assertEquals(11, $user_to_merge_into->refresh()->userlog->count());
        $this->assertEquals(2, $user1->refresh()->userlog->count());
        $this->assertEquals(2, $user2->refresh()->userlog->count());

    }


}
