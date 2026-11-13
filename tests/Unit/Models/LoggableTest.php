<?php

namespace Tests\Unit\Models;

use App\Exceptions\MissingLogTarget;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The Loggable trait provides the logCheckout() / logCheckin() helpers used by
 * every checkout-capable model. logCheckout() throws MissingLogTarget when the
 * caller can't hand it a valid target so callers wrapping their work in a
 * DB::transaction can catch it specifically, roll back cleanly, and return a
 * proper 4xx response body instead of leaking an unhandled 500.
 */
class LoggableTest extends TestCase
{
    public function test_user_password_changes_are_masked_in_the_log(): void
    {
        $user = User::factory()->make();
        $user->syncOriginal();
        $user->password = 'changed-password';

        $this->assertSame([
            'password' => ['old' => '*************', 'new' => '*************'],
        ], $user->get_changes());
    }

    public function test_user_changes_only_include_allowed_log_fields(): void
    {
        $user = User::factory()->make(['first_name' => 'Original', 'remember_token' => 'original-token']);
        $user->syncOriginal();
        $user->first_name = 'Updated';
        $user->remember_token = 'changed-token';

        $this->assertSame([
            'first_name' => ['old' => 'Original', 'new' => 'Updated'],
        ], $user->get_changes());
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    #[DataProvider('inventoryModels')]
    public function test_initial_inventory_has_one_create_log_and_does_not_leak_metadata_into_updates(string $modelClass, int $quantity): void
    {
        $creator = User::factory()->create();
        $item = $modelClass::factory()->make(['qty' => $quantity, 'created_by' => $creator->id]);
        $item->setImported(true);

        $this->assertTrue($item->save());

        $log = Actionlog::where('item_type', $modelClass)->where('item_id', $item->id)->sole();
        $line = $item->orderItems()->first();

        $this->assertSame('create', $log->action_type);
        $this->assertSame($quantity, (int) $log->quantity);
        $this->assertSame($line?->id, $log->order_item_id);
        $this->assertSame($quantity > 0 ? 1 : 0, $item->orderItems()->count());
        $this->assertSame($creator->id, $log->created_by);
        $this->assertSame('importer', $log->action_source);

        $item->name = 'Renamed inventory';
        $this->assertTrue($item->save());

        $update = Actionlog::where('item_type', $modelClass)
            ->where('item_id', $item->id)
            ->where('action_type', 'update')
            ->sole();

        $this->assertNull($update->order_item_id);
        $this->assertSame(1, (int) $update->quantity);
    }

    /**
     * @return array<string, array{class-string<Model>, int}>
     */
    public static function inventoryModels(): array
    {
        return [
            'stocked accessory' => [Accessory::class, 4],
            'empty accessory' => [Accessory::class, 0],
            'stocked component' => [Component::class, 4],
            'stocked consumable' => [Consumable::class, 4],
            'empty consumable' => [Consumable::class, 0],
        ];
    }

    /**
     * @param  class-string<Model>  $membershipClass
     */
    #[DataProvider('membershipSyncs')]
    public function test_membership_changes_do_not_merge_into_a_create_log(string $syncMethod, string $membershipClass): void
    {
        $user = User::factory()->create();
        $membership = $membershipClass::factory()->create();

        $user->{$syncMethod}([$membership->id]);

        $logs = Actionlog::where('item_type', User::class)->where('item_id', $user->id)->orderBy('id')->get();

        $this->assertSame(['create', 'update'], $logs->pluck('action_type')->all());
        $this->assertSame([], json_decode($logs->first()->log_meta, true));
    }

    /**
     * @param  class-string<Model>  $membershipClass
     */
    #[DataProvider('membershipSyncs')]
    public function test_a_save_without_changes_clears_the_previous_log_reference(string $syncMethod, string $membershipClass): void
    {
        $user = User::factory()->create(['jobtitle' => 'Engineer']);
        $membership = $membershipClass::factory()->create();
        $user->jobtitle = 'Senior Engineer';
        $this->assertTrue($user->save());
        $this->assertTrue($user->save());

        $user->{$syncMethod}([$membership->id]);

        $logs = Actionlog::where('item_type', User::class)
            ->where('item_id', $user->id)
            ->where('action_type', 'update')
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $logs);
        $this->assertSame(['jobtitle'], array_keys(json_decode($logs->first()->log_meta, true)));
    }

    /**
     * @return array<string, array{string, class-string<Model>}>
     */
    public static function membershipSyncs(): array
    {
        return [
            'companies' => ['syncCompaniesWithLogging', Company::class],
            'groups' => ['syncGroupsWithLogging', Group::class],
        ];
    }

    public function test_log_checkout_throws_missing_log_target_when_target_is_missing()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->expectException(MissingLogTarget::class);

        $asset->logCheckout('some note', null);
    }

    public function test_log_checkout_throws_missing_log_target_when_target_has_no_id()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        // Fresh, unsaved model: a real object but its id is null, so the
        // shape check should refuse to log against it.
        $badTarget = new User;

        $this->expectException(MissingLogTarget::class);

        $asset->logCheckout('some note', $badTarget);
    }
}
