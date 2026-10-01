<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckoutConsumableMail;
use App\Models\Consumable;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutConsumableMailTest extends TestCase
{
    #[Test]
    public function shows_location_of_user_consumable_was_checked_out_to()
    {
        $luke = User::factory()
            ->forLocation(['name' => 'Tatooine'])
            ->create();

        $rationPack = Consumable::factory()->create();

        $mail = new CheckoutConsumableMail($rationPack, $luke, User::factory()->create(), null, null);

        $mail->assertSeeInOrderInText([trans('general.location'), 'Tatooine']);
    }

    #[Test]
    public function does_not_show_location_when_user_has_no_location()
    {
        $han = User::factory()->create(['location_id' => null]);
        $rationPack = Consumable::factory()->create();

        $mail = new CheckoutConsumableMail($rationPack, $han, User::factory()->create(), null, null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
