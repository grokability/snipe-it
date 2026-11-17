<?php

namespace App\Models\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @template TModel of User
 *
 * @extends Builder<TModel>
 */
class UserQueryBuilder extends Builder
{
    public function getNotDeleted(): self
    {
        return $this->whereNull('deleted_at');
    }

    public function matchEmailOrUsername(string $user_username, string $user_email): self
    {
        return $this->where('email', '=', $user_email)
            ->orWhere('username', '=', $user_username)
            ->orWhere('username', '=', $user_email);
    }

    public function simpleNameSearch(string $search): self
    {
        return $this->where('first_name', 'LIKE', '%'.$search.'%')
            ->orWhere('last_name', 'LIKE', '%'.$search.'%')
            ->orWhere('display_name', 'LIKE', '%'.$search.'%')
            ->orWhereMultipleColumns(
                [
                    'users.first_name',
                    'users.last_name',
                ], $search
            )
            ->orWhereMultipleColumns(
                [
                    'users.last_name',
                    'users.first_name',
                ], $search
            );
    }

    public function byGroup(int $id): self
    {
        return $this->whereHas('groups', function ($query) use ($id) {
            $query->where('permission_groups.id', '=', $id);
        });
    }

    public function onlySuperAdmins(): self
    {
        return $this->where('users.permissions', 'LIKE', '%"superuser":"1"%')
            ->orWhere('users.permissions', 'LIKE', '%"superuser":1%')
            ->orWhereHas('groups', function ($query) {
                $query->where('permission_groups.permissions', 'LIKE', '%"superuser":"1"%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"superuser":1%');
            });
    }

    public function onlyAdminsAndSuperAdmins(): self
    {
        return $this->where('users.permissions', 'LIKE', '%"superuser":"1"%')
            ->orWhere('users.permissions', 'LIKE', '%"superuser":1%')
            ->orWhere('users.permissions', 'LIKE', '%"admin":1%')
            ->orWhere('users.permissions', 'LIKE', '%"admin":"1"%')
            ->orWhereHas('groups', function ($query) {
                $query->where('permission_groups.permissions', 'LIKE', '%"superuser":"1"%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"superuser":1%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"admin":1%')
                    ->orWhere('permission_groups.permissions', 'LIKE', '%"admin":"1"%');
            });
    }

    public function orderManager(string $order): self
    {
        // Left join here, or it will only return results with parents
        return $this
            ->leftJoin('users as users_manager', 'users.manager_id', '=', 'users_manager.id')
            ->orderBy('users_manager.first_name', $order)
            ->orderBy('users_manager.last_name', $order);
    }

    public function orderLocation(string $order): self
    {
        return $this
            ->leftJoin('locations as locations_users', 'users.location_id', '=', 'locations_users.id')
            ->orderBy('locations_users.name', $order);
    }

    public function orderDepartment(string $order): self
    {
        return $this
            ->leftJoin('departments as departments_users', 'users.department_id', '=', 'departments_users.id')
            ->orderBy('departments_users.name', $order);
    }

    public function orderByCreatedBy(string $order): self
    {
        // Left join here, or it will only return results with parents
        return $this
            ->leftJoin('users as admin_user', 'users.created_by', '=', 'admin_user.id')
            ->orderBy('admin_user.first_name', $order)
            ->orderBy('admin_user.last_name', $order);
    }

    public function orderCompany(string $order): self
    {
        // The MIN(...) aggregate has to go through DB::raw, which
        // bypasses Laravel's grammar, so the `companies` table
        // reference inside the raw string won't get DB_PREFIX applied
        // automatically. Same pattern as Api\AssetsController::index's
        // natural-sort branch.
        $prefix = DB::getTablePrefix();
        $sub = DB::table('company_user')
            ->join('companies', 'companies.id', '=', 'company_user.company_id')
            ->select('company_user.user_id', DB::raw('MIN('.$prefix.'companies.name) as min_company_name'))
            ->groupBy('company_user.user_id');

        return $this
            ->leftJoinSub($sub, 'companies_sort', 'companies_sort.user_id', '=', 'users.id')
            ->orderBy('companies_sort.min_company_name', $order);
    }

    public function userLocation(int $location, string $search): self
    {
        return $this->where('location_id', '=', $location)
            ->where('users.first_name', 'LIKE', '%'.$search.'%')
            ->orWhere('users.email', 'LIKE', '%'.$search.'%')
            ->orWhere('users.last_name', 'LIKE', '%'.$search.'%')
            ->orWhere('users.permissions', 'LIKE', '%'.$search.'%')
            ->orWhere('users.country', 'LIKE', '%'.$search.'%')
            ->orWhere('users.phone', 'LIKE', '%'.$search.'%')
            ->orWhere('users.jobtitle', 'LIKE', '%'.$search.'%')
            ->orWhere('users.employee_num', 'LIKE', '%'.$search.'%')
            ->orWhere('users.username', 'LIKE', '%'.$search.'%')
            ->orWhere('users.display_name', 'LIKE', '%'.$search.'%')
            ->orWhereRaw('CONCAT(users.first_name," ",users.last_name) LIKE ?', [$search.'%']);
    }

    public function withInventoryRelations(
        int $id,
        bool $withAssets = true,
        bool $withLicenses = true,
        bool $withAccessories = true,
        bool $withConsumables = true,
        bool $withComponents = true,
    ): self {
        $with = [];

        if ($withAssets) {
            $with = array_merge($with, [
                'assets.log' => fn ($query) => $query->withTrashed()
                    ->where('target_type', User::class)
                    ->where('target_id', $id)
                    ->where('action_type', 'accepted'),
                'assets.defaultLoc',
                'assets.location',
                'assets.model.category',
                'assets.assignedAssets.log' => fn ($query) => $query->withTrashed()
                    ->where('target_type', User::class)
                    ->where('target_id', $id)
                    ->where('action_type', 'accepted'),
                'assets.assignedAssets.assignedTo',
                'assets.assignedAssets.defaultLoc',
                'assets.assignedAssets.location',
                'assets.assignedAssets.model.category',
            ]);

            if ($withComponents) {
                $with[] = 'assets.components.category';
            }

            if ($withLicenses) {
                $with = array_merge($with, [
                    'assets.licenses',
                    'assets.licenses.category',
                ]);
            }

            if ($withAccessories) {
                $with = array_merge($with, [
                    'assets.assignedAccessories',
                    'assets.assignedAccessories.accessory.category',
                ]);
            }
        }

        if ($withLicenses) {
            $with = array_merge($with, [
                'directLicenses.category',
                'licenses.category',
            ]);
        }

        if ($withAccessories) {
            $with = array_merge($with, [
                'accessories.log' => fn ($query) => $query->withTrashed()
                    ->where('target_type', User::class)
                    ->where('target_id', $id)
                    ->where('action_type', 'accepted'),
                'accessories.category',
                'accessories.manufacturer',
            ]);
        }

        if ($withConsumables) {
            $with = array_merge($with, [
                'consumables.log' => fn ($query) => $query->withTrashed()
                    ->where('target_type', User::class)
                    ->where('target_id', $id)
                    ->where('action_type', 'accepted'),
                'consumables.category',
                'consumables.manufacturer',
            ]);
        }

        return $this->where('id', $id)->with($with)->withTrashed();
    }
}
