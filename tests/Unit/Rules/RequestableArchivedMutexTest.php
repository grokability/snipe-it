<?php

namespace Tests\Unit\Rules;

use App\Rules\RequestableArchivedMutex;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * The rule is wired on BOTH requestable and archived. Whichever side
 * is being toggled fires the conflict. Verify every corner of the
 * truth table rather than trusting prohibited_if-shaped semantics,
 * which treat `0` as "present and not empty" and mis-fire on
 * single-flag changes.
 */
class RequestableArchivedMutexTest extends TestCase
{
    private function validate(array $data): array
    {
        $v = Validator::make($data, [
            'requestable' => ['nullable', 'boolean', new RequestableArchivedMutex],
            'archived' => ['nullable', 'boolean', new RequestableArchivedMutex],
        ]);

        return $v->errors()->toArray();
    }

    public function test_both_truthy_fires_on_both_fields(): void
    {
        $errors = $this->validate(['requestable' => 1, 'archived' => 1]);

        $this->assertArrayHasKey('requestable', $errors);
        $this->assertArrayHasKey('archived', $errors);
        $this->assertSame(trans('general.requestable_archived_conflict'), $errors['requestable'][0]);
    }

    public function test_only_requestable_truthy_passes(): void
    {
        $this->assertEmpty($this->validate(['requestable' => 1, 'archived' => 0]));
    }

    public function test_only_archived_truthy_passes(): void
    {
        $this->assertEmpty($this->validate(['requestable' => 0, 'archived' => 1]));
    }

    public function test_both_falsy_passes(): void
    {
        $this->assertEmpty($this->validate(['requestable' => 0, 'archived' => 0]));
    }

    public function test_missing_sibling_passes(): void
    {
        $this->assertEmpty($this->validate(['archived' => 1]));
        $this->assertEmpty($this->validate(['requestable' => 1]));
    }
}
