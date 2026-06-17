<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Helper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelperRedirectTest extends TestCase
{
    public static function tableProvider(): array
    {
        return [
            ['Assets'], ['Users'], ['Licenses'], ['Accessories'], ['Components'], ['Consumables'],
        ];
    }

    #[DataProvider('tableProvider')]
    public function test_redirect_option_index(string $table): void
    {
        $request = new Request(['redirect_option' => 'index']);

        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($request, 1, $table));
    }

    #[DataProvider('tableProvider')]
    public function test_redirect_option_item(string $table): void
    {
        $request = new Request(['redirect_option' => 'item']);

        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($request, 1, $table));
    }

    public function test_redirect_option_target_user(): void
    {
        session(['checkout_to_type' => 'user']);
        $request = new Request(['redirect_option' => 'target', 'assigned_user' => 1]);

        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($request, 1, 'Assets'));
    }

    public function test_redirect_option_target_location_and_asset(): void
    {
        session(['checkout_to_type' => 'location']);
        $req1 = new Request(['redirect_option' => 'target', 'assigned_location' => 1]);
        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($req1, 1, 'Assets'));

        session(['checkout_to_type' => 'asset']);
        $req2 = new Request(['redirect_option' => 'target', 'assigned_asset' => 1]);
        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($req2, 1, 'Assets'));
    }

    public function test_redirect_option_other_redirect_audit(): void
    {
        session(['other_redirect' => 'audit']);
        $request = new Request(['redirect_option' => 'other_redirect']);

        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($request, 1, 'Assets'));
    }

    public function test_redirect_option_back_and_default(): void
    {
        $back = new Request(['redirect_option' => 'back']);
        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($back, 1, 'Assets'));

        $default = new Request([]);
        $this->assertInstanceOf(RedirectResponse::class, Helper::getRedirectOption($default, 1, 'Assets'));
    }
}
