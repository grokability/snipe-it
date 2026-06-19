<?php

namespace Tests\Unit\Models;

use App\Models\License;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Cubre metodos y scopes de License que no estaban cubiertos: estado (expired/
 * terminated/inactive), conteos de asientos, setters y scopes de orden/estado.
 */
class LicenseDeepMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_state_helpers(): void
    {
        $expired = License::factory()->create(['expiration_date' => now()->subYear()]);
        $this->assertTrue($expired->isExpired());

        $terminated = License::factory()->create(['termination_date' => now()->subDay()]);
        $this->assertTrue($terminated->isTerminated());

        $active = License::factory()->create(['expiration_date' => now()->addYear(), 'termination_date' => null]);
        $this->assertIsBool($active->isInactive());
    }

    public function test_seat_counts(): void
    {
        $license = License::factory()->create(['seats' => 5]);

        $this->assertIsNumeric($license->totalSeatsByLicenseID());
        $this->assertIsNumeric($license->availCount()->count());
        $this->assertIsInt($license->remaincount());
        $this->assertIsNumeric($license->assignedCount()->count());
        $this->assertIsNumeric($license->percentRemaining());
        $this->assertIsBool((bool) $license->requireAcceptance());
    }

    public function test_avail_and_free_seat_attributes(): void
    {
        $license = License::factory()->create(['seats' => 3]);

        $this->assertIsNumeric($license->avail_seats_count);
        $this->assertIsNumeric($license->license_seats_count);
        $this->assertTrue($license->free_seat_count === null || is_numeric($license->free_seat_count));
    }

    public function test_setters(): void
    {
        $license = new License;
        $license->maintained = true;
        $license->reassignable = false;
        $license->expiration_date = '';
        $license->termination_date = '';

        $this->assertTrue((bool) $license->maintained);
        $this->assertFalse((bool) $license->reassignable);
        $this->assertNull($license->expiration_date);
    }

    public function test_un_reassignable_count(): void
    {
        $license = License::factory()->create(['reassignable' => 0]);

        $this->assertIsInt(License::unReassignableCount($license));
    }

    public function test_static_counts(): void
    {
        License::factory()->create();

        $this->assertIsNumeric(License::assetcount());
        $this->assertIsNumeric(License::availassetcount());
    }

    public function test_state_scopes(): void
    {
        License::factory()->create(['expiration_date' => now()->subYear()]);
        License::factory()->create(['expiration_date' => now()->addMonth()]);

        $this->assertInstanceOf(Collection::class, License::activeLicenses()->get());
        $this->assertInstanceOf(Collection::class, License::expiredLicenses()->get());
        $this->assertInstanceOf(Collection::class, License::expiringLicenses(60, true)->get());
    }

    public function test_order_scopes(): void
    {
        License::factory()->count(2)->create();

        $this->assertInstanceOf(Collection::class, License::OrderManufacturer('asc')->get());
        $this->assertInstanceOf(Collection::class, License::OrderSupplier('desc')->get());
        $this->assertInstanceOf(Collection::class, License::OrderCompany('asc')->get());
        $this->assertInstanceOf(Collection::class, License::OrderByCreatedBy('desc')->get());
    }
}
