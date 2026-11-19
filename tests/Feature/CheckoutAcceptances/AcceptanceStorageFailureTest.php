<?php

use App\Models\CheckoutAcceptance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Regression coverage for Christopher Finks (christopherfi-dev) Issue 6:
 * AcceptanceController::store used Storage::put for the signature image and
 * for the acceptance PDF without checking the return value. On non-throwing
 * filesystem configurations a silent put() failure returned false, but the
 * flow continued into $acceptance->accept(), populating accepted_at, the
 * signature/EULA filename fields, the "accepted" action log, and the
 * downstream notifications, even though the evidence files were absent
 * from storage. The application then presented a completed acceptance
 * whose evidence file did not exist.
 *
 * The fix checks both put() returns explicitly and short-circuits with a
 * user-visible error before advancing acceptance state.
 */
function acceptPayloadWithSignature(): array
{
    // AcceptanceController rejects signature_output whose decoded
    // bytes are not a real image, so this test needs a legitimate tiny
    // PNG to exercise the failure path. The actual image content is irrelevant to the test.
    $canvas = imagecreatetruecolor(2, 2);
    ob_start();
    imagepng($canvas);
    $signaturePng = (string) ob_get_clean();

    return [
        'asset_acceptance' => 'accepted',
        'signature_output' => 'data:image/png;base64,'.base64_encode($signaturePng),
    ];
}

function mockStorageToFailPut(): void
{
    // Storage::put on the default disk returns false; every other
    // Storage call passes through so exists / makeDirectory continue
    // to work for the pre-flight directory checks in AcceptanceController.
    Storage::fake();
    $default = Storage::disk();

    $proxy = Mockery::mock($default);
    $proxy->shouldReceive('put')->andReturn(false);
    $proxy->shouldReceive('exists')->andReturnUsing(fn (...$a) => $default->exists(...$a));
    $proxy->shouldReceive('makeDirectory')->andReturnUsing(fn (...$a) => $default->makeDirectory(...$a));

    Storage::shouldReceive('disk')->andReturn($proxy);
    Storage::shouldReceive('exists')->andReturnUsing(fn (...$a) => $default->exists(...$a));
    Storage::shouldReceive('makeDirectory')->andReturnUsing(fn (...$a) => $default->makeDirectory(...$a));
    Storage::shouldReceive('put')->andReturn(false);
}

test('failed signature write does not finalize acceptance', function () {
    // Require signatures so the signature write path fires.
    $this->settings->enableRequiredSignatures();

    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    mockStorageToFailPut();

    $response = $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), acceptPayloadWithSignature());

    $response->assertSessionHas('error');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull('accepted_at must not populate when the signature write failed');
    expect($acceptance->signature_filename)->toBeNull('signature_filename must not populate when the signature write failed');
});

test('failed pdf write does not finalize acceptance', function () {
    // Signature not required; only the PDF write path fires.
    $target = User::factory()->create();
    $acceptance = CheckoutAcceptance::factory()->pending()->for($target, 'assignedTo')->create();

    mockStorageToFailPut();

    $response = $this->actingAs($target)
        ->post(route('account.store-acceptance', $acceptance), [
            'asset_acceptance' => 'accepted',
        ]);

    $response->assertSessionHas('error');

    $acceptance->refresh();
    expect($acceptance->accepted_at)->toBeNull('accepted_at must not populate when the PDF write failed');
});
