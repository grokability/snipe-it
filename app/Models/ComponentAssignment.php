<?php

namespace App\Models;

use App\Models\Builders\ComponentAssignmentQueryBuilder;
use App\Models\Traits\Searchable;
use App\Presenters\ComponentPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Model for Accessories.
 *
 * @version v1.0
 * @mixin IdeHelperComponentAssignment
 */
class ComponentAssignment extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'accessory_id',
        'assigned_to',
        'assigned_type',
        'note',
    ];

    protected $presenter = ComponentPresenter::class;

    protected $table = 'components_assets';

    /**
     * Establishes the accessory checkout -> accessory relationship
     *
     * @author [A. Kroeger]
     *
     * @since  [v7.0.9]
     *
     * @return Relation
     */
    public function component()
    {
        return $this->belongsTo(Component::class)->withTrashed();
    }

    public function components()
    {
        return $this->hasMany(Component::class, 'id', 'component_id')->withTrashed();
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'id', 'asset_id')->withTrashed();
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

    public function newEloquentBuilder($query): ComponentAssignmentQueryBuilder
    {
        return new ComponentAssignmentQueryBuilder($query);
    }
}
