<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class RestoreComponentTest extends TestCase
{
    public function test_single_restore_requires_delete_permission(): void
    {
        $component = Component::factory()->create();
        $component->delete();

        $this->actingAs(User::factory()->viewComponents()->create())
            ->post(route('components.restore', ['componentID' => $component->id]))
            ->assertForbidden();

        $this->assertNotNull($component->fresh()->deleted_at);
    }

    public function test_single_restore_brings_component_back(): void
    {
        $component = Component::factory()->create();
        $component->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.restore', ['componentID' => $component->id]))
            ->assertRedirect(route('components.show', $component));

        $this->assertNull($component->fresh()->deleted_at);
    }

    public function test_single_restore_rejects_already_live_component(): void
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.restore', ['componentID' => $component->id]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($component->fresh()->deleted_at);
    }

    public function test_single_restore_rejects_unknown_id(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.restore', ['componentID' => 999999]))
            ->assertRedirect(route('components.index'))
            ->assertSessionHas('error');
    }

    public function test_single_restore_blocks_cross_company_under_fmcs(): void
    {
        // postRestore now authorizes against the instance, so the
        // policy's before() hook runs the FMCS cross-tenant check.
        // Pin against the pre-fix behavior where class-level authorize
        // skipped that guard.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->deleteComponents()->viewComponents()->make());
        $foreign = Component::factory()->for($companyB)->create();
        $foreign->delete();

        $this->actingAs($actor)
            ->post(route('components.restore', ['componentID' => $foreign->id]))
            ->assertRedirect();

        $this->assertNotNull($foreign->fresh()->deleted_at);
    }

    public function test_bulk_restore_requires_delete_permission(): void
    {
        $component = Component::factory()->create();
        $component->delete();

        $this->actingAs(User::factory()->viewComponents()->create())
            ->post(route('components.bulkedit.show'), [
                'ids' => [$component->id],
                'bulk_actions' => 'restore',
            ])
            ->assertForbidden();

        $this->assertNotNull($component->fresh()->deleted_at);
    }

    public function test_bulk_restore_brings_selected_components_back(): void
    {
        $one = Component::factory()->create();
        $two = Component::factory()->create();
        $one->delete();
        $two->delete();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.show'), [
                'ids' => [$one->id, $two->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect(route('components.index', ['status' => 'Deleted']))
            ->assertSessionHas('success');

        $this->assertNull($one->fresh()->deleted_at);
        $this->assertNull($two->fresh()->deleted_at);
    }

    public function test_bulk_restore_skips_live_rows_without_error(): void
    {
        // Stale selection: one row is already live. The restore
        // dispatcher silently skips live rows so the deleted ones
        // still succeed. Pin so a future refactor can't tighten this
        // into a hard failure that blocks the batch.
        $deleted = Component::factory()->create();
        $deleted->delete();

        $live = Component::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.show'), [
                'ids' => [$deleted->id, $live->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect(route('components.index', ['status' => 'Deleted']))
            ->assertSessionHas('success');

        $this->assertNull($deleted->fresh()->deleted_at);
        $this->assertNull($live->fresh()->deleted_at);
    }

    public function test_bulk_restore_blocks_cross_company_under_fmcs(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $actor = $companyA->users()->save(User::factory()->editComponents()->deleteComponents()->viewComponents()->make());
        $foreign = Component::factory()->for($companyB)->create();
        $foreign->delete();

        $this->actingAs($actor)
            ->post(route('components.bulkedit.show'), [
                'ids' => [$foreign->id],
                'bulk_actions' => 'restore',
            ])
            ->assertRedirect();

        $this->assertNotNull($foreign->fresh()->deleted_at);
    }
}
