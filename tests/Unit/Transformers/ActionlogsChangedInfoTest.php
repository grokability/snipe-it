<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\ActionlogsTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre changedInfo() (resolucion de id -> nombre para location/model/company/
 * supplier/status/eol) y getQuantity() de ActionlogsTransformer, que no estaban cubiertos.
 */
class ActionlogsChangedInfoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function logWithMeta(array $meta, string $actionType = 'update'): Actionlog
    {
        return Actionlog::factory()->create([
            'action_type' => $actionType,
            'item_id' => Asset::factory()->create()->id,
            'item_type' => Asset::class,
            'log_meta' => json_encode($meta),
        ]);
    }

    public function test_changed_info_resolves_existing_entities(): void
    {
        $oldLoc = Location::factory()->create();
        $newLoc = Location::factory()->create();
        $oldModel = AssetModel::factory()->create();
        $newModel = AssetModel::factory()->create();
        $oldCompany = Company::factory()->create();
        $newCompany = Company::factory()->create();
        $oldSupplier = Supplier::factory()->create();
        $newSupplier = Supplier::factory()->create();
        $oldStatus = Statuslabel::factory()->create();
        $newStatus = Statuslabel::factory()->create();

        $log = $this->logWithMeta([
            'rtd_location_id' => ['old' => $oldLoc->id, 'new' => $newLoc->id],
            'location_id' => ['old' => $oldLoc->id, 'new' => $newLoc->id],
            'model_id' => ['old' => $oldModel->id, 'new' => $newModel->id],
            'company_id' => ['old' => $oldCompany->id, 'new' => $newCompany->id],
            'supplier_id' => ['old' => $oldSupplier->id, 'new' => $newSupplier->id],
            'status_id' => ['old' => $oldStatus->id, 'new' => $newStatus->id],
            'asset_eol_date' => ['old' => '2025-01-01', 'new' => '2026-01-01'],
        ]);

        $result = (new ActionlogsTransformer)->transformActionlog($log, Setting::getSettings());

        $this->assertIsArray($result['log_meta']);
        // Las claves se renombran a etiquetas traducidas; ya no existe el id crudo.
        $this->assertArrayNotHasKey('location_id', $result['log_meta']);
        $this->assertArrayNotHasKey('model_id', $result['log_meta']);
    }

    public function test_changed_info_handles_deleted_entities(): void
    {
        // ids inexistentes -> rama "deleted"/"unassigned".
        $log = $this->logWithMeta([
            'rtd_location_id' => ['old' => 999991, 'new' => 999992],
            'location_id' => ['old' => 999991, 'new' => 999992],
            'model_id' => ['old' => 999991, 'new' => 999992],
            'company_id' => ['old' => 999991, 'new' => 999992],
            'supplier_id' => ['old' => 999991, 'new' => 999992],
            'status_id' => ['old' => 999991, 'new' => 999992],
        ]);

        $result = (new ActionlogsTransformer)->transformActionlog($log, Setting::getSettings());

        $this->assertIsArray($result['log_meta']);
    }

    public function test_changed_info_handles_null_old_values(): void
    {
        // old null -> rama de cadena vacia / unassigned.
        $log = $this->logWithMeta([
            'company_id' => ['old' => null, 'new' => Company::factory()->create()->id],
            'supplier_id' => ['old' => null, 'new' => Supplier::factory()->create()->id],
            'status_id' => ['old' => null, 'new' => Statuslabel::factory()->create()->id],
            'location_id' => ['old' => null, 'new' => Location::factory()->create()->id],
        ]);

        $result = (new ActionlogsTransformer)->transformActionlog($log, Setting::getSettings());

        $this->assertIsArray($result['log_meta']);
    }

    public function test_get_quantity_for_checkout_action(): void
    {
        $log = Actionlog::factory()->create([
            'action_type' => 'checkout',
            'item_id' => Asset::factory()->create()->id,
            'item_type' => Asset::class,
            'quantity' => 5,
        ]);

        $result = (new ActionlogsTransformer)->transformActionlog($log, Setting::getSettings());

        $this->assertSame(5, $result['quantity']);
    }

    public function test_get_quantity_null_for_irrelevant_action(): void
    {
        $log = Actionlog::factory()->create([
            'action_type' => 'update',
            'item_id' => Asset::factory()->create()->id,
            'item_type' => Asset::class,
            'quantity' => 5,
        ]);

        $result = (new ActionlogsTransformer)->transformActionlog($log, Setting::getSettings());

        // 'update' no esta en la lista de tipos con cantidad relevante.
        $this->assertNull($result['quantity']);
    }

    public function test_transform_checkedout_actionlog(): void
    {
        User::factory()->count(2)->create();
        $users = User::query()->get();

        $result = (new ActionlogsTransformer)->transformCheckedoutActionlog($users, $users->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals($users->count(), $result['total']);
    }
}
