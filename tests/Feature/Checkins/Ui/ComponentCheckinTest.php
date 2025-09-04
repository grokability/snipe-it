<?php

namespace Tests\Feature\Checkins\Ui;

use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComponentCheckinTest extends TestCase
{

    public function testCheckingInComponentRequiresCorrectPermission()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $componentAsset = DB::table('components_assets')->where('component_id', $component->id)->first();

        $this->actingAs(User::factory()->create())
            ->post(route('components.checkin.store', $componentAsset->id))
            ->assertForbidden();
    }

    public function testPageRenders()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $componentAsset = DB::table('components_assets')->where('component_id', $component->id)->first();

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('components.checkin.show', $componentAsset->id))
            ->assertOk();
    }

    public function testComponentCheckinPagePostIsRedirectedIfRedirectSelectionIsIndex()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $componentAsset = DB::table('components_assets')->where('component_id', $component->id)->first();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('components.index'))
            ->post(route('components.checkin.store', [
                'componentID' => $componentAsset->id,
            ]), [
                'redirect_option' => 'index',
                'checkin_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertRedirect(route('components.index'));
        $this->assertHasTheseActionLogs($component, ['create', 'checkin from']);
    }

    public function testComponentCheckinPagePostIsRedirectedIfRedirectSelectionIsItem()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $componentAsset = DB::table('components_assets')->where('component_id', $component->id)->first();

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('components.index'))
            ->post(route('components.checkin.store', [
                'componentID' => $componentAsset->id,
            ]), [
                'redirect_option' => 'item',
                'checkin_qty' => 1,
            ])
            ->assertStatus(302)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('components.show', $component));
        $this->assertHasTheseActionLogs($component, ['create', 'checkin from']);

    }
}
