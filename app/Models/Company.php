<?php

namespace App\Models;

use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Companies.
 *
 * @version v1.8
 */
final class Company extends SnipeModel
{
    use HasFactory;
    use CompanyableTrait;


    protected $table = 'companies';

    // Declare the rules for the model validation
    protected $rules = [
        'name' => 'required|min:1|max:255|unique:companies,name',
        'fax' => 'min:7|max:35|nullable',
        'phone' => 'min:7|max:35|nullable',
    'email' => 'email|max:150|nullable',
    ];

    protected $presenter = \App\Presenters\CompanyPresenter::class;
    use Presentable;

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;
    use Searchable;
    
    /**
     * The attributes that should be included when searching the model.
     * 
     * @var array
     */
    protected $searchableAttributes = ['name', 'phone', 'fax', 'email', 'created_at', 'updated_at'];

    /**
     * The relations and their attributes that should be included when searching the model.
     * 
     * @var array
     */
    protected $searchableRelations = [];   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'fax',
        'email',
        'created_by',
        'notes',
    ];

    private static function isFullMultipleCompanySupportEnabled()
    {
        $settings = Setting::getSettings();

        // NOTE: this can happen when seeding the database
        if (is_null($settings)) {
            return false;
        } else {
            return $settings->full_multiple_companies_support == 1;
        }
    }


    public static function getIdFromInput($unescaped_input)
    {
        $escaped_input = e($unescaped_input);

        if ($escaped_input == '0') {
            return null;
        } else {
            return $escaped_input;
        }
    }

    /**
     * Get the company id for the current user taking into
     * account the full multiple company support setting
     * and if the current user is a super user.
     *
     * @param  $unescaped_input
     * @return int|mixed|string|null
     */
    public static function getIdForCurrentUser($unescaped_input)
    {
        if (! static::isFullMultipleCompanySupportEnabled()) {
            return static::getIdFromInput($unescaped_input);
        } else {
            $current_user = auth()->user();

            // Super users should be able to set a company to whatever they need
            if ($current_user->isSuperUser()) {
                return static::getIdFromInput($unescaped_input);
            } else {
                if ($current_user->company_id != null) {
                    return $current_user->company_id;
                } else {
                    return null;
                }
            }
        }
    }

    /**
     * Check to see if the current user should have access to the model.
     * I hate this method and I think it should be refactored.
     *
     * @param  $companyable
     * @return bool|void
     */
    public static function isCurrentUserHasAccess($companyable)
    {
        // When would this even happen tho??
        if (is_null($companyable)) {
            return false;
        }

        // If FMCS is not enabled, everyone has access, return true
        if (! static::isFullMultipleCompanySupportEnabled()) {
            return true;
        }

        // Again, where would this happen? But check that $companyable is not a string
        if (!is_string($companyable)) {
            $company_table = $companyable->getModel()->getTable();
            try {
                // This is primarily for the gate:allows-check in location->isDeletable()
                // Locations don't have a company_id so without this it isn't possible to delete locations with FullMultipleCompanySupport enabled
                // because this function is called by SnipePermissionsPolicy->before()
                if (!Schema::hasColumn($company_table, 'company_id')) {
                    return true;
                }

            } catch (\Exception $e) {
                Log::warning($e);
            }
        }


        if (auth()->user()) {
            // Log::warning('Companyable is '.$companyable);
            $current_user_company_id = auth()->user()->company_id;
            $companyable_company_id = $companyable->company_id;

            // Set this to check companyable on company
            if ($companyable instanceof Company) {
                $companyable_company_id = $companyable->id;
            }
            return ($current_user_company_id == null) || ($current_user_company_id == $companyable_company_id) || auth()->user()->isSuperUser();
        }

        return false;

    }

    public static function isCurrentUserAuthorized()
    {
        return (! static::isFullMultipleCompanySupportEnabled()) || (auth()->user()->isSuperUser());
    }

    public static function canManageUsersCompanies()
    {
        return ! static::isFullMultipleCompanySupportEnabled() || auth()->user()->isSuperUser() ||
                auth()->user()->company_id == null;
    }

    /**
     * Checks if company can be deleted
     *
     * @author [Dan Meltzer] [<dmeltzer.devel@gmail.com>]
     * @since  [v5.0]
     * @return bool
     */
    public function isDeletable()
    {

        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->licenses_count ?? $this->licenses()->count()) === 0)
            && (($this->components_count ?? $this->components()->count()) === 0)
            && (($this->consumables_count ?? $this->consumables()->count()) === 0)
            && (($this->accessories_count ?? $this->accessories()->count()) === 0)
            && (($this->users_count ?? $this->users()->count()) === 0);
    }

    /**
     * @param  $unescaped_input
     * @return int|mixed|string|null
     */
    public static function getIdForUser($unescaped_input)
    {
        if (! static::isFullMultipleCompanySupportEnabled() || auth()->user()->isSuperUser()) {
            return static::getIdFromInput($unescaped_input);
        } else {
            return static::getIdForCurrentUser($unescaped_input);
        }
    }


    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'company_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'company_id');
    }

    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'company_id');
    }

    public function consumables()
    {
        return $this->hasMany(Consumable::class, 'company_id');
    }

    public function components()
    {
        return $this->hasMany(Component::class, 'company_id');
    }

    /**
     * START COMPANY SCOPING FOR FMCS
     */

    /**
     * Scoping table queries, determining if a logged in user is part of a company, and only allows the user to access items associated with that company if FMCS is enabled.
     *
     * This method is the one that the CompanyableTrait uses to contrain queries automatically, however that trait CANNOT be
     * applied to the user's model, since it causes an infinite loop against the authenticated user.
     *
     * @todo - refactor that trait to handle the user's model as well.
     *
     * @author [A. Gianotto] <snipe@snipe.net>
     * @param  $query
     * @param  $column
     * @param  $table_name
     * @return mixed
     */
    public static function scopeCompanyables($query, $column = 'company_id', $table_name = null)
    {
        // If not logged in and hitting this, assume we are on the command line and don't scope?
        if (! static::isFullMultipleCompanySupportEnabled() || (Auth::hasUser() && auth()->user()->isSuperUser()) || (! Auth::hasUser())) {
            return $query;
        } else {
            return static::scopeCompanyablesDirectly($query, $column, $table_name);
        }
    }

    /**
     * Scoping table queries, determining if a logged-in user is part of a company, and only allows
     * that user to see items associated with that company
     *
     * @see https://github.com/laravel/framework/pull/24518 for info on Auth::hasUser()
     */
    private static function scopeCompanyablesDirectly($query, $column = 'company_id', $table_name = null)
    {

        $company_id = null;
        // Get the company ID of the logged-in user, or set it to null if there is no company associated with the user
        if (Auth::hasUser()) {
            $company_id = auth()->user()->company_id;
        }


        // If we are scoping the companies table itself, look for the company.id
        if ($query->getModel()->getTable() == 'companies') {
            return $query->where('companies.id', '=', $company_id);
        }


        // If the column exists in the table, use it to scope the query
        if ((($query) && ($query->getModel()) && (Schema::hasColumn($query->getModel()->getTable(), $column)))) {

            // Dynamically get the table name if it's not passed in, based on the model we're querying against
            $table = ($table_name) ? $table_name."." : $query->getModel()->getTable().".";

            return $query->where($table.$column, '=', $company_id);
        }



    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }


    /**
     * I legit do not know what this method does, but we can't remove it (yet).
     *
     * This gets invoked by CompanyableChildScope, but I'm not sure what it does.
     *
     * @author [A. Gianotto] <snipe@snipe.net>
     * @param  array $companyable_names
     * @param  $query
     * @return mixed
     */
    public static function scopeCompanyableChildren(array $companyable_names, $query)
    {

        if (count($companyable_names) == 0) {
            throw new Exception('No Companyable Children to scope');
        } elseif (! static::isFullMultipleCompanySupportEnabled() || (Auth::hasUser() && auth()->user()->isSuperUser())) {
            return $query;
        } else {
            $f = function ($q) {
                static::scopeCompanyablesDirectly($q);
            };

            $q = $query->where(
                function ($q) use ($companyable_names, $f) {
                    $q2 = $q->whereHas($companyable_names[0], $f);

                    for ($i = 1; $i < count($companyable_names); $i++) {
                        $q2 = $q2->orWhereHas($companyable_names[$i], $f);
                    }
                }
            );

            return $q;
        }
    }


    /**
     * Query builder scope to order on the user that created it
     */
    public function scopeOrderByCreatedBy($query, $order)
    {
        return $query->leftJoin('users as admin_sort', 'companies.created_by', '=', 'admin_sort.id')->select('companies.*')->orderBy('admin_sort.first_name', $order)->orderBy('admin_sort.last_name', $order);
    }

}
