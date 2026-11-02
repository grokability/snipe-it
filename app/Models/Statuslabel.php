<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Builders\StatuslabelQueryBuilder;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use App\Presenters\StatusLabelPresenter;
use App\Rules\CssColor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

/**
 * @mixin IdeHelperStatuslabel
 */
class Statuslabel extends SnipeModel
{
    use HasFactory;
    use Presentable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    protected $injectUniqueIdentifier = true;

    protected $table = 'status_labels';

    protected $hidden = ['user_id', 'deleted_at'];

    protected $presenter = StatusLabelPresenter::class;

    protected $rules = [
        'name' => 'required|max:255|string|unique_undeleted',
        'notes' => 'string|nullable',
        'deployable' => 'required',
        'pending' => 'required',
        'archived' => 'required',
    ];

    protected $fillable = [
        'archived',
        'deployable',
        'name',
        'notes',
        'pending',
    ];

    use Searchable;

    /**
     * Sanitize of the color column on read.
     */
    protected function color(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => CssColor::sanitize($value, ''),
        );
    }

    /**
     * Per-request memo of the ID lists used by Asset's status-driven scopes
     * (RTD / Pending / Undeployable / Archived / NotArchived). Each scope
     * fired a separate `SELECT id FROM status_labels WHERE …` query on every
     * call before this was introduced, which under withCount + transformer
     * loops (see AssetModelsTransformer) added dozens of redundant queries
     * per page or API hit.
     *
     * Cleared on save/delete via model events below, and reset between tests
     * in InitializesSettings (alongside Setting::$_cache).
     */
    protected static array $statusIdCache = [];

    /**
     * Return the cached set of status_label IDs matching the requested
     * "kind" — one of: deployable, pending, undeployable, archived,
     * not_archived. Each call after the first in a single request reads
     * from memory.
     */
    public static function idsFor(string $type): Collection
    {
        return self::$statusIdCache[$type] ??= match ($type) {
            'deployable' => self::where('deployable', 1)->where('pending', 0)->where('archived', 0)->whereNull('deleted_at')->pluck('id'),
            'pending' => self::where('deployable', 0)->where('pending', 1)->where('archived', 0)->whereNull('deleted_at')->pluck('id'),
            'undeployable' => self::where('deployable', 0)->where('pending', 0)->where('archived', 0)->whereNull('deleted_at')->pluck('id'),
            'archived' => self::where('deployable', 0)->where('pending', 0)->where('archived', 1)->whereNull('deleted_at')->pluck('id'),
            'not_archived' => self::where('archived', 0)->whereNull('deleted_at')->pluck('id'),
            default => throw new \InvalidArgumentException('Unknown status type: '.$type),
        };
    }

    public static function clearIdCache(): void
    {
        self::$statusIdCache = [];
    }

    protected static function booted(): void
    {
        // Any mutation to a status label invalidates the cached ID lists.
        $invalidate = fn () => self::clearIdCache();
        static::saved($invalidate);
        static::deleted($invalidate);
        static::restored($invalidate);
    }

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    /**
     * Advanced-search filterable fields.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filterableFields(): array
    {
        return [
            [
                'field' => 'type',
                'type' => 'enum',
                'options' => [
                    ['value' => 'deployable', 'label' => trans('admin/statuslabels/table.deployable')],
                    ['value' => 'pending', 'label' => trans('admin/statuslabels/table.pending')],
                    ['value' => 'undeployable', 'label' => trans('admin/statuslabels/table.undeployable')],
                    ['value' => 'archived', 'label' => trans('admin/statuslabels/table.archived')],
                ],
                'label' => trans('admin/statuslabels/table.status_type'),
            ],
            [
                'field' => 'show_in_nav',
                'type' => 'boolean',
                'label' => trans('admin/statuslabels/table.show_in_nav'),
            ],
            [
                'field' => 'default_label',
                'type' => 'boolean',
                'label' => trans('admin/statuslabels/table.default_label'),
            ],
            [
                'field' => 'created_by',
                'type' => 'fk',
                'column' => 'created_by',
                'endpoint' => 'users',
                'label' => trans('general.created_by'),
            ],
            [
                'field' => 'created_at',
                'type' => 'date-range',
                'label' => trans('general.created_at'),
            ],
            [
                'field' => 'updated_at',
                'type' => 'date-range',
                'label' => trans('general.updated_at'),
            ],
        ];
    }

    /**
     * Override hook from Searchable. The `type` filter is a four-way
     * enum (deployable / pending / undeployable / archived) that
     * decomposes into a WHERE on the stored (deployable, pending,
     * archived) tuple. Multi-select is supported by OR-combining the
     * per-value triples inside a single scope so a sibling AND
     * continues to compose cleanly.
     */
    protected function applyCustomFilter(\Illuminate\Database\Eloquent\Builder $query, string $filterKey, string $filterValue, string $boolean = 'and'): ?\Illuminate\Database\Eloquent\Builder
    {
        if ($filterKey !== 'type') {
            return null;
        }

        $tokens = array_values(array_filter(array_map('trim', explode(',', $filterValue)), 'strlen'));
        if ($tokens === []) {
            return null;
        }

        $tuples = [
            'deployable' => [1, 0, 0],
            'pending' => [0, 1, 0],
            'undeployable' => [0, 0, 0],
            'archived' => [0, 0, 1],
        ];
        $matched = array_values(array_intersect_key($tuples, array_flip($tokens)));
        if ($matched === []) {
            return null;
        }

        $outerBoolean = $boolean === 'or' ? 'orWhere' : 'where';
        $query->{$outerBoolean}(function ($nested) use ($matched) {
            foreach ($matched as $triple) {
                $nested->orWhere(function ($row) use ($triple) {
                    $row->where('deployable', $triple[0])
                        ->where('pending', $triple[1])
                        ->where('archived', $triple[2]);
                });
            }
        });

        return $query;
    }

    public function isDeletable()
    {
        return Gate::allows('delete', $this)
            && (($this->assets_count ?? $this->assets()->count()) === 0)
            && ($this->deleted_at == '');
    }

    /**
     * Establishes the status label -> assets relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return Relation
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'status_id');
    }

    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Gets the status label type
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return string
     */
    public function getStatuslabelType()
    {
        if (($this->pending == '1') && ($this->archived == '0') && ($this->deployable == '0')) {
            return 'pending';
        } elseif (($this->pending == '0') && ($this->archived == '1') && ($this->deployable == '0')) {
            return 'archived';
        } elseif (($this->pending == '0') && ($this->archived == '0') && ($this->deployable == '0')) {
            return 'undeployable';
        }

        return 'deployable';
    }

    /**
     * Helper function to determine type attributes
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v1.0]
     *
     * @return string
     */
    public static function getStatuslabelTypesForDB($type)
    {
        $statustype['pending'] = 0;
        $statustype['deployable'] = 0;
        $statustype['archived'] = 0;

        if ($type == 'pending') {
            $statustype['pending'] = 1;
            $statustype['deployable'] = 0;
            $statustype['archived'] = 0;
        } elseif ($type == 'deployable') {
            $statustype['pending'] = 0;
            $statustype['deployable'] = 1;
            $statustype['archived'] = 0;
        } elseif ($type == 'archived') {
            $statustype['pending'] = 0;
            $statustype['deployable'] = 0;
            $statustype['archived'] = 1;
        }

        return $statustype;
    }

    public function newEloquentBuilder($query): StatuslabelQueryBuilder
    {
        return new StatuslabelQueryBuilder($query);
    }
}
