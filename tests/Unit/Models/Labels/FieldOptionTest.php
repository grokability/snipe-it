<?php

namespace Tests\Unit\Models\Labels;

use App\Models\Asset;
use App\Models\Labels\FieldOption;
use App\Models\User;
use Tests\TestCase;

class FieldOptionTest extends TestCase
{
    public function testItDisplaysAssignedToProperly()
    {
        // "assignedTo" is a "special" value that can be used in the new label engine
        $fieldOption = FieldOption::fromString('Assigned To=assignedTo');

        $asset = Asset::factory()
            ->assignedToUser(User::factory()->create(['first_name' => 'Luke', 'last_name' => 'Skywalker']))
            ->create();

        $this->assertEquals('Luke Skywalker', $fieldOption->getValue($asset));
        // If the "assignedTo" relationship was eager loaded then the way to get the
        // relationship changes from $asset->assignedTo to $asset->assigned.
        $this->assertEquals('Luke Skywalker', $fieldOption->getValue(Asset::with('assignedTo')->find($asset->id)));
    }
}
