<?php

use App\Enums\ActionType;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Actionlog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('requires edit permission', function () {
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => 3,
            'note' => 'restock',
        ])
        ->assertForbidden();
});

test('signed amount writes quantity adjust log', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => 3,
            'note' => 'restock from PO',
            'order_number' => 'PO-API-1',
        ])
        ->assertOk();

    expect((int) $accessory->fresh()->qty)->toBe(8);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(3);
    expect($log->note)->toBe('restock from PO');

    // order_number moved off the log row to the Orders table — the
    // log carries order_id pointing at the newly-created Order.
    $order = $log->orderItem->order;
    expect($order->order_number)->toBe('PO-API-1');
    expect((int) $log->created_by)->toBe($actor->id);
});

test('negative amount decrements', function () {
    $actor = User::factory()->editAccessories()->create();
    $accessory = Accessory::factory()->create(['qty' => 10]);

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => -4,
            'note' => 'damaged units removed',
        ])
        ->assertOk();

    expect((int) $accessory->fresh()->qty)->toBe(6);
});

test('note is required', function () {
    // Snipe-IT convention: FormRequest validation errors on API
    // endpoints return 200 with a status:error body via
    // formatStandardApiResponse (Exceptions\Handler shapes this
    // globally). Below-in-use is different — that one is a
    // controller-level DomainException and returns 422 explicitly.
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), ['amount' => 2])
        ->assertOk()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('messages.note.0', 'The note field is required.');
});

test('zero amount writes audit log without changing qty', function () {
    // Zero delta is an audit-only submission: user counted the shelf
    // and confirmed it still matches the DB, so we record the
    // QuantityAdjust log entry (with quantity=0) for provenance but
    // do not touch the on-hand column.
    $accessory = Accessory::factory()->create(['qty' => 5]);

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => 0,
            'note' => 'shelf count matches',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    expect((int) $accessory->fresh()->qty)->toBe(5);

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect((int) $log->quantity)->toBe(0);
    expect($log->note)->toBe('shelf count matches');
});

test('decrement below currently checked out returns 422', function () {
    $accessory = Accessory::factory()->create(['qty' => 10]);
    AccessoryCheckout::factory()->count(4)->create([
        'accessory_id' => $accessory->id,
        'assigned_type' => User::class,
        'assigned_to' => User::factory()->create()->id,
    ]);

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => -7, // would leave qty=3, below the 4 in use
            'note' => 'oops',
        ])
        ->assertStatus(422);

    expect((int) $accessory->fresh()->qty)->toBe(10);
});

test('receipt attaches to the same log row and surfaces in files tab', function () {
    Storage::fake();

    $accessory = Accessory::factory()->create(['qty' => 5]);
    $uploadsBefore = $accessory->uploads()->count();

    $this->actingAsForApi(User::factory()->editAccessories()->create())
        ->postJson(route('api.accessories.adjust-quantity', $accessory), [
            'amount' => 3,
            'note' => 'restock with invoice',
            'order_number' => 'PO-API-2',
            'file' => UploadedFile::fake()->create('invoice-api.pdf', 32, 'application/pdf'),
        ])
        ->assertOk();

    $log = Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', ActionType::QuantityAdjust->value)
        ->latest('id')
        ->firstOrFail();

    expect($log->filename)->not->toBeNull();
    expect($log->filename)->toEndWith('.pdf');
    expect(Actionlog::where('item_type', Accessory::class)
        ->where('item_id', $accessory->id)
        ->where('action_type', 'uploaded')
        ->count())->toBe(0, 'No separate uploaded log entry should have been created.');
    expect($accessory->uploads()->count())->toBe($uploadsBefore + 1);
});

// Order + OrderItem-side behavior of this endpoint (dedup, blank
// order_number splitting, audit-vs-purchase decision) lives in the
// sibling AdjustAccessoryOrderRecordingTest so both classes stay
// under Codacy's 10-method-per-class ceiling and each is coherent
// about the concern it covers.
