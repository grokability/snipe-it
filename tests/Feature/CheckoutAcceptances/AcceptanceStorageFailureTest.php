<?php

namespace Tests\Feature\CheckoutAcceptances;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

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
class AcceptanceStorageFailureTest extends TestCase
{
    private function pendingAcceptance(User $target): CheckoutAcceptance
    {
        $asset = Asset::factory()->assignedToUser($target)->create();

        return CheckoutAcceptance::factory()->pending()->for($asset, 'checkoutable')->create([
            'assigned_to_id' => $target->id,
        ]);
    }

    private function acceptPayloadWithSignature(): array
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
            'signature_output' => 'data:image/png;base64,' . base64_encode($signaturePng),
        ];
    }

    private function mockStorageToFailPut(): void
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

    public function test_failed_signature_write_does_not_finalize_acceptance(): void
    {
        // Require signatures so the signature write path fires.
        $settings = Setting::query()->firstOrFail();
        $settings->require_accept_signature = 1;
        $settings->save();
        Setting::$_cache = null;

        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        $this->mockStorageToFailPut();

        $response = $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), $this->acceptPayloadWithSignature());

        $response->assertSessionHas('error');

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at, 'accepted_at must not populate when the signature write failed');
        $this->assertNull($acceptance->signature_filename, 'signature_filename must not populate when the signature write failed');
    }

    public function test_failed_pdf_write_does_not_finalize_acceptance(): void
    {
        // Signature not required; only the PDF write path fires.
        $target = User::factory()->create();
        $acceptance = $this->pendingAcceptance($target);

        $this->mockStorageToFailPut();

        $response = $this->actingAs($target)
            ->post(route('account.store-acceptance', $acceptance), [
                'asset_acceptance' => 'accepted',
            ]);

        $response->assertSessionHas('error');

        $acceptance->refresh();
        $this->assertNull($acceptance->accepted_at, 'accepted_at must not populate when the PDF write failed');
    }
}
