<?php

namespace Tests\Unit\Actions\Permissions;

use App\Actions\Permissions\NormalizePermissionsPayloadAction;
use Tests\TestCase;

class NormalizePermissionsPayloadActionTest extends TestCase
{
    public function test_it_returns_arrays_unchanged(): void
    {
        $permissions = ['users.view' => '1', 'reports.view' => 0];

        $normalized = NormalizePermissionsPayloadAction::run($permissions);

        $this->assertSame($permissions, $normalized);
    }

    public function test_it_decodes_valid_json_objects(): void
    {
        $normalized = NormalizePermissionsPayloadAction::run('{"users.view":"1","reports.view":"0"}');

        $this->assertSame(['users.view' => '1', 'reports.view' => '0'], $normalized);
    }

    public function test_it_casts_std_class_payloads_to_arrays(): void
    {
        $permissions = (object) ['users.view' => '1'];

        $normalized = NormalizePermissionsPayloadAction::run($permissions);

        $this->assertSame(['users.view' => '1'], $normalized);
    }

    public function test_it_returns_empty_array_for_invalid_json_or_non_array_values(): void
    {
        $this->assertSame([], NormalizePermissionsPayloadAction::run('{not-valid-json}'));
        $this->assertSame([], NormalizePermissionsPayloadAction::run('null'));
        $this->assertSame([], NormalizePermissionsPayloadAction::run('"users.view"'));
        $this->assertSame([], NormalizePermissionsPayloadAction::run(null));
        $this->assertSame([], NormalizePermissionsPayloadAction::run(123));
        $this->assertSame([], NormalizePermissionsPayloadAction::run(true));
    }
}
