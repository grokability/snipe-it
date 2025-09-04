<?php

namespace Tests\Unit\BladeComponents;

use App\Models\User;
use Generator;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserFullNameTest extends TestCase
{
    public static function provider(): Generator
    {
        yield 'Renders link to user if they exist and the authenticated user can view them' => [
            function () {
                return [
                    'actor' => User::factory()->viewUsers()->create(),
                    'user' => User::factory()->create(['first_name' => 'Jim', 'last_name' => 'Bagg', 'display_name' => null]),
                    'assertions' => function ($rendered) {
                        Assert::assertStringContainsString('<a ', $rendered);
                        Assert::assertStringContainsString('Jim Bagg', $rendered);
                    },
                ];
            }
        ];

        yield 'Renders struck-through link to user if they are deleted and the authenticated user can view them' => [
            function () {
                return [
                    'actor' => User::factory()->viewUsers()->create(),
                    'user' => User::factory()->deleted()->create(['first_name' => 'Jim', 'last_name' => 'Bagg', 'display_name' => 'Jim Baggins']),
                    'assertions' => function ($rendered) {
                        Assert::assertStringContainsString('<s><a ', $rendered);
                        Assert::assertStringContainsString('Jim Bagg', $rendered);
                    },
                ];
            }
        ];

        yield 'Renders name without link if the authenticated user cannot view them' => [
            function () {
                return [
                    'actor' => User::factory()->create(),
                    'user' => User::factory()->create(['first_name' => 'Jim', 'last_name' => 'Bagg',  'display_name' => 'Jim Bagg']),
                    'assertions' => function ($rendered) {
                        Assert::assertStringContainsString('<span>Jim Bagg', $rendered);
                        Assert::assertStringNotContainsString('<a ', $rendered);
                    },
                ];
            }
        ];

        yield 'Renders struck-through name without link if the user is deleted and the authenticated user cannot view them' => [
            function () {
                return [
                    'actor' => User::factory()->create(),
                    'user' => User::factory()->deleted()->create(['first_name' => 'Jim', 'last_name' => 'Bagg',  'display_name' => 'Jim Bagg']),
                    'assertions' => function ($rendered) {
                        Assert::assertStringContainsString('<s><span>Jim Bagg', $rendered);
                    },
                ];
            }
        ];

        yield 'Renders nothing if the provided user is null' => [
            function () {
                return [
                    'actor' => User::factory()->create(),
                    'user' => null,
                    'assertions' => function ($rendered) {
                        Assert::assertEmpty($rendered);
                    },
                ];
            }
        ];
    }

    #[DataProvider('provider')]
    public function testComponent($provided)
    {
        ['actor' => $actor, 'user' => $user, 'assertions' => $assertions] = $provided();

         // $user->displayName();

        // \Log::error($user->toArray());

        $this->actingAs($actor);

        $renderedTemplateString = View::make('blade.full-user-name', ['user' => $user])->render();

        $assertions($renderedTemplateString);
    }
}
