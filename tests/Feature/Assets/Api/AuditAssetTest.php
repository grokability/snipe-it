<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->group('auditing');

test('permission required to bulk audit assets', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.asset.audit', Asset::factory()->create()))
        ->assertForbidden();
});

test('that a non existent asset id returns error', function () {
    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit', 123456789))
        ->assertStatusMessageIs('error');
});

test('requires permission to audit asset', function () {
    $asset = Asset::factory()->create();
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.asset.audit', $asset))
        ->assertForbidden();
});

test('legacy asset audit is saved', function () {
    $asset = Asset::factory()->create();
    $future = now()->addMonths(5)->toDateString();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'asset_tag' => $asset->asset_tag,
            'next_audit_date' => $future,
            'note' => 'test',
        ])
        ->assertStatusMessageIs('success')
        ->assertJson(
            [
                'messages' => trans('admin/hardware/message.audit.success'),
                'payload' => [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'note' => 'test',
                ],
            ])
        ->assertStatus(200);

    $asset->refresh();
    expect($asset->next_audit_date)->toEqual($future);
});

test('legacy asset audit defaults to asset tag when audit by field is not sent', function () {
    $asset = Asset::factory()->create();
    $future = now()->addMonths(2)->toDateString();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            // Simulates the new quickscan form where audit_key is posted
            // and the dropdown may remain on default.
            'audit_key' => $asset->asset_tag,
            'next_audit_date' => $future,
            'note' => 'default asset tag',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id)
        ->assertJsonPath('payload.audit_by_field', 'Asset Tag')
        ->assertJsonPath('payload.audit_key', (string) $asset->asset_tag)
        ->assertStatus(200);

    $asset->refresh();
    expect($asset->next_audit_date)->toEqual($future);
});

test('legacy asset audit can find asset by serial when selected', function () {
    $this->settings->enableUniqueSerialNumbers();

    $asset = Asset::factory()->create(['serial' => 'SERIAL-ABC-123']);
    $future = now()->addMonths(2)->toDateString();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => 'serial',
            'audit_key' => $asset->serial,
            'next_audit_date' => $future,
            'note' => 'serial lookup',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id)
        ->assertJsonPath('payload.audit_by_field', 'Serial')
        ->assertJsonPath('payload.audit_key', $asset->serial)
        ->assertStatus(200);

    $asset->refresh();
    expect($asset->next_audit_date)->toEqual($future);
});

test('legacy asset audit resolves 2d barcode scan of hardware show url', function () {
    // URL-shape QR codes are matched regardless of configured target
    // so sites that printed labels under hardware_id and later changed
    // targets can still audit the old stack. #19778
    $this->settings->set([
        'label2_2d_type' => 'qrcode',
        'label2_2d_target' => 'plain_asset_tag',
        'label2_2d_prefix' => '',
    ]);

    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => '2d_barcode',
            'audit_key' => 'https://snipe.example.com/hardware/'.$asset->id,
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id);
});

test('legacy asset audit resolves 2d barcode scan of ht tag url', function () {
    $this->settings->set([
        'label2_2d_type' => 'qrcode',
        'label2_2d_target' => 'ht_tag',
        'label2_2d_prefix' => '',
    ]);

    $asset = Asset::factory()->create(['asset_tag' => 'HT-TAG-001']);

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => '2d_barcode',
            'audit_key' => 'https://snipe.example.com/ht/HT-TAG-001',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id);
});

test('legacy asset audit resolves 2d barcode plain asset id with prefix', function () {
    $this->settings->set([
        'label2_2d_type' => 'qrcode',
        'label2_2d_target' => 'plain_asset_id',
        'label2_2d_prefix' => 'ACME-',
    ]);

    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => '2d_barcode',
            'audit_key' => 'ACME-'.$asset->id,
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id);
});

test('legacy asset audit resolves 2d barcode plain asset tag', function () {
    $this->settings->set([
        'label2_2d_type' => 'qrcode',
        'label2_2d_target' => 'plain_asset_tag',
        'label2_2d_prefix' => '',
    ]);

    $asset = Asset::factory()->create(['asset_tag' => 'PLAIN-TAG-42']);

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => '2d_barcode',
            'audit_key' => 'PLAIN-TAG-42',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id);
});

test('legacy asset audit resolves 2d barcode plain serial number', function () {
    $this->settings->set([
        'label2_2d_type' => 'qrcode',
        'label2_2d_target' => 'plain_serial_number',
        'label2_2d_prefix' => '',
    ]);

    $asset = Asset::factory()->create(['serial' => 'SN-ZZ-9']);

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => '2d_barcode',
            'audit_key' => 'SN-ZZ-9',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.id', $asset->id);
});

test('legacy asset audit rejects 2d barcode scan when target is non unique', function () {
    // Non-unique targets (model name, location name, etc.) can't be
    // used for audit lookup. Fall through to "not found" rather than
    // guessing which asset the admin meant.
    $this->settings->set([
        'label2_2d_type' => 'qrcode',
        'label2_2d_target' => 'plain_model_name',
        'label2_2d_prefix' => '',
    ]);

    Asset::factory()->create();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'audit_by_field' => '2d_barcode',
            'audit_key' => 'Macbook Pro 16-inch',
        ])
        ->assertStatusMessageIs('error');
});

