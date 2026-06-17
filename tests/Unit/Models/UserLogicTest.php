<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserLogicTest extends TestCase
{
    public static function nameFormatProvider(): array
    {
        return [
            ['filastname'],
            ['firstname.lastname'],
            ['lastnamefirstinitial'],
            ['firstintial.lastname'],
            ['firstname_lastname'],
            ['firstname'],
            ['lastname'],
            ['firstinitial.lastname'],
            ['lastname_firstinitial'],
            ['lastname.firstinitial'],
            ['firstnamelastname'],
            ['firstnamelastinitial'],
            ['lastname.firstname'],
        ];
    }

    #[DataProvider('nameFormatProvider')]
    public function test_generate_formatted_name_from_full_name(string $format): void
    {
        $result = User::generateFormattedNameFromFullName('Jane Doe', $format);

        $this->assertEquals('Jane', $result['first_name']);
        $this->assertEquals('Doe', $result['last_name']);
        $this->assertNotEmpty($result['username']);
        $this->assertEquals(strtolower($result['username']), $result['username']);
    }

    public function test_generate_formatted_name_with_single_name(): void
    {
        $result = User::generateFormattedNameFromFullName('Cher');

        $this->assertEquals('Cher', $result['first_name']);
        $this->assertEquals('', $result['last_name']);
        $this->assertEquals('cher', $result['username']);
    }

    public function test_generate_email_from_full_name(): void
    {
        $this->assertIsString(User::generateEmailFromFullName('Jane Doe'));
    }

    public function test_full_name_and_display_name_attributes(): void
    {
        $user = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe']);

        $this->assertEquals('Jane Doe', $user->getFullNameAttribute());
        $this->assertStringContainsString('Jane', $user->display_name);
    }

    public function test_role_helpers(): void
    {
        $super = User::factory()->superuser()->create();
        $regular = User::factory()->create();

        $this->assertTrue($super->isSuperUser());
        $this->assertFalse($regular->isSuperUser());
        $this->assertIsBool($regular->isAdmin());
    }

    public function test_is_activated_and_deletable(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $user = User::factory()->create(['activated' => true]);

        $this->assertTrue($user->isActivated());
        $this->assertIsBool($user->isDeletable());
    }

    public function test_can_edit_profile(): void
    {
        $user = User::factory()->create();

        $this->assertIsBool($user->canEditProfile());
    }

    public function test_no_password(): void
    {
        $user = User::factory()->create();

        $this->assertIsString($user->noPassword());
    }

    public function test_has_access_and_permissions_helpers(): void
    {
        $super = User::factory()->superuser()->create();

        $this->assertTrue($super->hasAccess('admin'));
        $this->assertIsBool($super->hasIndividualPermissions());
        $this->assertIsArray($super->decodePermissions() ?: []);
    }

    public function test_two_factor_helpers(): void
    {
        $user = User::factory()->create();

        $this->assertIsBool((bool) $user->two_factor_active());
    }

    public function test_avatar_is_external(): void
    {
        $user = User::factory()->create(['avatar' => 'https://example.com/a.png']);

        $this->assertTrue($user->isAvatarExternal());
    }
}
