<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre los metodos de Loggable que generan Actionlog y no estaban cubiertos:
 * logAudit, logCreate, logUpload, logForceCheckin y getLatestSignedAcceptance.
 */
class LoggableMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_log_create(): void
    {
        $asset = Asset::factory()->create();

        $log = $asset->logCreate('creado en test');

        $this->assertInstanceOf(Actionlog::class, $log);
        $this->assertSame('create', $log->action_type);
    }

    public function test_log_upload(): void
    {
        $asset = Asset::factory()->create();

        $log = $asset->logUpload('archivo.pdf', 'subida de prueba');

        $this->assertSame('archivo.pdf', $log->filename);
        $this->assertSame('uploaded', $log->action_type);
    }

    public function test_log_force_checkin(): void
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $log = $asset->logForceCheckin('forzado');

        $this->assertInstanceOf(Actionlog::class, $log);
        $this->assertSame('force checkin', $log->action_type);
    }

    public function test_log_audit_with_changes(): void
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create(['name' => 'Nuevo Nombre']);

        // originalValues distinto al actual -> rama de cambios (log_meta).
        $log = $asset->logAudit('auditoria', $location->id, null, [
            'name' => 'Nombre Viejo',
            'updated_at' => '2020-01-01',
        ]);

        $this->assertInstanceOf(Actionlog::class, $log);
        $this->assertSame('audit', $log->action_type);
        $this->assertNotNull($log->log_meta);
    }

    public function test_log_audit_without_changes(): void
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create();

        // Sin originalValues -> rama sin log_meta.
        $log = $asset->logAudit('auditoria', $location->id);

        $this->assertSame('audit', $log->action_type);
    }

    public function test_get_latest_signed_acceptance_returns_null_when_none(): void
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        $this->assertNull($asset->getLatestSignedAcceptance($user));
    }
}
