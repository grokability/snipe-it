<?php

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('statuslabels.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('status label with assets is not bulk deleted', function () {
    $statusLabel = Statuslabel::factory()->create();
    Asset::factory()->create(['status_id' => $statusLabel->id]);

    $this->actingAs(User::factory()->deleteStatusLabels()->create())
        ->post(route('statuslabels.bulk.delete'), [
            'ids' => [$statusLabel->id],
        ]);

    $this->assertModelExists($statusLabel);
    $this->assertNotSoftDeleted($statusLabel);
});

test('deletable status labels are bulk deleted', function () {
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
});

test('partial success deletes the clean ones and reports the rest', function () {
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
});

test('nonexistent ids are reported and do not break the batch', function () {
    $deletable = Statuslabel::factory()->create();

    $this->actingAs(User::factory()->deleteStatusLabels()->create())
        ->post(route('statuslabels.bulk.delete'), [
            'ids' => [$deletable->id, 999999],
        ])
        ->assertRedirect(route('statuslabels.index'))
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted($deletable);
});

test('bulk success message pluralizes by count', function () {
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
});
