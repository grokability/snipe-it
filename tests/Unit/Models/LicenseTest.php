<?php

namespace Tests\Unit\Models;

use App\Enums\ActionType;
use App\Models\Category;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;

    // TESTS PRE-EXISTENTES

    public function test_adding_seats_is_logged_when_updating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $license = License::factory()->create(['seats' => 2]);
        $license->update(['seats' => 6]);
        $this->assertDatabaseHas('action_logs', [
            'created_by'  => $user->id,
            'action_type' => ActionType::AddSeats,
            'item_type'   => License::class,
            'item_id'     => $license->id,
            'deleted_at'  => null,
            'quantity'    => 4,
            'note'        => 'added 4 seats',
        ]);
    }

    public function test_removing_seats_is_logged_when_updating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $license = License::factory()->create(['seats' => 6]);
        $license->update(['seats' => 3]);
        $this->assertDatabaseHas('action_logs', [
            'created_by'  => $user->id,
            'action_type' => ActionType::DeleteSeats,
            'item_type'   => License::class,
            'item_id'     => $license->id,
            'deleted_at'  => null,
            'quantity'    => 3,
            'note'        => 'deleted 3 seats',
        ]);
    }

    public function test_percent_remaining_returns_zero_when_seats_are_zero()
    {
        $license = new class extends License {
            public int $remaining = 8;
            public function remaincount(): int { return $this->remaining; }
        };
        $license->seats = 0;
        $this->assertEquals(0, $license->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_available_ratio()
    {
        $license = new class extends License {
            public int $remaining = 6;
            public function remaincount(): int { return $this->remaining; }
        };
        $license->seats = 12;
        $this->assertEquals(50.0, $license->percentRemaining());
    }

    public function test_percent_remaining_clamps_remaining_to_valid_bounds()
    {
        $license = new class extends License {
            public int $remaining = -3;
            public function remaincount(): int { return $this->remaining; }
        };
        $license->seats = 10;
        $this->assertEquals(0.0, $license->percentRemaining());
        $license->remaining = 99;
        $this->assertEquals(100.0, $license->percentRemaining());
    }

    public function test_depreciation_progress_percent_is_available_for_license(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));
        try {
            $license = new class extends License {
                public function depreciated_date() { return date_create('2027-01-01'); }
            };
            $license->purchase_date = '2025-01-01';
            $this->assertSame(50.0, $license->depreciationProgressPercent());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_depreciation_progress_percent_returns_zero_when_dates_are_missing(): void
    {
        $license = new class extends License {
            public function depreciated_date() { return null; }
        };
        $license->purchase_date = null;
        $this->assertSame(0.0, $license->depreciationProgressPercent());
    }

    // =========================================================================
    // VALIDACIÓN DE CAMPOS REQUERIDOS
    // =========================================================================

    /** @test */
    public function test_license_requires_name()
    {
        $license = License::factory()->make(['name' => null]);
        $this->assertFalse($license->isValid());
        $this->assertArrayHasKey('name', $license->getErrors()->toArray());
    }

    /** @test */
    public function test_license_requires_seats()
    {
        $license = License::factory()->make(['seats' => null]);
        $this->assertFalse($license->isValid());
        $this->assertArrayHasKey('seats', $license->getErrors()->toArray());
    }

    /** @test */
    public function test_license_requires_category_id()
    {
        $license = License::factory()->make(['category_id' => null]);
        $this->assertFalse($license->isValid());
        $this->assertArrayHasKey('category_id', $license->getErrors()->toArray());
    }

    /** @test */
    public function test_license_seats_must_be_at_least_one()
    {
        $license = License::factory()->make(['seats' => 0]);
        $this->assertFalse($license->isValid());
        $this->assertArrayHasKey('seats', $license->getErrors()->toArray());
    }

    /** @test */
    public function test_license_with_all_required_fields_is_valid()
    {
        $license = License::factory()->make();
        $this->assertTrue($license->isValid());
    }

    // =========================================================================
    // SEAT GENERATION — AL CREAR
    // =========================================================================

    /** @test */
    public function test_creating_license_generates_correct_number_of_seats()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 5]);

        $this->assertDatabaseCount('license_seats', 5);
        $this->assertEquals(5, LicenseSeat::where('license_id', $license->id)->count());
    }

    /** @test */
    public function test_all_generated_seats_start_unassigned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 3]);

        $unassigned = LicenseSeat::where('license_id', $license->id)
            ->whereNull('assigned_to')
            ->whereNull('asset_id')
            ->count();

        $this->assertEquals(3, $unassigned);
    }

    // =========================================================================
    // SEAT ADJUSTMENT — AL ACTUALIZAR
    // =========================================================================

    /** @test */
    public function test_increasing_seats_creates_new_seat_records()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 3]);
        $this->assertEquals(3, LicenseSeat::where('license_id', $license->id)->count());

        $license->update(['seats' => 6]);
        $this->assertEquals(6, LicenseSeat::where('license_id', $license->id)->count());
    }

    /** @test */
    public function test_decreasing_seats_removes_unassigned_seat_records()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 5]);
        $this->assertEquals(5, LicenseSeat::where('license_id', $license->id)->count());

        $license->update(['seats' => 3]);
        $this->assertEquals(3, LicenseSeat::where('license_id', $license->id)->count());
    }

    /** @test */
    public function test_cannot_decrease_seats_below_assigned_count()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 3]);

        // Asignar todos los seats
        LicenseSeat::where('license_id', $license->id)->update(['assigned_to' => $user->id]);

        // Intentar reducir por debajo de asignados — debe fallar (retorna false)
        $result = License::adjustSeatCount($license, 3, 1);
        $this->assertFalse($result);

        // Los seats no deben haber cambiado
        $this->assertEquals(3, LicenseSeat::where('license_id', $license->id)->count());
    }

    // =========================================================================
    // REMAIN COUNT
    // =========================================================================

    /** @test */
    public function test_remaincount_returns_correct_available_seats()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()
            ->withSeats(5)
            ->create();

        // Asignar 2 seats
        LicenseSeat::where('license_id', $license->id)
            ->limit(2)
            ->update(['assigned_to' => $user->id]);

        // Refrescar contadores
        $license->load('licenseSeatsRelation', 'assignedCount');

        $this->assertEquals(3, $license->remaincount());
    }

    /** @test */
    public function test_remaincount_returns_zero_when_all_seats_assigned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 2]);

        LicenseSeat::where('license_id', $license->id)
            ->update(['assigned_to' => $user->id]);

        $license->load('licenseSeatsRelation', 'assignedCount');

        $this->assertEquals(0, $license->remaincount());
    }

    // =========================================================================
    // EXPIRACIÓN
    // =========================================================================

    /** @test */
    public function test_is_expired_returns_true_when_expiration_date_is_in_the_past()
    {
        $license = new License();
        $license->expiration_date = Carbon::yesterday()->toDateString();

        $this->assertTrue($license->isExpired());
    }

    /** @test */
    public function test_is_expired_returns_true_when_expiration_date_is_today()
    {
        // "startOfDay <= day" — hoy también cuenta como expirado
        $license = new License();
        $license->expiration_date = Carbon::today()->toDateString();

        $this->assertTrue($license->isExpired());
    }

    /** @test */
    public function test_is_expired_returns_false_when_expiration_date_is_in_the_future()
    {
        $license = new License();
        $license->expiration_date = Carbon::tomorrow()->toDateString();

        $this->assertFalse($license->isExpired());
    }

    /** @test */
    public function test_is_expired_returns_false_when_no_expiration_date_is_set()
    {
        $license = new License();
        $license->expiration_date = null;

        $this->assertFalse($license->isExpired());
    }

    // =========================================================================
    // TERMINACIÓN
    // =========================================================================

    /** @test */
    public function test_is_terminated_returns_true_when_termination_date_is_in_the_past()
    {
        $license = new License();
        $license->termination_date = Carbon::yesterday()->toDateString();

        $this->assertTrue($license->isTerminated());
    }

    /** @test */
    public function test_is_terminated_returns_true_when_termination_date_is_today()
    {
        $license = new License();
        $license->termination_date = Carbon::today()->toDateString();

        $this->assertTrue($license->isTerminated());
    }

    /** @test */
    public function test_is_terminated_returns_false_when_termination_date_is_in_the_future()
    {
        $license = new License();
        $license->termination_date = Carbon::tomorrow()->toDateString();

        $this->assertFalse($license->isTerminated());
    }

    /** @test */
    public function test_is_terminated_returns_false_when_no_termination_date_is_set()
    {
        $license = new License();
        $license->termination_date = null;

        $this->assertFalse($license->isTerminated());
    }

    // =========================================================================
    // ESTADO INACTIVO
    // =========================================================================

    /** @test */
    public function test_is_inactive_returns_true_when_license_is_expired()
    {
        $license = new License();
        $license->expiration_date  = Carbon::yesterday()->toDateString();
        $license->termination_date = null;

        $this->assertTrue($license->isInactive());
    }

    /** @test */
    public function test_is_inactive_returns_true_when_license_is_terminated()
    {
        $license = new License();
        $license->expiration_date  = null;
        $license->termination_date = Carbon::yesterday()->toDateString();

        $this->assertTrue($license->isInactive());
    }

    /** @test */
    public function test_is_inactive_returns_true_when_both_expired_and_terminated()
    {
        $license = new License();
        $license->expiration_date  = Carbon::yesterday()->toDateString();
        $license->termination_date = Carbon::yesterday()->toDateString();

        $this->assertTrue($license->isInactive());
    }

    /** @test */
    public function test_is_inactive_returns_false_when_neither_expired_nor_terminated()
    {
        $license = new License();
        $license->expiration_date  = Carbon::tomorrow()->toDateString();
        $license->termination_date = Carbon::tomorrow()->toDateString();

        $this->assertFalse($license->isInactive());
    }

    /** @test */
    public function test_is_inactive_returns_false_when_no_dates_set()
    {
        $license = new License();
        $license->expiration_date  = null;
        $license->termination_date = null;

        $this->assertFalse($license->isInactive());
    }

    // =========================================================================
    // MUTADORES DE FECHA (casting)
    // =========================================================================

    /** @test */
    public function test_set_expiration_date_attribute_stores_null_for_empty_string()
    {
        $license = new License();
        $license->expiration_date = '';

        $this->assertNull($license->getAttributes()['expiration_date']);
    }

    /** @test */
    public function test_set_expiration_date_attribute_stores_null_for_zero_date()
    {
        $license = new License();
        $license->expiration_date = '0000-00-00';

        $this->assertNull($license->getAttributes()['expiration_date']);
    }

    /** @test */
    public function test_set_expiration_date_attribute_stores_valid_date()
    {
        $license = new License();
        $license->expiration_date = '2027-12-31';

        $this->assertEquals('2027-12-31', $license->getAttributes()['expiration_date']);
    }

    /** @test */
    public function test_set_termination_date_attribute_stores_null_for_empty_string()
    {
        $license = new License();
        $license->termination_date = '';

        $this->assertNull($license->getAttributes()['termination_date']);
    }

    /** @test */
    public function test_set_termination_date_attribute_stores_valid_date()
    {
        $license = new License();
        $license->termination_date = '2027-06-30';

        $this->assertEquals('2027-06-30', $license->getAttributes()['termination_date']);
    }

    /** @test */
    public function test_purchase_date_is_cast_to_date_instance()
    {
        $license = new License(['purchase_date' => '2024-01-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $license->purchase_date);
    }

    // =========================================================================
    // FREE SEAT
    // =========================================================================

    /** @test */
    public function test_free_seat_returns_first_unassigned_non_unreassignable_seat()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 3]);

        $seat = $license->freeSeat();

        $this->assertNotNull($seat);
        $this->assertNull($seat->assigned_to);
        $this->assertNull($seat->asset_id);
        $this->assertFalse((bool) $seat->unreassignable_seat);
    }

    /** @test */
    public function test_free_seat_returns_null_when_all_seats_are_assigned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 2]);

        LicenseSeat::where('license_id', $license->id)
            ->update(['assigned_to' => $user->id]);

        $this->assertNull($license->freeSeat());
    }

    /** @test */
    public function test_free_seat_skips_unreassignable_seats()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 2]);

        // Marcar todos como no reasignables
        LicenseSeat::where('license_id', $license->id)
            ->update(['unreassignable_seat' => true]);

        $this->assertNull($license->freeSeat());
    }

    // =========================================================================
    // IS DELETABLE
    // =========================================================================

    /** @test */
    public function test_is_deletable_returns_false_when_seats_are_assigned()
    {
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 2]);

        LicenseSeat::where('license_id', $license->id)
            ->limit(1)
            ->update(['assigned_to' => $user->id]);

        $this->assertFalse($license->isDeletable());
    }

    /** @test */
    public function test_is_deletable_returns_true_when_all_seats_are_free()
    {
        $user = User::factory()->superuser()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 2]);

        // isDeletable() compara free_seats_count (atributo de withCount) con seats,
        // por lo que hay que cargar el conteo antes de evaluar.
        $license->loadCount('freeSeats');

        // Todos los seats sin asignar (estado inicial)
        $this->assertTrue($license->isDeletable());
    }

    // =========================================================================
    // RELACIONES
    // =========================================================================

    /** @test */
    public function test_license_has_many_license_seats()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 3]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Collection::class,
            $license->licenseseats
        );
        $this->assertEquals(3, $license->licenseseats->count());
    }

    /** @test */
    public function test_license_belongs_to_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create();

        $this->assertInstanceOf(Category::class, $license->category);
    }

    /** @test */
    public function test_license_free_seats_relation_returns_only_unassigned()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create(['seats' => 3]);

        // Asignar 1 seat
        LicenseSeat::where('license_id', $license->id)
            ->limit(1)
            ->update(['assigned_to' => $user->id]);

        $this->assertEquals(2, $license->freeSeats()->count());
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /** @test */
    public function test_scope_active_licenses_excludes_expired_licenses()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $active  = License::factory()->create(['expiration_date' => Carbon::tomorrow()]);
        $expired = License::factory()->create(['expiration_date' => Carbon::yesterday()]);

        $results = License::activeLicenses()->pluck('id');

        $this->assertContains($active->id, $results);
        $this->assertNotContains($expired->id, $results);
    }

    /** @test */
    public function test_scope_active_licenses_excludes_terminated_licenses()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $active     = License::factory()->create(['termination_date' => Carbon::tomorrow()]);
        $terminated = License::factory()->create(['termination_date' => Carbon::yesterday()]);

        $results = License::activeLicenses()->pluck('id');

        $this->assertContains($active->id, $results);
        $this->assertNotContains($terminated->id, $results);
    }

    /** @test */
    public function test_scope_active_licenses_includes_licenses_without_dates()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $license = License::factory()->create([
            'expiration_date'  => null,
            'termination_date' => null,
        ]);

        $results = License::activeLicenses()->pluck('id');

        $this->assertContains($license->id, $results);
    }

    /** @test */
    public function test_scope_expired_licenses_returns_expired_license()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $expired = License::factory()->create(['expiration_date' => Carbon::yesterday()]);
        $active  = License::factory()->create(['expiration_date' => Carbon::tomorrow()]);

        $results = License::expiredLicenses()->pluck('id');

        $this->assertContains($expired->id, $results);
        $this->assertNotContains($active->id, $results);
    }

    /** @test */
    public function test_scope_expiring_licenses_returns_licenses_expiring_within_days()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $expiringSoon = License::factory()->create([
            'expiration_date'  => Carbon::now()->addDays(30),
            'termination_date' => null,
        ]);
        $expiringLate = License::factory()->create([
            'expiration_date'  => Carbon::now()->addDays(90),
            'termination_date' => null,
        ]);

        $results = License::expiringLicenses(60)->pluck('id');

        $this->assertContains($expiringSoon->id, $results);
        $this->assertNotContains($expiringLate->id, $results);
    }
}
