<?php
namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\User;
use App\Models\Location;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AssetUnitTest extends TestCase
{

    public function test_zerofill()
    {
        $this->assertSame('007', Asset::zerofill(7, 3));
        $this->assertSame('42', Asset::zerofill(42, 2));

        // Zusätzliche Checks
        $this->assertSame('00042', Asset::zerofill(42, 5));
        $this->assertSame('5', Asset::zerofill(5, 1));
    }

    public function test_assigned_type_detection()
    {
        $asset = new Asset;
        $this->assertNull($asset->assignedType());

        $asset->assigned_type = User::class;
        $this->assertSame('user', $asset->assignedType());

        $asset->assigned_type = Location::class;
        $this->assertSame('location', $asset->assignedType());

        $asset->assigned_type = Asset::class;
        $this->assertSame('asset', $asset->assignedType());

        $asset->assigned_type = 'Some\\Nonexistent\\Class';
        $this->assertSame('class', $asset->assignedType());

    }

    public function test_checked_out_type_helpers()
    {
        $a = new Asset;

        $a->assigned_type = User::class;
        $this->assertTrue($a->checkedOutToUser());
        $this->assertFalse($a->checkedOutToLocation());
        $this->assertFalse($a->checkedOutToAsset());

        $a->assigned_type = Location::class;
        $this->assertTrue($a->checkedOutToLocation());
        $this->assertFalse($a->checkedOutToUser());
        $this->assertFalse($a->checkedOutToAsset());

        $a->assigned_type = Asset::class;
        $this->assertTrue($a->checkedOutToAsset());
        $this->assertFalse($a->checkedOutToUser());
        $this->assertFalse($a->checkedOutToLocation());
    }

    public function test_target_show_route_mapping()
    {
        $a = new Asset;

        $a->assigned_type = Asset::class;  
        $this->assertSame('hardware', $a->targetShowRoute());

        $a->assigned_type = User::class;    
        $this->assertSame('users', $a->targetShowRoute());

        $a->assigned_type = Location::class;
        $this->assertSame('locations', $a->targetShowRoute());

        $a->assigned_type = null;
        $this->assertNull($a->targetShowRoute());
    }


    public function test_warranty_expires_attribute()
    {
        $asset = new Asset;

        $asset->purchase_date   = '2024-01-15';
        $asset->warranty_months = 12;
        $expires = $asset->warranty_expires;
        $this->assertInstanceOf(Carbon::class, $expires);
        $this->assertSame('2025-01-15', $expires->format('Y-m-d'));

        $asset->warranty_months = 0;
        $this->assertSame('2024-01-15', $asset->warranty_expires->format('Y-m-d'));

        $asset->purchase_date = null;
        $this->assertNull($asset->warranty_expires);
    }

    public function test_date_and_bool_mutators()
    {
        $a = new Asset;

        $a->next_audit_date = '2025-08-29';
        $this->assertSame('2025-08-29', $a->next_audit_date);

        $a->last_audit_date = '2025-08-30 12:34:56';
        $this->assertSame('2025-08-30 12:34:56', $a->last_audit_date);

        $a->last_checkout = '2025-06-01 08:00:00';
        $a->last_checkin  = '2025-06-10 18:30:45';
        $this->assertSame('2025-06-01 08:00:00', $a->last_checkout);
        $this->assertSame('2025-06-10 18:30:45', $a->last_checkin);

        $a->asset_eol_date = '2030-12-31';
        $this->assertSame('2030-12-31', $a->asset_eol_date);

        $a->requestable = 'true';
        $this->assertSame(1, $a->requestable);
        $a->requestable = 'false';
        $this->assertSame(0, $a->requestable);
        $a->requestable = 0;
        $this->assertSame(0, $a->requestable);
        $a->requestable = 1;
        $this->assertSame(1, $a->requestable);
    }

    public function test_set_expected_checkin_empty_string_becomes_null()
    {
        $a = new Asset;
        $a->expected_checkin = '';
        $this->assertNull($a->expected_checkin);
    }

    public function test_check_invalid_next_audit_date_logic()
    {
        $a = new Asset;

        $a->last_audit_date = '2025-08-20 10:00:00';
        $a->next_audit_date = '2025-08-10';
        $this->assertTrue($a->checkInvalidNextAuditDate());

        $a->last_audit_date = '2025-08-10 10:00:00';
        $a->next_audit_date = '2025-08-20';
        $this->assertFalse($a->checkInvalidNextAuditDate());

        $a->last_audit_date = '2025-08-20 10:00:00';
        $a->next_audit_date = '2025-08-20';
        $this->assertFalse($a->checkInvalidNextAuditDate());

        $a->last_audit_date = null;
        $a->next_audit_date = '2025-08-20';
        $this->assertFalse($a->checkInvalidNextAuditDate());
    }
}