test('asset audit is saved', function () {
    $asset = Asset::factory()->create(['next_audit_date' => now()->subMonth()->toDateString()]);
    $now = now();
    $future = now()->addMonths(3)->toDateString();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit', $asset), [
            'next_audit_date' => $future,
            'note' => 'test',
        ])
        ->assertStatusMessageIs('success')
        ->assertJson(
            [
                'messages' => trans('admin/hardware/message.audit.success'),
                'payload' => [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'note' => 'test',
                ],
            ])
        ->assertStatus(200);

    $this->assertHasTheseActionLogs($asset, ['create', 'audit']);

    $asset->refresh();
    expect(Carbon::parse($asset->last_audit_date)->betweenIncluded($now->copy()->subSecond(), now()->addSecond()))->toBeTrue('Expected last_audit_date to be close to the audit request time.');
    expect($asset->next_audit_date)->toEqual($future);
});

test('audit does not set next audit date if given null', function () {
    $this->settings->setAuditInterval(null);

    $asset = Asset::factory()->create(['next_audit_date' => null]);

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit', $asset), [
            'asset_tag' => $asset->asset_tag,
            // this is the important part
            'next_audit_date' => null,
            'note' => null,
        ])
        ->assertStatusMessageIs('success')
        ->assertStatus(200);

    $asset->refresh();
    expect($asset->next_audit_date)->toBeNull();
});

test('asset name is cleared on audit when clear name is set', function () {
    $asset = Asset::factory()->create(['name' => 'My Asset Name']);

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'asset_tag' => $asset->asset_tag,
            'clear_name' => '1',
        ])
        ->assertStatusMessageIs('success')
        ->assertStatus(200);

    expect($asset->refresh()->name)->toBeNull();
});

test('asset name is not cleared on audit when clear name is not set', function () {
    $asset = Asset::factory()->create(['name' => 'My Asset Name']);

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit.legacy'), [
            'asset_tag' => $asset->asset_tag,
        ])
        ->assertStatusMessageIs('success')
        ->assertStatus(200);

    expect($asset->refresh()->name)->toEqual('My Asset Name');
});

test('audit persists uploaded image and stamps filename on log', function () {
    // Parity with the web audit form: an uploaded `image` on the API
    // audit request should be saved into private_uploads/audits/ and its
    // storage-relative filename recorded on the resulting audit log entry.
    Storage::fake();

    $asset = Asset::factory()->create();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->post(route('api.asset.audit', $asset->id), [
            'note' => 'audit w/ photo',
            'file' => [UploadedFile::fake()->image('audit.jpg')],
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    // Payload echoes back the stored filename.
    $filename = $response->json('payload.image');
    expect($filename)->not->toBeNull();
    expect($filename)->toStartWith('audit-'.$asset->id.'-');
    Storage::assertExists('private_uploads/audits/'.$filename);

    // Filename shows up on the audit log so the audit history view can render it.
    $log = Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first();
    expect($log)->not->toBeNull();
    expect($log->filename)->toBe($filename);
});

test('audit without image does not stamp filename', function () {
    Storage::fake();

    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.audit', $asset->id), [
            'note' => 'no photo',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $log = Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first();
    expect($log)->not->toBeNull();
    expect($log->filename)->toBeNull();
});

test('audit rejects php payload in file upload', function () {
    Storage::fake();

    $asset = Asset::factory()->create();
    $php = UploadedFile::fake()->createWithContent('shell.php', "<?php echo system(\$_GET['cmd']); ?>");

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('api.asset.audit', $asset->id), [
            'note' => 'webshell probe',
            'file' => [$php],
        ])
        ->assertStatusMessageIs('error');

    expect(Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first())->toBeNull();
    expect(Storage::allFiles('private_uploads/audits'))->toBeEmpty();
});

test('audit rejects php payload in legacy image field', function () {
    Storage::fake();

    $asset = Asset::factory()->create();
    $php = UploadedFile::fake()->createWithContent('shell.php', "<?php echo system(\$_GET['cmd']); ?>");

    $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('api.asset.audit', $asset->id), [
            'note' => 'webshell via legacy field',
            'image' => $php,
        ])
        ->assertStatusMessageIs('error');

    expect(Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first())->toBeNull();
    expect(Storage::allFiles('private_uploads/audits'))->toBeEmpty();
});

test('audit still accepts valid image posted via legacy image field', function () {
    Storage::fake();

    $asset = Asset::factory()->create();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->post(route('api.asset.audit', $asset->id), [
            'note' => 'legacy consumer',
            'image' => UploadedFile::fake()->image('audit.jpg'),
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $filename = $response->json('payload.image');
    expect($filename)->not->toBeNull();
    Storage::assertExists('private_uploads/audits/'.$filename);
});
