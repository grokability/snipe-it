<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CalendarEvent;
use App\Models\CheckoutAcceptance;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Maintenance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Testing\PendingCommand;

beforeEach(function () {
    $this->runBulkDelete = function (User $admin, Company $company, array $types, string $deleteType): PendingCommand {
        $searchLabel = 'Who are you? Search by username, first or last name.';
        $companiesLabel = 'Which companies would you like to check in and delete items for?';
        $typesLabel = 'What item types would you like to check in and delete?';
        $deleteLabel = 'How should items be deleted?';

        $hasNotifiable = ! empty(array_intersect($types, ['assets', 'licenses', 'accessories', 'components']));

        $cmd = $this->artisan('snipeit:checkin-delete-items')
            ->expectsConfirmation('Is this a dry run?', 'no')
            ->expectsQuestion($searchLabel, $admin->username)
            ->expectsQuestion($searchLabel, (string) $admin->id)
            ->expectsQuestion($companiesLabel, '')
            ->expectsQuestion($companiesLabel, [$company->id])
            ->expectsQuestion($typesLabel, $types)
            ->expectsQuestion($deleteLabel, $deleteType);

        if ($hasNotifiable) {
            $cmd->expectsConfirmation('Should we send checkin notifications?', 'no');
        }

        $cmd->expectsConfirmation('Should we clear related action logs?', 'no')
            ->expectsConfirmation('Should we also delete associated image and upload files?', 'no')
            ->expectsQuestion('Should the selected companies also be deleted?', 'keep')
            ->expectsConfirmation('Should we run a backup before proceeding?', 'no');

        if ($admin->email) {
            $cmd->expectsConfirmation("Send an email report to {$admin->email}?", 'no');
        }

        return $cmd->expectsConfirmation('Are you sure you want to proceed? This cannot be undone.', 'yes');
    };
});
function anyCalendarEventIdFor(string $sourceType, int $sourceId): ?int
{
    $id = CalendarEvent::where('source_type', $sourceType)
        ->where('source_id', $sourceId)
        ->value('id');

    return $id === null ? null : (int) $id;
}

test('hard delete asset purges checkout requests calendar events order items and empty orders', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();

    // warranty_months + purchase_date populate the calendar's
    // warranty.expires lane via the HasCalendarEvents observer,
    // giving us a real event to assert against without racing the
    // observer's own writes.
    $asset = Asset::factory()->for($company)->create([
        'warranty_months' => 12,
        'purchase_date' => now()->subMonth()->toDateString(),
    ]);

    $requester = User::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_type' => Asset::class,
        'requestable_id' => $asset->id,
        'quantity' => 1,
    ]);

    $eventId = anyCalendarEventIdFor(Asset::class, $asset->id);
    expect($eventId)->not->toBeNull('Expected the observer to have published at least one calendar_event for this asset.');

    // Order that becomes empty after cleanup: gets purged.
    $emptyableOrder = Order::create(['order_number' => 'EMPTY-'.uniqid()]);
    $emptyableOrder->created_by = $admin->id;
    $emptyableOrder->save();
    $lineOnEmptyOrder = OrderItem::create([
        'order_id' => $emptyableOrder->id,
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'qty' => 1,
    ]);

    // Order that still has another line after cleanup: kept.
    $mixedOrder = Order::create(['order_number' => 'MIXED-'.uniqid()]);
    $mixedOrder->created_by = $admin->id;
    $mixedOrder->save();
    $lineForThisAsset = OrderItem::create([
        'order_id' => $mixedOrder->id,
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'qty' => 1,
    ]);
    $unrelatedAsset = Asset::factory()->create();
    $lineForOtherAsset = OrderItem::create([
        'order_id' => $mixedOrder->id,
        'item_type' => Asset::class,
        'item_id' => $unrelatedAsset->id,
        'qty' => 1,
    ]);

    ($this->runBulkDelete)($admin, $company, ['assets'], 'hard')->assertExitCode(0);

    $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    expect(CheckoutRequest::withTrashed()->find($request->id))->toBeNull();
    expect(CalendarEvent::withTrashed()->find($eventId))->toBeNull();
    expect(OrderItem::withTrashed()->find($lineOnEmptyOrder->id))->toBeNull();
    expect(OrderItem::withTrashed()->find($lineForThisAsset->id))->toBeNull();

    // Order that lost its only item is purged.
    expect(Order::withTrashed()->find($emptyableOrder->id))->toBeNull();

    // Order that still has another line is kept.
    expect(Order::withTrashed()->find($mixedOrder->id))->not->toBeNull();
    expect(OrderItem::withTrashed()->find($lineForOtherAsset->id))->not->toBeNull();
});

test('hard delete asset purges maintenance calendar events', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();
    $maintenance = Maintenance::factory()->for($asset)->create([
        'start_date' => now()->addDays(7)->toDateString(),
        'completion_date' => now()->addDays(8)->toDateString(),
    ]);

    $eventId = anyCalendarEventIdFor(Maintenance::class, $maintenance->id);
    expect($eventId)->not->toBeNull('Expected the observer to have published a calendar_event for this maintenance.');

    ($this->runBulkDelete)($admin, $company, ['assets'], 'hard')->assertExitCode(0);

    expect(Maintenance::withTrashed()->find($maintenance->id))->toBeNull();
    expect(CalendarEvent::withTrashed()->find($eventId))->toBeNull();
});

