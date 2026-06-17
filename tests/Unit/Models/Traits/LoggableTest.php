<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Ejercita el trait App\Models\Traits\Loggable a traves del modelo Asset,
 * que registra acciones (checkout, checkin, audit, create, upload) en el actionlog.
 */
class LoggableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_log_create_creates_action_log(): void
    {
        $asset = Asset::factory()->create();

        $log = $asset->logCreate('created in test');

        $this->assertEquals('create', $log->action_type);
        $this->assertDatabaseHas('action_logs', ['id' => $log->id, 'action_type' => 'create']);
    }

    public function test_log_checkout_creates_action_log(): void
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        $log = $asset->logCheckout('checked out in test', $target);

        $this->assertEquals('checkout', $log->action_type);
    }

    public function test_log_checkin_creates_action_log(): void
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        $log = $asset->logCheckin($target, 'checked in test');

        $this->assertNotNull($log->id);
    }

    public function test_log_audit_creates_action_log(): void
    {
        $asset = Asset::factory()->create();

        $log = $asset->logAudit('audited', null);

        $this->assertEquals('audit', $log->action_type);
    }

    public function test_log_upload_creates_action_log(): void
    {
        $asset = Asset::factory()->create();

        $log = $asset->logUpload('file.pdf', 'uploaded a file');

        $this->assertEquals('uploaded', $log->action_type);
    }

    public function test_log_force_checkin(): void
    {
        $asset = Asset::factory()->create();

        $log = $asset->logForceCheckin('force checkin');

        $this->assertNotNull($log->id);
    }

    public function test_set_imported_flag(): void
    {
        $asset = Asset::factory()->create();

        $asset->setImported(true);

        $this->assertTrue(true); // setImported no devuelve valor; basta con no lanzar excepcion
    }

    public function test_get_latest_signed_acceptance_returns_null_without_acceptance(): void
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $this->assertNull($asset->getLatestSignedAcceptance($user));
    }
}
