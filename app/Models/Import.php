<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperImport
 */
class Import extends Model
{
    use HasFactory;

    protected $casts = [
        'header_row' => 'array',
        'first_row' => 'array',
        'field_map' => 'json',
    ];

    /**
     * Establishes the license -> admin user relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     *
     * @since  [v2.0]
     *
     * @return Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Shared mutex key for an in-flight import of this specific file. Scoped by
     * file id so different files can be processed in parallel: admin A
     * importing accessories.csv does not block admin B importing
     * assets-batch-2.csv. Same file can't be processed concurrently.
     */
    public function lockKey(): string
    {
        return 'import:'.$this->id.':running';
    }
}
