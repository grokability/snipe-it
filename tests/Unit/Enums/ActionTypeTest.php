<?php

namespace Tests\Unit\Enums;

use App\Enums\ActionType;
use Tests\TestCase;

class ActionTypeTest extends TestCase
{
    public function test_reacceptance_requested_case_has_the_expected_value(): void
    {
        $this->assertSame('reacceptance requested', ActionType::ReacceptanceRequested->value);
    }

    public function test_reacceptance_requested_translation_key_resolves(): void
    {
        // The presenter maps the action_type to general.<value-with-underscores>.
        $key = 'general.'.str_replace(' ', '_', ActionType::ReacceptanceRequested->value);

        $this->assertSame('requested re-acceptance', trans($key));
    }
}
