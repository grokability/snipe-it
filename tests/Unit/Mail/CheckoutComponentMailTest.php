<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckoutComponentMail;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutComponentMailTest extends TestCase
{
    #[Test]
    public function shows_location_of_asset_component_was_checked_out_to()
    {
        $han = User::factory()->create();

        $millenniumFalcon = Asset::factory()
            ->forLocation(['name' => 'Cloud City'])
            ->assignedToUser($han)
            ->create();

        $hyperdriveMotivator = Component::factory()
            ->forLocation(['name' => 'Mos Eisley'])
            ->create();

        $mail = new CheckoutComponentMail($hyperdriveMotivator, $millenniumFalcon, User::factory()->create(), null, null);

        $mail->assertSeeInOrderInText([trans('general.location'), 'Cloud City']);
        $mail->assertDontSeeInText('Mos Eisley');
    }

    #[Test]
    public function does_not_show_location_when_asset_has_no_location()
    {
        $han = User::factory()->create();

        $millenniumFalcon = Asset::factory()
            ->assignedToUser($han)
            ->create(['location_id' => null]);

        $hyperdriveMotivator = Component::factory()
            ->forLocation(['name' => 'Mos Eisley'])
            ->create();

        $mail = new CheckoutComponentMail($hyperdriveMotivator, $millenniumFalcon, User::factory()->create(), null, null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
