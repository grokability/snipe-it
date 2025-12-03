<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\DataAwareRule;

class UniqueUndeleted implements ValidationRule, ValidatorAwareRule
{
    protected ?Validator $validator = null;
    protected array $columns = [];
    protected $data = [];

    public function __construct(
        public string  $table,
        string         ...$columns,
    )
    {
        $this->columns = $columns;
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;
        $this->data = $validator->getData();
        //TODO - can we somehow grab the ID of the route-model-bound object, and omit its ID?
        // to do that, we'd have to know _which_ parameter in the validator is actually the R-M-B'ed
        // parameter. Or maybe we just change the function signature to let you specify it.

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)->whereNull('deleted_at');
        $query->where($this->columns[0], '=', $value); //the first column to check
        $translation_string = 'validation.unique_undeleted'; //the normal validation string for a single-column check
        foreach (array_slice($this->columns, 1) as $column) {
            $translation_string = 'validation.two_column_unique_undeleted';
            $query->where($column, '=', $this->data[$column]);
        }

        if ($query->count() > 0) {
            $fail($translation_string)->translate();
        }
    }
}
