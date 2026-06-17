<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class UserScopesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->count(3)->create();
        User::factory()->superuser()->create();
    }

    public function test_get_not_deleted_scope(): void
    {
        $this->assertInstanceOf(Collection::class, User::getNotDeleted()->get());
    }

    public function test_match_email_or_username_scope(): void
    {
        $this->assertInstanceOf(Collection::class, User::matchEmailOrUsername('jane', 'jane@example.com')->get());
    }

    public function test_simple_name_search_scope(): void
    {
        $this->assertInstanceOf(Collection::class, User::simpleNameSearch('jane')->get());
    }

    public function test_only_super_admins_scope(): void
    {
        $this->assertInstanceOf(Collection::class, User::onlySuperAdmins()->get());
    }

    public function test_only_admins_and_super_admins_scope(): void
    {
        $this->assertInstanceOf(Collection::class, User::onlyAdminsAndSuperAdmins()->get());
    }

    public function test_order_scopes(): void
    {
        $this->assertInstanceOf(Collection::class, User::OrderManager('asc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderLocation('desc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderDepartment('asc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderCompany('asc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderByCreatedBy('desc')->get());
    }
}
