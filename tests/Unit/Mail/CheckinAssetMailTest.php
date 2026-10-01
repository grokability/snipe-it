<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckinAssetMail;
use App\Models\Asset;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckinAssetMailTest extends TestCase
{
    #[Test]
    public function shows_location_asset_landed_at_instead_of_users_location()
    {
        $luke = User::factory()
            ->forLocation(['name' => 'Tatooine'])
            ->create();

        $r2d2 = Asset::factory()
            ->forLocation(['name' => 'Dagobah'])
            ->create();

        $mail = new CheckinAssetMail($r2d2, $luke, User::factory()->create(), null);

        $mail->assertSeeInOrderInText([trans('general.location'), 'Dagobah']);
        $mail->assertDontSeeInText('Tatooine');
    }

    #[Test]
    public function does_not_show_location_when_asset_has_no_location()
    {
        $luke = User::factory()
            ->forLocation(['name' => 'Tatooine'])
            ->create();
        $r2d2 = Asset::factory()->create(['location_id' => null]);

        $mail = new CheckinAssetMail($r2d2, $luke, User::factory()->create(), null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
