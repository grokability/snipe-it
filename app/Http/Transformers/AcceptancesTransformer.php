<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/**
 * Response shape for GET /api/v1/unaccepted-acceptances, powering the
 * /unaccepted-acceptances bs-table. The row shape leans on the
 * activity-report precedent so the shared polymorphicItemFormatter
 * can render item + target + admin columns with the right per-type
 * icon and link, no bespoke formatters per morph type.
 */
class AcceptancesTransformer
{
    public function transformAcceptances(Collection $acceptances, int $total): array
    {
        $rows = [];
        foreach ($acceptances as $acceptance) {
            $rows[] = self::transformAcceptance($acceptance);
        }

        return (new DatatablesTransformer)->transformDatatables($rows, $total);
    }

    private static function transformAcceptance(CheckoutAcceptance $acceptance): array
    {
        $item = $acceptance->checkoutable;
        $assignee = $acceptance->assignedTo;
        $admin = self::resolveCheckoutAdmin($acceptance);
        $hasEmail = $assignee !== null && ! empty($assignee->email);
        $isTrashed = $acceptance->trashed();

        return [
            'id' => (int) $acceptance->id,
            'created_at' => Helper::getFormattedDateObject($acceptance->created_at, 'datetime'),
            'item' => $item ? [
                'id' => (int) ($item instanceof LicenseSeat ? $item->license?->id : $item->id),
                'name' => e($item instanceof LicenseSeat
                    ? (string) $item->license?->name
                    : (string) ($item->display_name ?? $item->name)),
                'type' => self::itemTypeKey($acceptance->checkoutable_type),
                'deleted_at' => (method_exists($item, 'trashed') && $item->trashed())
                    ? Helper::getFormattedDateObject($item->deleted_at, 'datetime')
                    : null,
                'viewable' => Gate::allows('view', $item instanceof LicenseSeat ? ($item->license ?? $item) : $item),
            ] : null,
            'company' => self::resolveCompany($item),
            'category' => self::resolveCategory($item),
            'model' => self::resolveModel($item),
            'asset_tag' => $item instanceof Asset ? e((string) ($item->asset_tag ?? '')) : null,
            'target' => $assignee ? [
                'id' => (int) $assignee->id,
                'name' => e($assignee->display_name),
                'username' => e($assignee->username ?? ''),
                'type' => 'user',
                'deleted_at' => $assignee->trashed() ? Helper::getFormattedDateObject($assignee->deleted_at, 'datetime') : null,
                'viewable' => Gate::allows('view', $assignee),
            ] : null,
            'checked_out_by' => $admin ? [
                'id' => (int) $admin->id,
                'name' => e($admin->display_name),
                'type' => 'user',
                'deleted_at' => $admin->trashed() ? Helper::getFormattedDateObject($admin->deleted_at, 'datetime') : null,
                'viewable' => Gate::allows('view', $admin),
            ] : null,
            'deleted_at' => $isTrashed ? Helper::getFormattedDateObject($acceptance->deleted_at, 'datetime') : null,
            // Mirrors the row-level buttons: send-reminder is only
            // available if the assignee has an email and the row
            // isn't soft-deleted. Delete only when not already
            // soft-deleted. bulk_selectable gates the row checkbox
            // so no-email + trashed rows don't participate in bulk.
            'available_actions' => [
                'send_reminder' => $hasEmail && ! $isTrashed,
                'delete' => ! $isTrashed,
                'bulk_selectable' => [
                    'send_reminder' => $hasEmail && ! $isTrashed,
                ],
            ],
        ];
    }

    /**
     * polymorphicItemFormatter's switch keys off these lowercase
     * strings to pick the icon + destination. Keep the mapping in
     * one place so a new checkoutable type here can't drift from
     * the formatter's known set.
     */
    private static function itemTypeKey(string $checkoutableType): string
    {
        return match ($checkoutableType) {
            Accessory::class => 'accessory',
            Consumable::class => 'consumable',
            Component::class => 'component',
            LicenseSeat::class => 'license',
            default => 'asset',
        };
    }

    /**
     * Who physically did the checkout. Pulled from the matching
     * action_log row's adminuser. The lookup uses a 1-second window
     * on created_at rather than exact equality, matching the
     * tolerance in UnacceptedAcceptancesController::dispatchReminder.
     * Returns null when no matching log row exists on the item (rare,
     * but tolerable since this column is informational).
     */
    private static function resolveCheckoutAdmin(CheckoutAcceptance $acceptance): mixed
    {
        $item = $acceptance->checkoutable;
        $created = $acceptance->created_at;
        if (! $item || ! $created) {
            return null;
        }

        $window = [
            $created->copy()->subSecond(),
            $created->copy()->addSecond(),
        ];

        $logItem = $item instanceof LicenseSeat
            ? $item->license?->checkouts()->with('adminuser')->whereBetween('created_at', $window)->orderByDesc('created_at')->first()
            : $item->checkouts()->with('adminuser')->whereBetween('created_at', $window)->orderByDesc('created_at')->first();

        return $logItem?->adminuser;
    }

    /**
     * @return ?array{id: int, name: string}
     */
    private static function resolveCompany($item): ?array
    {
        $company = $item instanceof LicenseSeat
            ? $item->license?->company
            : ($item->company ?? null);

        return $company ? [
            'id' => (int) $company->id,
            'name' => e($company->name),
        ] : null;
    }

    /**
     * @return ?array{id: int, name: string}
     */
    private static function resolveCategory($item): ?array
    {
        $category = match (true) {
            $item instanceof Asset => $item->model?->category,
            $item instanceof LicenseSeat => $item->license?->category,
            default => $item->category ?? null,
        };

        return $category ? [
            'id' => (int) $category->id,
            'name' => e($category->name),
        ] : null;
    }

    /**
     * @return ?array{id: ?int, name: string}
     */
    private static function resolveModel($item): ?array
    {
        if ($item instanceof Asset) {
            return $item->model ? [
                'id' => (int) $item->model->id,
                'name' => e($item->model->name),
            ] : null;
        }

        if ($item instanceof Accessory || $item instanceof Consumable || $item instanceof Component) {
            $modelNumber = $item->model_number ?? '';

            return $modelNumber !== '' ? [
                'id' => null,
                'name' => e($modelNumber),
            ] : null;
        }

        return null;
    }
}
