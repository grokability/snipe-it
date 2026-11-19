<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->group('auditing');

test('audits all assets and returns per row envelope', function () {
    [$a, $b, $c] = Asset::factory()->count(3)->create();
    $future = now()->addMonths(4)->toDateString();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$a->id, $b->id, $c->id],
            'next_audit_date' => $future,
            'note' => 'bulk audit',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $results = $response->json('results');
    expect($results)->toHaveCount(3);

    foreach ([$a, $b, $c] as $original) {
        $fresh = $original->fresh();
        expect($fresh->next_audit_date)->toEqual($future, "next_audit_date not applied to asset {$original->id}");
        expect($fresh->last_audit_date)->not->toBeNull("last_audit_date not stamped on asset {$original->id}");
    }

    expect($results[0])->toHaveKey('id');
    expect($results[0])->toHaveKey('status');
    expect($results[0])->toHaveKey('messages');
    expect($results[0])->toHaveKey('payload');
    expect($results[0]['status'])->toBe('success');

    // asset_tag is HTML-escaped through e() in the payload; compare as strings.
    expect((string) $results[0]['payload']['asset_tag'])->toBe((string) $a->asset_tag);
});

test('input order is preserved in results', function () {
    [$a, $b, $c] = Asset::factory()->count(3)->create();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$c->id, $a->id, $b->id],
            'note' => 'order check',
        ])
        ->assertOk();

    $ids = array_column($response->json('results'), 'id');
    expect($ids)->toBe([$c->id, $a->id, $b->id]);
});

test('nonexistent id is reported as row error', function () {
    $a = Asset::factory()->create();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$a->id, 999999],
            'note' => 'partial',
        ])
        ->assertOk();

    expect($response->json('status'))->toBe('partial');

    $rows = collect($response->json('results'))->keyBy('id');
    expect($rows[$a->id]['status'])->toBe('success');
    expect($rows[999999]['status'])->toBe('error');
    expect($rows[999999]['payload'])->toBeNull();
});

test('all failures produce overall error status', function () {
    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [999998, 999999],
            'note' => 'nothing exists',
        ])
        ->assertOk();

    expect($response->json('status'))->toBe('error');
    expect($response->json('results'))->toHaveCount(2);

    foreach ($response->json('results') as $row) {
        expect($row['status'])->toBe('error');
    }
});

test('duplicate ids are only processed once', function () {
    $a = Asset::factory()->create();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$a->id, $a->id, $a->id],
            'note' => 'once please',
        ])
        ->assertOk();

    expect($response->json('results'))->toHaveCount(1);
    expect($response->json('results.0.id'))->toBe($a->id);
});

test('missing ids field is a request level validation error', function () {
    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'note' => 'no ids provided',
        ])
        ->assertJsonPath('status', 'error');

    expect($response->json('messages'))->toHaveKey('ids');
    expect($response->json('results'))->toBe([]);
});

test('attaches uploaded image to every row audit log', function () {
    // A single `image` on a bulk audit should be stored once per asset
    // and each asset's audit log entry should reference its own copy —
    // mirroring the web audit form which associates the uploaded image
    // with the individual audit.
    Storage::fake();

    [$a, $b] = Asset::factory()->count(2)->create();

    $response = $this->actingAsForApi(User::factory()->auditAssets()->create())
        ->post(route('api.asset.bulk-audit'), [
            'ids' => [$a->id, $b->id],
            'note' => 'batch w/ photo',
            'file' => [UploadedFile::fake()->image('audit.jpg')],
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $rows = collect($response->json('results'))->keyBy('id');
    foreach ([$a, $b] as $asset) {
        $filename = $rows[$asset->id]['payload']['image'] ?? null;
        expect($filename)->not->toBeNull("no image filename for asset {$asset->id}");
        expect($filename)->toStartWith('audit-'.$asset->id.'-');
        Storage::assertExists('private_uploads/audits/'.$filename);

        $log = Actionlog::where('item_id', $asset->id)->where('action_type', 'audit')->first();
        expect($log->filename)->toBe($filename);
    }
});

test('superuser bypasses fmcs and audits assets in any company', function () {
    // FMCS on, but a superuser is exempt from CompanyableScope — they can
    // see and audit assets in every company. A bulk request touching
    // assets in two different companies should succeed on both rows.
    $this->settings->enableMultipleFullCompanySupport();

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $assetA = Asset::factory()->create(['company_id' => $companyA->id]);
    $assetB = Asset::factory()->create(['company_id' => $companyB->id]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$assetA->id, $assetB->id],
            'note' => 'superuser bulk audit',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $rows = collect($response->json('results'))->keyBy('id');
    expect($rows[$assetA->id]['status'])->toBe('success');
    expect($rows[$assetB->id]['status'])->toBe('success');

    expect($assetA->fresh()->last_audit_date)->not->toBeNull();
    expect($assetB->fresh()->last_audit_date)->not->toBeNull();
});

test('respects fmcs scoping for non superuser', function () {
    // FMCS on, caller in company A tries to audit both an A-owned asset
    // and a B-owned asset. B is hidden by the CompanyableScope on the
    // Asset::whereIn(...) fetch, so it reports as `does_not_exist` in
    // the per-row envelope. Then the per-row Gate::allows('audit', $asset)
    // check inside bulkAudit() acts as belt-and-braces if a future scope
    // change lets the query see it — the row would surface as
    // `unauthorized` instead.
    $this->settings->enableMultipleFullCompanySupport();

    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $userA = User::factory()->auditAssets()->forCompany($companyA->id)->create();
    $assetA = Asset::factory()->create(['company_id' => $companyA->id, 'created_by' => $userA->id]);
    $assetB = Asset::factory()->create(['company_id' => $companyB->id]);

    $response = $this->actingAsForApi($userA)
        ->postJson(route('api.asset.bulk-audit'), [
            'ids' => [$assetA->id, $assetB->id],
            'note' => 'fmcs check',
        ])
        ->assertOk();

    expect($response->json('status'))->toBe('partial');

    $rows = collect($response->json('results'))->keyBy('id');
    expect($rows[$assetA->id]['status'])->toBe('success');
    expect($rows[$assetB->id]['status'])->toBe('error');

    expect($assetA->fresh()->last_audit_date)->not->toBeNull();
    expect($assetB->fresh()->last_audit_date)->toBeNull();
});
