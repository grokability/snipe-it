<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckinAccessoryMail;
use App\Models\Accessory;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckinAccessoryMailTest extends TestCase
{
    #[Test]
    public function shows_accessory_storage_location_instead_of_users_location()
    {
        $luke = User::factory()
            ->forLocation(['name' => 'Tatooine'])
            ->create();

        $lightsaber = Accessory::factory()
            ->forLocation(['name' => 'Jedi Temple'])
            ->create();

        $mail = new CheckinAccessoryMail($lightsaber, $luke, User::factory()->create(), null);

        $mail->assertSeeInOrderInText([trans('general.location'), 'Jedi Temple']);
        $mail->assertDontSeeInText('Tatooine');
    }

    #[Test]
    public function does_not_show_location_when_accessory_has_no_location()
    {
        $luke = User::factory()
            ->forLocation(['name' => 'Tatooine'])
            ->create();

        $lightsaber = Accessory::factory()->create(['location_id' => null]);

        $mail = new CheckinAccessoryMail($lightsaber, $luke, User::factory()->create(), null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
