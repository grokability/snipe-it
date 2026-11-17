<?php

namespace App\Models;

use App\Models\Builders\AccessoryCheckoutQueryBuilder;
use App\Models\Traits\CompanyableChildTrait;
use App\Models\Traits\Searchable;
use App\Presenters\AccessoryPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Model for Accessories.
 *
 * @version v1.0
 *
 * @mixin IdeHelperAccessoryCheckout
 */
class AccessoryCheckout extends Model
{
    use CompanyableChildTrait;
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'accessory_id',
        'assigned_to',
        'assigned_type',
        'note',
    ];

    /**
     * CompanyableChildScope walks these relation names at query time
     * and inner-joins against the parent, applying CompanyableScope on
     * the parent so pivot rows pointing to accessories in another FMCS
     * tenant drop out of every Eloquent query against this model.
     *
     * @return array<int, string>
     */
    public function getCompanyableParents()
    {
        return ['accessory'];
    }

    protected $presenter = AccessoryPresenter::class;

    protected $table = 'accessories_checkout';

    /**
     * Establishes the accessory checkout -> accessory relationship
     *
     * @author [A. Kroeger]
     *
     * @since  [v7.0.9]
     *
     * @return Relation
     */
    public function accessory()
    {
        return $this->belongsTo(Accessory::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class, 'id', 'accessory_id');
    }

    /**
     * Establishes the accessory checkout -> user relationship
     *
     * @author [A. Kroeger]
     *
     * @since  [v7.0.9]
     *
     * @return Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get the target this asset is checked out to
     *
     * @author [A. Kroeger]
     *
     * @since  [v7.0]
     *
     * @return Relation
     */
    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Gets the lowercased name of the type of target the asset is assigned to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v4.0]
     *
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
     *
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

    public function newEloquentBuilder($query): AccessoryCheckoutQueryBuilder
    {
        return new AccessoryCheckoutQueryBuilder($query);
    }

    /**
     * Run additional, advanced searches.
     *
     * @param  array  $terms  The search terms
     */
    public function advancedTextSearch(Builder $outer, Builder $nested, array $terms): void
    {
        $userQuery = User::where(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%'.$term.'%';
                    $query->where('first_name', 'like', $search_str)
                        ->orWhere('last_name', 'like', $search_str)
                        ->orWhere('note', 'like', $search_str)
                        ->orWhereHas('companies', fn ($q) => $q->where('companies.name', 'like', $search_str));
                }
            }
        )->select('id');

        $locationQuery = Location::where(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%'.$term.'%';
                    $query->where('name', 'like', $search_str);
                }
            }
        )->select('id');

        $assetQuery = Asset::where(
            function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%'.$term.'%';
                    $query->where('name', 'like', $search_str);
                }
            }
        )->select('id');

        $nested->orWhere(
            function ($q) use ($userQuery) {
                $q->where('assigned_type', User::class)
                    ->whereIn('assigned_to', $userQuery);
            }
        )->orWhere(
            function ($q) use ($locationQuery) {
                $q->where('assigned_type', Location::class)
                    ->whereIn('assigned_to', $locationQuery);
            }
        )->orWhere(
            function ($q) use ($assetQuery) {
                $q->where('assigned_type', Asset::class)
                    ->whereIn('assigned_to', $assetQuery);
            }
        )->orWhere(
            function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $search_str = '%'.$term.'%';
                    $q->where('note', 'like', $search_str);
                }
            }
        );
    }
}