test('hard delete license purges checkout requests and calendar events', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();

    // expiration_date drives the license calendar event.
    $license = License::factory()->create([
        'company_id' => $company->id,
        'expiration_date' => now()->addYears(1)->toDateString(),
    ]);

    $requester = User::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_type' => License::class,
        'requestable_id' => $license->id,
        'quantity' => 1,
    ]);

    $eventId = anyCalendarEventIdFor(License::class, $license->id);
    expect($eventId)->not->toBeNull('Expected the observer to have published a calendar_event for this license.');

    ($this->runBulkDelete)($admin, $company, ['licenses'], 'hard')->assertExitCode(0);

    expect(License::withTrashed()->find($license->id))->toBeNull();
    expect(CheckoutRequest::withTrashed()->find($request->id))->toBeNull();
    expect(CalendarEvent::withTrashed()->find($eventId))->toBeNull();
});

test('hard delete accessory purges checkout requests and acceptances and order items', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $accessory = Accessory::factory()->create(['company_id' => $company->id]);

    $requester = User::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_type' => Accessory::class,
        'requestable_id' => $accessory->id,
        'quantity' => 1,
    ]);

    $acceptance = CheckoutAcceptance::factory()->forAccessory()->create([
        'checkoutable_id' => $accessory->id,
    ]);

    $order = Order::create(['order_number' => 'ACC-'.uniqid()]);
    $order->created_by = $admin->id;
    $order->save();
    $line = OrderItem::create([
        'order_id' => $order->id,
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'qty' => 1,
    ]);

    ($this->runBulkDelete)($admin, $company, ['accessories'], 'hard')->assertExitCode(0);

    $this->assertDatabaseMissing('accessories', ['id' => $accessory->id]);
    expect(CheckoutRequest::withTrashed()->find($request->id))->toBeNull();
    expect(CheckoutAcceptance::withTrashed()->find($acceptance->id))->toBeNull();
    expect(OrderItem::withTrashed()->find($line->id))->toBeNull();
    expect(Order::withTrashed()->find($order->id))->toBeNull();
});

test('hard delete consumable purges checkout requests and acceptances', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $consumable = Consumable::factory()->create(['company_id' => $company->id]);

    $requester = User::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_type' => Consumable::class,
        'requestable_id' => $consumable->id,
        'quantity' => 1,
    ]);

    $acceptance = CheckoutAcceptance::factory()->create([
        'checkoutable_type' => Consumable::class,
        'checkoutable_id' => $consumable->id,
    ]);

    ($this->runBulkDelete)($admin, $company, ['consumables'], 'hard')->assertExitCode(0);

    $this->assertDatabaseMissing('consumables', ['id' => $consumable->id]);
    expect(CheckoutRequest::withTrashed()->find($request->id))->toBeNull();
    expect(CheckoutAcceptance::withTrashed()->find($acceptance->id))->toBeNull();
});

test('hard delete component purges order items', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $component = Component::factory()->create(['company_id' => $company->id]);

    $order = Order::create(['order_number' => 'COMP-'.uniqid()]);
    $order->created_by = $admin->id;
    $order->save();
    $line = OrderItem::create([
        'order_id' => $order->id,
        'item_type' => Component::class,
        'item_id' => $component->id,
        'qty' => 1,
    ]);

    ($this->runBulkDelete)($admin, $company, ['components'], 'hard')->assertExitCode(0);

    $this->assertDatabaseMissing('components', ['id' => $component->id]);
    expect(OrderItem::withTrashed()->find($line->id))->toBeNull();
    expect(Order::withTrashed()->find($order->id))->toBeNull();
});

test('hard delete user purges their checkout requests and calendar events', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();

    // end_date is what publishes the user's calendar event.
    $user = User::factory()->forCompany($company->id)->create([
        'end_date' => now()->addYears(1)->toDateString(),
    ]);

    $asset = Asset::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $user->id,
        'requestable_type' => Asset::class,
        'requestable_id' => $asset->id,
        'quantity' => 1,
    ]);

    $eventId = anyCalendarEventIdFor(User::class, $user->id);
    expect($eventId)->not->toBeNull('Expected the observer to have published a calendar_event for this user.');

    ($this->runBulkDelete)($admin, $company, ['users'], 'hard')->assertExitCode(0);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    expect(CheckoutRequest::withTrashed()->find($request->id))->toBeNull();
    expect(CalendarEvent::withTrashed()->find($eventId))->toBeNull();
});

test('soft delete does not purge related records', function () {
    $admin = User::factory()->superuser()->create();
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create([
        'warranty_months' => 12,
        'purchase_date' => now()->subMonth()->toDateString(),
    ]);

    $requester = User::factory()->create();
    $request = CheckoutRequest::factory()->create([
        'user_id' => $requester->id,
        'requestable_type' => Asset::class,
        'requestable_id' => $asset->id,
        'quantity' => 1,
    ]);

    $eventId = anyCalendarEventIdFor(Asset::class, $asset->id);
    expect($eventId)->not->toBeNull();

    ($this->runBulkDelete)($admin, $company, ['assets'], 'soft')->assertExitCode(0);

    // Asset is soft-deleted, related records still present so restore is meaningful.
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    expect(CheckoutRequest::withTrashed()->find($request->id))->not->toBeNull();
    expect(CalendarEvent::withTrashed()->find($eventId))->not->toBeNull();
});
