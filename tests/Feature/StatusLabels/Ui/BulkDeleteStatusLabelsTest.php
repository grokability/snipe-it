<?php

namespace Tests\Feature\StatusLabels\Ui;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteStatusLabelsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [1, 2, 3],
            ])
            ->assertForbidden();
    }

    public function test_status_label_with_assets_is_not_bulk_deleted()
    {
        $statusLabel = Statuslabel::factory()->create();
        Asset::factory()->create(['status_id' => $statusLabel->id]);

        $this->actingAs(User::factory()->deleteStatusLabels()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [$statusLabel->id],
            ]);

        $this->assertModelExists($statusLabel);
        $this->assertNotSoftDeleted($statusLabel);
    }

    public function test_deletable_status_labels_are_bulk_deleted()
    {
        $label1 = Statuslabel::factory()->create();
        $label2 = Statuslabel::factory()->create();
        $label3 = Statuslabel::factory()->create();

        $this->actingAs(User::factory()->deleteStatusLabels()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [$label1->id, $label2->id, $label3->id],
            ])
            ->assertRedirect(route('statuslabels.index'));

        $this->assertSoftDeleted($label1);
        $this->assertSoftDeleted($label2);
        $this->assertSoftDeleted($label3);
    }

    public function test_partial_success_deletes_the_clean_ones_and_reports_the_rest()
    {
        // One clean, one blocked by an asset. The clean one should be soft-deleted
        // and the blocked one should surface in the multi-error flash bag.
        $deletable = Statuslabel::factory()->create();
        $blocked = Statuslabel::factory()->create();
        Asset::factory()->create(['status_id' => $blocked->id]);

        $this->actingAs(User::factory()->deleteStatusLabels()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [$deletable->id, $blocked->id],
            ])
            ->assertRedirect(route('statuslabels.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted($deletable);
        $this->assertNotSoftDeleted($blocked);
    }

    public function test_nonexistent_ids_are_reported_and_do_not_break_the_batch()
    {
        $deletable = Statuslabel::factory()->create();

        $this->actingAs(User::factory()->deleteStatusLabels()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [$deletable->id, 999999],
            ])
            ->assertRedirect(route('statuslabels.index'))
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted($deletable);
    }

    public function test_bulk_success_message_pluralizes_by_count()
    {
        $solo = Statuslabel::factory()->create();

        $this->actingAs(User::factory()->deleteStatusLabels()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [$solo->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/statuslabels/message.delete.bulk_success', 1, ['count' => 1]));

        $a = Statuslabel::factory()->create();
        $b = Statuslabel::factory()->create();
        $c = Statuslabel::factory()->create();

        $this->actingAs(User::factory()->deleteStatusLabels()->create())
            ->post(route('statuslabels.bulk.delete'), [
                'ids' => [$a->id, $b->id, $c->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/statuslabels/message.delete.bulk_success', 3, ['count' => 3]));
    }
}
