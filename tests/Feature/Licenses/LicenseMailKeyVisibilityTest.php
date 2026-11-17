<?php

namespace Tests\Feature\Licenses;

use App\Mail\CheckinLicenseMail;
use App\Mail\CheckoutLicenseMail;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Render-time coverage for the license checkin / checkout mail
 * bodies. The Key row only renders when the recipient user holds
 * licenses.keys. Omitted entirely for recipients the admin has
 * deliberately denied, matching the policy that an explicit deny
 * beats implicit grants from create / edit. These tests pin the
 * blade conditional so a future redesign of the mail layout cannot
 * silently reintroduce the raw key for a denied recipient.
 */
class LicenseMailKeyVisibilityTest extends TestCase
{
    public function test_checkin_mail_body_includes_raw_key_for_recipient_with_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'CHECKIN-MAIL-RAW-111']);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $recipient = User::factory()->viewLicenses()->viewKeysLicenses()->create();
        $admin = User::factory()->admin()->create();

        $html = (new CheckinLicenseMail($seat, $recipient, $admin, ''))->render();

        $this->assertStringContainsString('CHECKIN-MAIL-RAW-111', $html);
    }

    public function test_checkin_mail_body_omits_key_row_for_recipient_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'CHECKIN-MAIL-SECRET-222']);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $recipient = User::factory()->viewLicenses()->create();
        $admin = User::factory()->admin()->create();

        $html = (new CheckinLicenseMail($seat, $recipient, $admin, ''))->render();

        $this->assertStringNotContainsString('CHECKIN-MAIL-SECRET-222', $html);
        $this->assertStringNotContainsString(License::PRODUCT_KEY_MASK, $html);
    }

    public function test_checkin_mail_body_omits_key_row_when_recipient_has_create_or_edit_but_explicit_deny_on_keys(): void
    {
        // Mirrors the real-world shape caught during the GHSA
        // review. A recipient granted licenses.create and
        // licenses.edit still has keys explicitly denied, and the
        // mail must respect that deny.
        $license = License::factory()->create(['serial' => 'CHECKIN-MAIL-DENY-333']);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $recipient = User::factory()
            ->viewLicenses()
            ->state(fn ($state) => [
                'permissions' => json_encode(array_merge(
                    json_decode($state['permissions'], true),
                    [
                        'licenses.create' => '1',
                        'licenses.edit' => '1',
                        'licenses.keys' => '-1',
                    ],
                )),
            ])
            ->create();
        $admin = User::factory()->admin()->create();

        $html = (new CheckinLicenseMail($seat, $recipient, $admin, ''))->render();

        $this->assertStringNotContainsString('CHECKIN-MAIL-DENY-333', $html);
    }

    public function test_checkout_mail_body_includes_raw_key_for_recipient_with_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'CHECKOUT-MAIL-RAW-444']);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $recipient = User::factory()->viewLicenses()->viewKeysLicenses()->create();
        $admin = User::factory()->admin()->create();

        $html = (new CheckoutLicenseMail($seat, $recipient, $admin, null, '', true))->render();

        $this->assertStringContainsString('CHECKOUT-MAIL-RAW-444', $html);
    }

    public function test_checkout_mail_body_omits_key_row_for_recipient_without_viewkeys(): void
    {
        $license = License::factory()->create(['serial' => 'CHECKOUT-MAIL-SECRET-555']);
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $recipient = User::factory()->viewLicenses()->create();
        $admin = User::factory()->admin()->create();

        $html = (new CheckoutLicenseMail($seat, $recipient, $admin, null, '', true))->render();

        $this->assertStringNotContainsString('CHECKOUT-MAIL-SECRET-555', $html);
        $this->assertStringNotContainsString(License::PRODUCT_KEY_MASK, $html);
    }

    public function test_checkout_mail_greeting_still_renders_recipient_display_name(): void
    {
        // Regression guard. The previous CheckoutLicenseMail
        // constructor stringified $target to display_name so the
        // greeting could do '# Hello '.$target.','. Preserving the
        // User instance for the Key gate requires the blade to read
        // $target->display_name instead. This test pins that the
        // greeting still renders without a type coercion error.
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create(['license_id' => $license->id]);
        $recipient = User::factory()->create([
            'first_name' => 'Mail',
            'last_name' => 'Recipient',
        ]);
        $admin = User::factory()->admin()->create();

        $html = (new CheckoutLicenseMail($seat, $recipient, $admin, null, '', true))->render();

        $this->assertStringContainsString($recipient->display_name, $html);
    }
}
