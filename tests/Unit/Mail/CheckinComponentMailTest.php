<?php

namespace Tests\Unit\Mail;

use App\Mail\CheckinComponentMail;
use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckinComponentMailTest extends TestCase
{
    #[Test]
    public function shows_component_storage_location_instead_of_assets_location()
    {
        $han = User::factory()->create();

        $millenniumFalcon = Asset::factory()
            ->forLocation(['name' => 'Cloud City'])
            ->assignedToUser($han)
            ->create();

        $hyperdriveMotivator = Component::factory()
            ->forLocation(['name' => 'Mos Eisley'])
            ->create();

        $mail = new CheckinComponentMail($hyperdriveMotivator, $millenniumFalcon, User::factory()->create(), null);

        $mail->assertSeeInOrderInText([trans('general.location'), 'Mos Eisley']);
        $mail->assertDontSeeInText('Cloud City');
    }

    #[Test]
    public function does_not_show_location_when_component_has_no_location()
    {
        $han = User::factory()->create();

        $millenniumFalcon = Asset::factory()
            ->forLocation(['name' => 'Cloud City'])
            ->assignedToUser($han)
            ->create();

        $hyperdriveMotivator = Component::factory()->create(['location_id' => null]);

        $mail = new CheckinComponentMail($hyperdriveMotivator, $millenniumFalcon, User::factory()->create(), null);

        $mail->assertDontSeeInText(trans('general.location'));
    }
}
