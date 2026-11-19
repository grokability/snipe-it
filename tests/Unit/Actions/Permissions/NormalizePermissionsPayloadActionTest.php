<?php

use App\Actions\Permissions\NormalizePermissionsPayloadAction;

test('it returns arrays unchanged', function () {
    $permissions = ['users.view' => '1', 'reports.view' => 0];

    $normalized = NormalizePermissionsPayloadAction::run($permissions);

    expect($normalized)->toBe($permissions);
});

test('it decodes valid json objects', function () {
    $normalized = NormalizePermissionsPayloadAction::run('{"users.view":"1","reports.view":"0"}');

    expect($normalized)->toBe(['users.view' => '1', 'reports.view' => '0']);
});

test('it casts std class payloads to arrays', function () {
    $permissions = (object) ['users.view' => '1'];

    $normalized = NormalizePermissionsPayloadAction::run($permissions);

    expect($normalized)->toBe(['users.view' => '1']);
});

test('it returns empty array for invalid json or non array values', function () {
    expect(NormalizePermissionsPayloadAction::run('{not-valid-json}'))->toBe([]);
    expect(NormalizePermissionsPayloadAction::run('null'))->toBe([]);
    expect(NormalizePermissionsPayloadAction::run('"users.view"'))->toBe([]);
    expect(NormalizePermissionsPayloadAction::run(null))->toBe([]);
    expect(NormalizePermissionsPayloadAction::run(123))->toBe([]);
    expect(NormalizePermissionsPayloadAction::run(true))->toBe([]);
});
