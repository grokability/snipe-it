<?php

namespace App\Models\Builders;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceQueryBuilder extends Builder
{
    public function active(): static
    {
        return $this->whereNull('maintenances.completed_at');
    }

    public function completed(): static
    {
        return $this->whereNotNull('maintenances.completed_at');
    }

    public function dueForCompletion(Setting $settings): static
    {
        $interval = (int) ($settings->audit_warning_days ?? 0);
        $today = Carbon::now();

        return $this->whereNotNull('maintenances.expected_completion_date')
            ->whereNull('maintenances.completed_at')
            ->whereBetween('maintenances.expected_completion_date', [
                $today->format('Y-m-d'),
                $today->copy()->addDays($interval)->format('Y-m-d'),
            ]);
    }

    public function overdueForCompletion(): static
    {
        return $this->whereNotNull('maintenances.expected_completion_date')
            ->whereNull('maintenances.completed_at')
            ->where('maintenances.expected_completion_date', '<', Carbon::now()->format('Y-m-d'));
    }

    public function dueOrOverdueForCompletion(Setting $settings): static
    {
        return $this->where(fn ($q) => $q->overdueForCompletion())
            ->orWhere(fn ($q) => $q->dueForCompletion($settings));
    }

    public function orderBySupplier(string $order): static
    {
        return $this->leftJoin('suppliers as suppliers_maintenances', 'maintenances.supplier_id', '=', 'suppliers_maintenances.id')
            ->orderBy('suppliers_maintenances.name', $order);
    }

    public function orderByTag(string $order): static
    {
        return $this->leftJoin('assets', 'maintenances.asset_id', '=', 'assets.id')
            ->orderBy('assets.asset_tag', $order);
    }

    public function orderByAssetName(string $order): static
    {
        return $this->leftJoin('assets', 'maintenances.asset_id', '=', 'assets.id')
            ->orderBy('assets.name', $order);
    }

    public function orderByAssetSerial(string $order): static
    {
        return $this->leftJoin('assets', 'maintenances.asset_id', '=', 'assets.id')
            ->orderBy('assets.serial', $order);
    }

    public function orderStatusName(string $order): static
    {
        return $this->join('assets as maintained_asset', 'maintenances.asset_id', '=', 'maintained_asset.id')
            ->leftJoin('status_labels as maintained_asset_status', 'maintained_asset_status.id', '=', 'maintained_asset.status_id')
            ->orderBy('maintained_asset_status.name', $order);
    }

    public function orderLocationName(string $order): static
    {
        return $this->join('assets as maintained_asset', 'maintenances.asset_id', '=', 'maintained_asset.id')
            ->leftJoin('locations as maintained_asset_location', 'maintained_asset_location.id', '=', 'maintained_asset.location_id')
            ->orderBy('maintained_asset_location.name', $order);
    }

    public function orderByCreatedBy(string $order): static
    {
        return $this->leftJoin('users as admin_sort', 'maintenances.created_by', '=', 'admin_sort.id')
            ->select('maintenances.*')
            ->orderBy('admin_sort.first_name', $order)
            ->orderBy('admin_sort.last_name', $order);
    }

    public function orderByAssetModelName(string $order): static
    {
        return $this->join('assets as maintained_asset', 'maintenances.asset_id', '=', 'maintained_asset.id')
            ->leftJoin('models as maintained_asset_model', 'maintained_asset_model.id', '=', 'maintained_asset.model_id')
            ->orderBy('maintained_asset_model.name', $order);
    }

    public function orderByAssetModelNumber(string $order): static
    {
        return $this->join('assets as maintained_asset', 'maintenances.asset_id', '=', 'maintained_asset.id')
            ->leftJoin('models as maintained_asset_model', 'maintained_asset_model.id', '=', 'maintained_asset.model_id')
            ->orderBy('maintained_asset_model.model_number', $order);
    }

    public function orderByMaintenanceType(string $order): static
    {
        return $this->leftJoin('maintenance_types as maintenance_type_sort', 'maintenances.maintenance_type_id', '=', 'maintenance_type_sort.id')
            ->orderBy('maintenance_type_sort.name', $order);
    }

    public function orderByCompletedAt(string $order): static
    {
        return $this->orderBy('maintenances.completed_at', $order);
    }
}
