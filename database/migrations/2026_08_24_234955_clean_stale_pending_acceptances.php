<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Soft-deletes pending acceptance requests that no longer correspond to
 * anything the holder has, cleaning up after the checkin/transfer bugs fixed
 * alongside this. Pendings for units still held are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $clearedAt = now();

        // Assets and license seats are 1:1 with their acceptance rows, so a
        // boolean "still holds it" test is enough. A trashed item does not
        // count: a restored asset comes back unassigned and licenses have no
        // restore path, so the pending could never become actionable.
        $this->clean(Asset::class, $clearedAt, function (Builder $query) {
            $query->from('assets')
                ->whereColumn('assets.id', 'checkout_acceptances.checkoutable_id')
                ->whereColumn('assets.assigned_to', 'checkout_acceptances.assigned_to_id')
                ->where('assets.assigned_type', User::class)
                ->whereNull('assets.deleted_at');
        });

        $this->clean(LicenseSeat::class, $clearedAt, function (Builder $query) {
            $query->from('license_seats')
                ->whereColumn('license_seats.id', 'checkout_acceptances.checkoutable_id')
                ->whereColumn('license_seats.assigned_to', 'checkout_acceptances.assigned_to_id')
                ->whereNull('license_seats.deleted_at');
        });

        // Accessories and consumables are not 1:1: their pivots hold one row
        // per unit, so a held/not-held test cannot see "holds one unit, has
        // pendings worth three".
        $this->reconcile(Accessory::class, $clearedAt, 'accessories_checkout', 'accessory_id', pivotHasAssignedType: true);
        $this->reconcile(Consumable::class, $clearedAt, 'consumables_users', 'consumable_id', pivotHasAssignedType: false);
    }

    public function down(): void
    {
        // No-op. These rows were already meaningless when this migration ran,
        // and restoring them is not possible anyway: there is no way to tell
        // them apart from acceptances soft-deleted by a normal checkin.
    }

    /**
     * Soft-delete every pending acceptance of one 1:1 type whose holder no
     * longer has the item. `$stillHolds` receives a subquery that should match
     * a row proving the pair still holds it; anything unmatched gets cleared.
     *
     * The `deleted_at` filter is what makes re-running this a no-op.
     */
    private function clean(string $checkoutableType, DateTimeInterface $clearedAt, Closure $stillHolds): void
    {
        DB::table('checkout_acceptances')
            ->select('id')
            ->where('checkoutable_type', $checkoutableType)
            ->whereNull('deleted_at')
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->whereNotExists($stillHolds)
            ->chunkById(500, function (Collection $acceptances) use ($clearedAt) {
                DB::table('checkout_acceptances')
                    ->whereIn('id', $acceptances->pluck('id'))
                    ->update(['deleted_at' => $clearedAt]);
            });
    }

    /**
     * Bring each pair's total pending qty down to the units it actually holds.
     *
     * Excess is retired oldest row first: a row is soft-deleted when all of it
     * is excess, decremented when only part is. A pair holding nothing loses
     * every row, so this is a superset of the boolean pass, not a departure.
     * Re-running computes zero excess and writes nothing.
     */
    private function reconcile(
        string $checkoutableType,
        DateTimeInterface $clearedAt,
        string $pivotTable,
        string $itemColumn,
        bool $pivotHasAssignedType,
    ): void {
        foreach ($this->pairsWithPendingAcceptances($checkoutableType) as $pair) {
            $acceptances = $this->pendingAcceptancesFor($checkoutableType, $pair);

            $unitsHeld = DB::table($pivotTable)
                ->where($itemColumn, $pair->checkoutable_id)
                ->where('assigned_to', $pair->assigned_to_id)
                ->when($pivotHasAssignedType, fn ($query) => $query->where('assigned_type', User::class))
                ->count();

            $excess = $this->totalQty($acceptances) - $unitsHeld;

            foreach ($acceptances as $acceptance) {
                if ($excess <= 0) {
                    break;
                }

                $qty = $acceptance->qty ?? 1;

                if ($qty <= $excess) {
                    DB::table('checkout_acceptances')
                        ->where('id', $acceptance->id)
                        ->update(['deleted_at' => $clearedAt]);

                    $excess -= $qty;

                    continue;
                }

                DB::table('checkout_acceptances')
                    ->where('id', $acceptance->id)
                    ->update(['qty' => $qty - $excess]);

                $excess = 0;
            }
        }
    }

    /**
     * The distinct (item, holder) pairs with anything pending. Grouping keeps
     * this to one row per pair rather than loading the whole table.
     *
     * @return Collection<int, stdClass>
     */
    private function pairsWithPendingAcceptances(string $checkoutableType): Collection
    {
        return DB::table('checkout_acceptances')
            ->select('checkoutable_id', 'assigned_to_id')
            ->where('checkoutable_type', $checkoutableType)
            ->whereNull('deleted_at')
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->groupBy('checkoutable_id', 'assigned_to_id')
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    private function pendingAcceptancesFor(string $checkoutableType, stdClass $pair): Collection
    {
        return DB::table('checkout_acceptances')
            ->select('id', 'qty')
            ->where('checkoutable_type', $checkoutableType)
            ->where('checkoutable_id', $pair->checkoutable_id)
            ->where('assigned_to_id', $pair->assigned_to_id)
            ->whereNull('deleted_at')
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Null qty means one unit, as in AcceptanceController and LogListener.
     * Rows predating the qty column, and every asset and seat acceptance,
     * carry null.
     *
     * @param  Collection<int, stdClass>  $acceptances
     */
    private function totalQty(Collection $acceptances): int
    {
        return (int) $acceptances->sum(fn (stdClass $acceptance) => $acceptance->qty ?? 1);
    }
};
