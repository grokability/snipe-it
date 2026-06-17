<?php

namespace Tests\Unit\Models;

use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class ComponentLogicTest extends TestCase
{
    public function test_counts_and_percent_for_fresh_component(): void
    {
        $component = Component::factory()->create(['qty' => 10]);

        $this->assertSame(0, $component->numCheckedOut());
        $this->assertSame(10, $component->numRemaining());
        $this->assertEquals(100, $component->percentRemaining());
    }

    public function test_require_acceptance_and_checkin_email_return_bool(): void
    {
        $component = Component::factory()->create();

        $this->assertIsBool((bool) $component->requireAcceptance());
        $this->assertIsBool((bool) $component->checkin_email());
    }

    public function test_is_deletable(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $component = Component::factory()->create();

        $this->assertIsBool($component->isDeletable());
    }
}
