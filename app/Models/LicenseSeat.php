<?php

namespace App\Models;

use App\Models\Builders\LicenseSeatQueryBuilder;
use App\Models\Contracts\CompanyableChild;
use App\Models\Traits\Acceptable;
use App\Models\Traits\CompanyableChildTrait;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\LicenseSeatPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperLicenseSeat
 */
class LicenseSeat extends SnipeModel implements CompanyableChild
{
    use Acceptable;
    use CompanyableChildTrait;
    use HasFactory;
    use Loggable;
    use Presentable;
    use Searchable;
    use SoftDeletes;

    protected $presenter = LicenseSeatPresenter::class;

    protected $guarded = 'id';

    protected $table = 'license_seats';

    protected $casts = [
        'unreassignable_seat' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'assigned_to',
        'asset_id',
        'notes',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'notes',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'user' => ['first_name', 'last_name', 'display_name', 'username', 'email'],
        'asset' => ['name', 'asset_tag'],
    ];

    public function getCompanyableParents()
    {
        return ['asset', 'license'];
    }

    /**
     * Determine whether the user should be required to accept the license
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return bool
     */
    public function requireAcceptance()
    {
        if ($this->license && $this->license->category) {
            return $this->license->category->require_acceptance;
        }

        return false;
    }

    public function getEula()
    {
        return $this->license->getEula();
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->license?->name,
        );
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $this->license?->name,
        );
    }

    /**
     * Establishes the seat -> license relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    /**
     * Establishes the seat -> assignee relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    /**
     * Establishes the seat -> asset relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return Relation
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id')->withTrashed();
    }

    /**
     * Determines the assigned seat's location based on user
     * or asset its assigned to
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v4.0]
     *
     * @return string
     */
    public function location()
    {
        if (($this->user) && ($this->user->location)) {
            return $this->user->location;
        } elseif (($this->asset) && ($this->asset->location)) {
            return $this->asset->location;
        }

        return false;
    }

    /**
     * Get the list of checkouts for this License
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function checkouts()
    {
        return $this->assetlog()->where('action_type', '=', 'checkout')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Establishes the license -> action logs relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since  [v3.0]
     *
     * @return Relation
     */
    public function assetlog()
    {
        return $this->hasMany(Actionlog::class, 'item_id')->where('item_type', self::class)->orderBy('created_at', 'desc')->withTrashed();
    }

    public function newEloquentBuilder($query): LicenseSeatQueryBuilder
    {
        return new LicenseSeatQueryBuilder($query);
    }
}
