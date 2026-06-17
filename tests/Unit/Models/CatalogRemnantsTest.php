<?php

namespace Tests\Unit\Models;

use App\Models\CustomField;
use App\Models\Department;
use App\Models\Group;
use App\Models\Supplier;
use App\Models\User;
use App\Presenters\CustomFieldPresenter;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class CatalogRemnantsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_custom_field_visibility_icons_all_flags(): void
    {
        $field = CustomField::factory()->create([
            'display_checkout' => 1,
            'display_checkin' => 1,
            'display_audit' => 1,
            'display_in_user_view' => 1,
            'show_in_listview' => 1,
            'show_in_email' => 1,
            'show_in_requestable_list' => 1,
        ]);

        $this->assertCount(7, CustomFieldPresenter::visibilityIconsArray($field));
        $this->assertStringContainsString('fa-', CustomFieldPresenter::visibilityIcons($field));
    }

    public function test_custom_field_visibility_icons_no_flags(): void
    {
        $field = CustomField::factory()->create([
            'display_checkout' => 0, 'display_checkin' => 0, 'display_audit' => 0,
            'display_in_user_view' => 0, 'show_in_listview' => 0, 'show_in_email' => 0,
            'show_in_requestable_list' => 0,
        ]);

        $this->assertCount(0, CustomFieldPresenter::visibilityIconsArray($field));
    }

    public function test_group_helpers(): void
    {
        $group = Group::factory()->create();

        $this->assertIsBool($group->isDeletable());
        $this->assertNotNull($group->decodePermissions());
        $this->assertInstanceOf(Collection::class, Group::OrderByCreatedBy('asc')->get());
    }

    public function test_department_helpers(): void
    {
        $department = Department::factory()->create();

        $this->assertIsBool($department->isDeletable());
        $this->assertInstanceOf(Collection::class, Department::OrderLocation('asc')->get());
        $this->assertInstanceOf(Collection::class, Department::OrderManager('asc')->get());
        $this->assertInstanceOf(Collection::class, Department::OrderCompany('asc')->get());
    }

    public function test_supplier_helpers(): void
    {
        $supplier = Supplier::factory()->create();

        $this->assertIsBool($supplier->isDeletable());
        $this->assertNotNull($supplier->adminuser());
    }
}
