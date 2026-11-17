<?php

namespace Tests\Unit\Components;

use App\Models\Component;
use Tests\TestCase;

class ComponentQueryBuilderArchivedScopeTest extends TestCase
{
    public function test_archived_scope_returns_only_archived(): void
    {
        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $ids = Component::query()->archived()->pluck('id')->all();

        $this->assertContains($archived->id, $ids);
        $this->assertNotContains($live->id, $ids);
    }

    public function test_not_archived_scope_excludes_archived(): void
    {
        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $ids = Component::query()->notArchived()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertNotContains($archived->id, $ids);
    }

    public function test_components_for_show_hides_archived_when_setting_off(): void
    {
        $this->settings->set(['show_archived_in_list' => 0]);

        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $ids = Component::query()->componentsForShow()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertNotContains($archived->id, $ids);
    }

    public function test_components_for_show_includes_archived_when_setting_on(): void
    {
        $this->settings->set(['show_archived_in_list' => 1]);

        $live = Component::factory()->create();
        $archived = Component::factory()->archived()->create();

        $ids = Component::query()->componentsForShow()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertContains($archived->id, $ids);
    }

    public function test_requestable_scope_excludes_archived(): void
    {
        $requestable = Component::factory()->create(['requestable' => 1]);
        $archivedButRequestable = Component::factory()->archived()->create(['requestable' => 1]);

        $ids = Component::query()->requestable()->pluck('id')->all();

        $this->assertContains($requestable->id, $ids);
        $this->assertNotContains($archivedButRequestable->id, $ids);
    }
}
