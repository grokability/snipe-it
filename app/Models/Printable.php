<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Printable HTML templates.
 *
 * Printables are HTML templates that can be assigned to asset categories.
 * When generating a printable, asset-specific variables (e.g. {asset_tag},
 * {model_name}) are substituted with real values and the resulting HTML
 * document is presented in a browser-friendly, print-ready view.
 */
class Printable extends SnipeModel
{
    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;
    use Searchable;

    protected $table = 'printables';

    protected $fillable = [
        'name',
        'content',
        'created_by',
    ];

    /**
     * Validation rules
     *
     * @var array<string, string|array<string>>
     */
    public $rules = [
        'name'    => 'required|min:1|max:255',
        'content' => 'required',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array<string>
     */
    protected $searchableAttributes = [
        'name',
        'created_at',
    ];

    /**
     * The relations and their attributes that should be included when searching.
     *
     * @var array<string, array<string>>
     */
    protected $searchableRelations = [
        'creator' => ['first_name', 'last_name', 'display_name'],
    ];

    /**
     * Establishes the printable → categories relationship.
     *
     * @return BelongsToMany<Category>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_printable');
    }

    /**
     * Establishes the printable → creator relationship.
     *
     * @return BelongsTo<User, Printable>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
