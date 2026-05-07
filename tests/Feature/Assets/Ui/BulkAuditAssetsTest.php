<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auditing')]
class BulkAuditAssetsTest extends TestCase
{
    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->auditAssets()->create();
    }

    public function test_permission_required_to_view_page()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('assets.bulkaudit'))
            ->assertStatus(403);
    }

    public function test_can_view_audit_page()
    {
        $this->actingAs($this->actor)
            ->get(route('assets.bulkaudit'))
            ->assertViewIs('hardware.quickscan');
    }

    public function test_bulk_audit_page_is_given_todays_date_when_audit_interval_is_null()
    {
        $this->settings->setAuditInterval(null);

        $this->actingAs($this->actor)
            ->get(route('assets.bulkaudit'))
            ->assertViewIs('hardware.quickscan')
            ->assertViewHas('next_audit_date', Carbon::now()->toDateString());
    }

    public function test_bulk_audit_page_is_given_correct_date_when_audit_interval_is_set()
    {
        $this->settings->setAuditInterval(5);

        $this->actingAs($this->actor)
            ->get(route('assets.bulkaudit'))
            ->assertViewIs('hardware.quickscan')
            ->assertViewHas('next_audit_date', Carbon::now()->addMonths(5)->toDateString());
    }
}
