<?php

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function handle(array $args = []): ResponseFactory
{
    return handle(new Request($args));
}

test('returns current user info', function () {
    $user = auth()->user();

    $content = handle()->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['username'])->toEqual($user->username);
});

test('response includes expected fields', function () {
    $content = handle()->getStructuredContent();

    foreach (['id', 'username', 'first_name', 'last_name', 'email', 'company', 'department', 'location', 'employee_num', 'title', 'phone', 'activated'] as $field) {
        expect($content)->toHaveKey($field);
    }
});
