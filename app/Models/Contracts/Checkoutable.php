<?php

namespace App\Models\Contracts;

/**
 * This establishes what type of targets an object can be checked out to.
 * It is NOT CHECKING FOR PERMISSIONS OR AVAILABILITY.
 *
 * DOES NOT CHECK:
 * - auth check (actor permission, policy)
 * - FMCS cross-tenant check (CompanyableTrait::canCheckoutTo)
 * - availability check (archived, unreassignable, free seats, remaining qty).
 */
interface Checkoutable
{
    /**
     * Fully-qualified class names of the model types this checkoutable
     * can be checked out to.
     *
     * Order matters here: the first entry is the default tab
     * in Select2 target pickers.
     *
     * Items where the actual checkout happens on a different model
     * (e.g. ComponentAssignment checked out to Asset, but Component is
     * the parent) use the child rules so that keep those rules all in one place.
     *
     * For example, ComponentAssignment::allowedCheckoutTargetTypes()
     * on the parent Component model.
     *
     * @return list<class-string>
     */
    public static function allowedCheckoutTargetTypes(): array;
}
