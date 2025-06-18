<?php

namespace Tests\Feature\Users\Ui\BulkActions;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class BulkDeleteUsersTest extends TestCase
{
    public function test_requires_correct_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('users/bulksave'), [
                'ids' => [
                    User::factory()->create()->id,
                ],
                'status_id' => Statuslabel::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        Asset::factory()->assignedToUser($user)->create();

        $actor = $this->actingAs(User::factory()->editUsers()->create());

        // "ids" required
        $actor->post(route('users/bulksave'), [
            // 'ids' => [
            //     $user->id,
            // ],
            'status_id' => Statuslabel::factory()->create()->id,
        ])->assertSessionHas('error')->assertRedirect();

        // "status_id" needed when provided users have assets associated
        $actor->post(route('users/bulksave'), [
            'ids' => [
                $user->id,
            ],
            // 'status_id' => Statuslabel::factory()->create()->id,
        ])->assertSessionHas('error')->assertRedirect();
    }

    public function test_cannot_perform_bulk_actions_on_self()
    {
        $actor = User::factory()->editUsers()->create();

        $this->actingAs($actor)
            ->post(route('users/bulksave'), [
                'ids' => [
                    $actor->id,
                ],
                'delete_user' => '1',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', trans('general.bulk_checkin_delete_success'));

        $this->assertNotSoftDeleted($actor);
    }

    public function test_accessories_can_be_bulk_checked_in()
    {
        [$accessoryA, $accessoryB] = Accessory::factory()->count(2)->create();
        [$userA, $userB, $userC] = User::factory()->count(3)->create();

        // Add checkouts for multiple accessories to multiple users to get different ids in the mix
        $this->attachAccessoryToUsers($accessoryA, [$userA, $userB, $userC]);
        $this->attachAccessoryToUsers($accessoryB, [$userA, $userB]);

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users/bulksave'), [
                'ids' => [
                    $userA->id,
                    $userC->id,
                ],
                'status_id' => Statuslabel::factory()->create()->id,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue($userA->fresh()->accessories->isEmpty());
        $this->assertTrue($userB->fresh()->accessories->isNotEmpty());
        $this->assertTrue($userC->fresh()->accessories->isEmpty());

        // These assertions check against a bug where the wrong value from
        // accessories_users was being populated in action_logs.item_id.
        $this->assertActionLogCheckInEntryFor($userA, $accessoryA);
        $this->assertActionLogCheckInEntryFor($userA, $accessoryB);
        $this->assertActionLogCheckInEntryFor($userC, $accessoryA);
    }

    public function test_assets_can_be_bulk_checked_in()
    {
        [$userA, $userB, $userC] = User::factory()->count(3)->create();

        $assetForUserA = $this->assignAssetToUser($userA);
        $lonelyAsset = $this->assignAssetToUser($userB);
        $assetForUserC = $this->assignAssetToUser($userC);

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users/bulksave'), [
                'ids' => [
                    $userA->id,
                    $userC->id,
                ],
                'status_id' => Statuslabel::factory()->create()->id,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue($userA->fresh()->assets->isEmpty());
        $this->assertTrue($userB->fresh()->assets->isNotEmpty());
        $this->assertTrue($userC->fresh()->assets->isEmpty());

        $this->assertActionLogCheckInEntryFor($userA, $assetForUserA);
        $this->assertActionLogCheckInEntryFor($userC, $assetForUserC);
    }

    public function test_consumables_can_be_bulk_checked_in()
    {
        [$consumableA, $consumableB] = Consumable::factory()->count(2)->create();
        [$userA, $userB, $userC] = User::factory()->count(3)->create();

        // Add checkouts for multiple consumables to multiple users to get different ids in the mix
        $this->attachConsumableToUsers($consumableA, [$userA, $userB, $userC]);
        $this->attachConsumableToUsers($consumableB, [$userA, $userB]);

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users/bulksave'), [
                'ids' => [
                    $userA->id,
                    $userC->id,
                ],
                'status_id' => Statuslabel::factory()->create()->id,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue($userA->fresh()->consumables->isEmpty());
        $this->assertTrue($userB->fresh()->consumables->isNotEmpty());
        $this->assertTrue($userC->fresh()->consumables->isEmpty());

        // Consumable checkin should not be logged.
        $this->assertNoActionLogCheckInEntryFor($userA, $consumableA);
        $this->assertNoActionLogCheckInEntryFor($userA, $consumableB);
        $this->assertNoActionLogCheckInEntryFor($userC, $consumableA);
    }

    public function test_license_seats_can_be_bulk_checked_in()
    {
        [$userA, $userB, $userC] = User::factory()->count(3)->create();

        $licenseSeatForUserA = LicenseSeat::factory()->assignedToUser($userA)->create();
        $lonelyLicenseSeat = LicenseSeat::factory()->assignedToUser($userB)->create();
        $licenseSeatForUserC = LicenseSeat::factory()->assignedToUser($userC)->create();

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users/bulksave'), [
                'ids' => [
                    $userA->id,
                    $userC->id,
                ],
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', trans('general.bulk_checkin_success'));

        $this->assertDatabaseMissing('license_seats', [
            'license_id' => $licenseSeatForUserA->license->id,
            'assigned_to' => $userA->id,
        ]);

        $this->assertDatabaseMissing('license_seats', [
            'license_id' => $licenseSeatForUserC->license->id,
            'assigned_to' => $userC->id,
        ]);

        // Slightly different from the other assertions since we use
        // the license and not the license seat in this case.
        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkin from',
            'target_id' => $userA->id,
            'target_type' => User::class,
            'note' => 'Bulk checkin items',
            'item_type' => License::class,
            'item_id' => $licenseSeatForUserA->license->id,
        ]);

        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkin from',
            'target_id' => $userC->id,
            'target_type' => User::class,
            'note' => 'Bulk checkin items',
            'item_type' => License::class,
            'item_id' => $licenseSeatForUserC->license->id,
        ]);
    }

    public function test_users_can_be_deleted_in_bulk()
    {
        [$userA, $userB, $userC] = User::factory()->count(3)->create();

        $this->actingAs(User::factory()->editUsers()->create())
            ->post(route('users/bulksave'), [
                'ids' => [
                    $userA->id,
                    $userC->id,
                ],
                'delete_user' => '1',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', trans('general.bulk_checkin_delete_success'));

        $this->assertSoftDeleted($userA);
        $this->assertNotSoftDeleted($userB);
        $this->assertSoftDeleted($userC);
    }

    private function assignAssetToUser(User $user): Asset
    {
        return Asset::factory()->assignedToUser($user)->create();
    }

    private function attachAccessoryToUsers(Accessory $accessory, array $users): void
    {
        foreach ($users as $user) {
            $accessoryCheckout = $accessory->checkouts()->make();
            $accessoryCheckout->assignedTo()->associate($user);
            $accessoryCheckout->save();
        }
    }

    private function attachConsumableToUsers(Consumable $consumable, array $users): void
    {
        foreach ($users as $user) {
            $consumable->users()->attach($consumable->id, [
                'consumable_id' => $consumable->id,
                'assigned_to' => $user->id,
            ]);
        }
    }

    private function assertActionLogCheckInEntryFor(User $user, Model $model): void
    {
        $this->assertDatabaseHas('action_logs', [
            'action_type' => 'checkin from',
            'target_id' => $user->id,
            'target_type' => User::class,
            'note' => 'Bulk checkin items',
            'item_type' => get_class($model),
            'item_id' => $model->id,
        ]);
    }

    private function assertNoActionLogCheckInEntryFor(User $user, Model $model): void
    {
        $this->assertDatabaseMissing('action_logs', [
            'action_type' => 'checkin from',
            'target_id' => $user->id,
            'target_type' => User::class,
            'note' => 'Bulk checkin items',
            'item_type' => get_class($model),
            'item_id' => $model->id,
        ]);
    }
}
