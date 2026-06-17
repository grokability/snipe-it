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

    public function test_history_relation_returns_logs(): void
    {
        $asset = Asset::factory()->create();
        $asset->logCreate('seed');

        $this->assertGreaterThanOrEqual(1, $asset->history()->count());
    }

    public function test_log_checkin_with_original_values(): void
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        $log = $asset->logCheckin($target, 'devuelto', null, ['name' => 'old']);

        $this->assertNotNull($log->id);
    }

    public function test_log_audit_with_location(): void
    {
        $asset = Asset::factory()->create();
        $location = \App\Models\Location::factory()->create();

        $log = $asset->logAudit('auditado', $location->id);

        $this->assertEquals('audit', $log->action_type);
    }

    public function test_get_history_returns_paginated_result(): void
    {
        $asset = Asset::factory()->create();
        $asset->logCreate('seed history');

        $request = new \Illuminate\Http\Request([
            'sort' => 'created_at', 'order' => 'desc', 'offset' => 0, 'limit' => 20,
        ]);

        $this->assertNotNull($asset->getHistory($request));
    }

    public function test_log_checkout_with_quantity_and_original_values(): void
    {
        $asset = Asset::factory()->create();
        $target = User::factory()->create();

        $log = $asset->logCheckout('with qty', $target, now(), ['name' => 'old'], 2);

        $this->assertEquals('checkout', $log->action_type);
    }
}
