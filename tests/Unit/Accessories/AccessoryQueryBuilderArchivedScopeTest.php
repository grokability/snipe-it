<?php

namespace Tests\Unit\Accessories;

use App\Models\Accessory;
use Tests\TestCase;

class AccessoryQueryBuilderArchivedScopeTest extends TestCase
{
    public function test_archived_scope_returns_only_archived(): void
    {
        $live = Accessory::factory()->create();
        $archived = Accessory::factory()->archived()->create();

        $ids = Accessory::query()->archived()->pluck('id')->all();

        $this->assertContains($archived->id, $ids);
        $this->assertNotContains($live->id, $ids);
    }

    public function test_not_archived_scope_excludes_archived(): void
    {
        $live = Accessory::factory()->create();
        $archived = Accessory::factory()->archived()->create();

        $ids = Accessory::query()->notArchived()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertNotContains($archived->id, $ids);
    }

    public function test_accessories_for_show_hides_archived_when_setting_off(): void
    {
        $this->settings->set(['show_archived_in_list' => 0]);

        $live = Accessory::factory()->create();
        $archived = Accessory::factory()->archived()->create();

        $ids = Accessory::query()->accessoriesForShow()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertNotContains($archived->id, $ids);
    }

    public function test_accessories_for_show_includes_archived_when_setting_on(): void
    {
        $this->settings->set(['show_archived_in_list' => 1]);

        $live = Accessory::factory()->create();
        $archived = Accessory::factory()->archived()->create();

        $ids = Accessory::query()->accessoriesForShow()->pluck('id')->all();

        $this->assertContains($live->id, $ids);
        $this->assertContains($archived->id, $ids);
    }

    public function test_requestable_scope_excludes_archived(): void
    {
        $requestable = Accessory::factory()->create(['requestable' => 1]);
        $archivedButRequestable = Accessory::factory()->archived()->create(['requestable' => 1]);

        $ids = Accessory::query()->requestable()->pluck('id')->all();

        $this->assertContains($requestable->id, $ids);
        $this->assertNotContains($archivedButRequestable->id, $ids);
    }
}
