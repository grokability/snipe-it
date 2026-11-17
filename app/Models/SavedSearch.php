<?php

namespace App\Models;

use App\Models\Builders\SavedSearchQueryBuilder;
use App\Rules\EloquentModelClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Watson\Validating\ValidatingTrait;

/**
 * Named filter-preset for a listing page's advanced-search. Each
 * record stores a URL query string captured from the advanced-search
 * modal. Private to the creator for v1 (no sharing/default yet).
 *
 * @mixin IdeHelperSavedSearch
 */
class SavedSearch extends Model
{
    use HasFactory;
    use ValidatingTrait;

    protected $table = 'saved_searches';

    protected $fillable = [
        'created_by',
        'table_name',
        'model',
        'name',
        'query_string',
    ];

    protected $rules = [
        'created_by' => 'required|integer',
        'table_name' => 'required|string|max:191',
        'model' => 'nullable|string|max:191',
        'name' => 'required|string|max:191',
        'query_string' => 'required|string|max:4096',
    ];

    public function getRules(): array
    {
        $rules = $this->rules;
        $rules['model'] = ['nullable', 'string', 'max:191', new EloquentModelClass];

        $createdBy = $this->created_by ?? 'NULL';
        $tableName = $this->table_name ?? 'NULL';

        $uniqueTail = ",NULL,id,created_by,{$createdBy},table_name,{$tableName}";
        $rules['name'] .= '|unique:saved_searches,name'.$uniqueTail;
        $rules['query_string'] .= '|unique:saved_searches,query_string'.$uniqueTail;

        return $rules;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function newEloquentBuilder($query): SavedSearchQueryBuilder
    {
        return new SavedSearchQueryBuilder($query);
    }
}
