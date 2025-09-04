<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Models\Traits\Acceptable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Accessories.
 *
 * @version v1.0
 */
class AccessoryCheckout extends Model
{
    use Searchable;

    protected $fillable = [
        'accessory_id',
        'assigned_to',
        'assigned_type',
        'note'
    ];

    protected $presenter = \App\Presenters\AccessoryPresenter::class;
    protected $table = 'accessories_checkout';
    
    /**
     * Establishes the accessory checkout -> accessory relationship
     *
     * @author [A. Kroeger]
     * @since  [v7.0.9]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function accessory()
    {
        return $this->hasOne(Accessory::class, 'id', 'accessory_id');
    }

    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'id', 'accessory_id');
    }
    /**
     * Establishes the accessory checkout -> user relationship
     *
     * @author [A. Kroeger]
     * @since  [v7.0.9]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'created_by');
    }

    /**
     * Get the target this asset is checked out to
     *
     * @author [A. Kroeger]
     * @since  [v7.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Gets the lowercased name of the type of target the asset is assigned to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since  [v4.0]
     * @return string
     */
    public function assignedType()
    {
        return $this->assigned_type ? strtolower(class_basename($this->assigned_type)) : null;
    }

    /**
     * Determines whether the accessory is checked out to a user
     *
     * Even though we allow for checkout to things beyond users
     * this method is an easy way of seeing if we are checked out to a user.
     *
     * @author [A. Kroeger]
     * @since  [v7.0]
     */
    public function checkedOutToUser(): bool
    {
        return $this->assigned_type == User::class;
    }

    public function checkedOutToLocation(): bool
    {
        return $this->assigned_type == Location::class;
    }

    public function checkedOutToAsset(): bool
    {
        return $this->assigned_type == Asset::class;
    }

    public function scopeUserAssigned(Builder $query): void
    {
        $query->where('assigned_type', '=', User::class);
    }

    public function scopeLocationAssigned(Builder $query): void
    {
        $query->where('assigned_type', '=', Location::class);
    }

    public function scopeAssetsAssigned(Builder $query): void
    {
        $query->where('assigned_type', '=', Asset::class);
    }

    /**
     * Run additional, advanced searches.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $terms The search terms
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function advancedTextSearch(Builder $query, array $terms)
    {

        $userQuery = User::where(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%' . $term . '%';
                    $query->where('first_name', 'like', $search_str)
                        ->orWhere('last_name', 'like', $search_str)
                        ->orWhere('note', 'like', $search_str);
                }
            }
        )->select('id');

        $locationQuery = Location::where(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%' . $term . '%';
                    $query->where('name', 'like', $search_str);
                }
            }
        )->select('id');

        $assetQuery = Asset::where(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%' . $term . '%';
                    $query->where('name', 'like', $search_str);
                }
            }
        )->select('id');

        $query->where(
            function ($query) use ($userQuery) {
                $query->where('assigned_type', User::class)
                    ->whereIn('assigned_to', $userQuery);
            }
        )->orWhere(
            function ($query) use ($locationQuery) {
                $query->where('assigned_type', Location::class)
                    ->whereIn('assigned_to', $locationQuery);
            }
        )->orWhere(
            function ($query) use ($assetQuery) {
                $query->where('assigned_type', Asset::class)
                    ->whereIn('assigned_to', $assetQuery);
            }
        )->orWhere(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%' . $term . '%';
                    $query->where('note', 'like', $search_str);
                }
            }
        );

        return $query;
    }


}
