<?php

namespace Tests\Feature\Users;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class TransferUserItemsPageAccessTest extends TestCase
{
    public function test_transfer_page_requires_authentication(): void
    {
        User::factory()->create();
        $source = User::factory()->create();

        $this->get(route('users.transfer.show', $source))
            ->assertRedirect(route('login'));
    }

    public function test_transfer_page_requires_checkout_permission(): void
    {
        $source = User::factory()->create();

        $this->actingAs(User::factory()->viewUsers()->create())
            ->get(route('users.transfer.show', $source))
            ->assertForbidden();
    }

    public function test_transfer_page_renders_when_source_has_items(): void
    {
        $source = User::factory()->create();
        Asset::factory()->create(['assigned_to' => $source->id, 'assigned_type' => User::class]);

        $this->actingAs($this->transferActor())
            ->get(route('users.transfer.show', $source))
            ->assertOk()
            ->assertViewIs('users.transfer');
    }

    public function test_transfer_page_redirects_when_source_has_no_items(): void
    {
        $source = User::factory()->create();

        $this->actingAs($this->transferActor())
            ->get(route('users.transfer.show', $source))
            ->assertRedirect(route('users.show', $source));
    }

    private function transferActor(): User
    {
        return User::factory()
            ->viewUsers()
            ->checkinAssets()
            ->checkoutAssets()
            ->create();
    }
}
