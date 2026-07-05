<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ReportTemplateSelect;
use App\Models\ReportTemplate;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTemplateSelectTest extends TestCase
{
    public function test_the_component_can_render()
    {
        Livewire::test(ReportTemplateSelect::class)->assertStatus(200);
    }

    public function test_scopes_to_user()
    {
        User::factory()
            ->has(ReportTemplate::factory(['type' => 'asset', 'name' => 'Another User: Asset']))
            ->has(ReportTemplate::factory(['type' => 'component', 'name' => 'Another User: Component']))
            ->create();

        $actor = User::factory()->canViewReports()
            ->has(ReportTemplate::factory(['type' => 'asset', 'name' => 'User: Asset']))
            ->has(ReportTemplate::factory(['type' => 'component', 'name' => 'User: Component']))
            ->create();

        Livewire::actingAs($actor)->test(ReportTemplateSelect::class)
            ->assertSet('templates', fn ($templates) => count($templates) === 2)
            ->assertDontSee('Another User');
    }

    public function test_scopes_to_provided_type()
    {
        $actor = User::factory()->canViewReports()
            ->has(ReportTemplate::factory(['type' => 'asset', 'name' => 'User Saved Asset Template']))
            ->has(ReportTemplate::factory(['type' => 'component', 'name' => 'User Saved Component Template']))
            ->create();

        Livewire::actingAs($actor)->test(ReportTemplateSelect::class, ['type' => 'asset'])
            ->assertSet('templates', fn ($templates) => count($templates) === 1)
            ->assertSee('User Saved Asset Template')
            ->assertDontSee('User Saved Component Template');
    }

    public function test_includes_shared()
    {
        $template = ReportTemplate::factory()->shared()->create(['type' => 'asset', 'name' => 'Shared Asset Template']);

        Livewire::actingAs(User::factory()->canViewReports()->create())
            ->test(ReportTemplateSelect::class, ['type' => 'asset'])
            ->assertSet('templates', fn ($templates) => $templates->contains($template));
    }
}
