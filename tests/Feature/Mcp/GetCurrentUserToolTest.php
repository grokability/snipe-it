<?php

use App\Mcp\Tools\GetCurrentUserTool;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function getCurrentUserTool(array $args = []): ResponseFactory
{
    return (new GetCurrentUserTool)->handle(new Request($args));
}

test('returns current user info', function () {
    $user = auth()->user();

    $content = getCurrentUserTool()->getStructuredContent();

    expect($content['id'])->toEqual($user->id);
    expect($content['username'])->toEqual($user->username);
});

test('response includes expected fields', function () {
    $content = getCurrentUserTool()->getStructuredContent();

    foreach (['id', 'username', 'first_name', 'last_name', 'email', 'company', 'department', 'location', 'employee_num', 'title', 'phone', 'activated'] as $field) {
        expect($content)->toHaveKey($field);
    }
});
