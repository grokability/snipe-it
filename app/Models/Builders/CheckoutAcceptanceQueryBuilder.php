<?php

namespace App\Models\Builders;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Gate;

/**
 * @template TModel of CheckoutAcceptance
 *
 * @extends Builder<TModel>
 */
class CheckoutAcceptanceQueryBuilder extends Builder
{
    public function forUser(User $user): self
    {
        return $this->where('assigned_to_id', $user->id);
    }

    public function pending(): self
    {
        return $this->whereNull('accepted_at')->whereNull('declined_at');
    }

    public function declined(): self
    {
        return $this->whereNull('accepted_at')->whereNotNull('declined_at');
    }

    /**
     * Narrows to acceptances whose checkoutable the current viewer
     * can `view` AND can see per FMCS. Pre-filters the morph types
     * so types the caller can't view are excluded from the query
     * entirely (no type-shape leak), and whereHasMorph lets each
     * concrete type apply its own CompanyableScope /
     * CompanyableChildScope during the join. The whole page body
     * reflects the viewer-permissioned set, including total count.
     *
     * Eager-loads enough of each checkoutable to render the
     * acceptance list (model, category, company, assignee) without
     * N+1-ing on the transformer.
     */
    public function visibleToCurrentUser(): self
    {
        $viewableTypes = array_filter(
            [Asset::class, LicenseSeat::class, Accessory::class, Component::class, Consumable::class],
            fn (string $type) => Gate::allows('view', $type === LicenseSeat::class ? License::class : $type),
        );

        if ($viewableTypes === []) {
            // whereIn against an empty array produces an empty
            // result without short-circuiting the builder chain,
            // so downstream ->get() / ->orderBy() still work. Portable
            // across MySQL / MariaDB / SQLite without the whereRaw
            // escape hatch.
            return $this->whereIn('id', []);
        }

        return $this
            ->with([
                'checkoutable' => function (MorphTo $morphTo) {
                    $morphTo->withTrashed()->morphWith([
                        Asset::class => ['model.category', 'assignedTo', 'company'],
                        Accessory::class => ['category', 'checkouts', 'company'],
                        LicenseSeat::class => ['user', 'license.company', 'license.category'],
                        Component::class => ['assignedTo', 'category', 'company'],
                        Consumable::class => ['category', 'company'],
                    ]);
                },
                'assignedTo' => fn ($q) => $q->withTrashed(),
            ])
            ->whereHasMorph('checkoutable', $viewableTypes);
    }
}
