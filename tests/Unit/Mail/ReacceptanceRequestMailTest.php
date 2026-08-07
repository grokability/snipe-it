<?php

namespace Tests\Unit\Mail;

use App\Mail\ReacceptanceRequestMail;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Tests\TestCase;

class ReacceptanceRequestMailTest extends TestCase
{
    public function test_subject_uses_the_reacceptance_wording_when_single_item(): void
    {
        $mail = new ReacceptanceRequestMail(User::factory()->create(), CheckoutAcceptance::factory()->count(1)->create());

        $mail->assertHasSubject(trans_choice('mail.reacceptance_required', 1));
    }

    public function test_subject_uses_the_reacceptance_wording_when_multiple_items(): void
    {
        $mail = new ReacceptanceRequestMail(User::factory()->create(), collect(CheckoutAcceptance::factory()->count(2)->create()));

        $mail->assertHasSubject(trans_choice('mail.reacceptance_required', 3));
    }

    public function test_body_uses_correct_wording_when_single_item(): void
    {
        $user = User::factory()->create();

        $acceptances = CheckoutAcceptance::factory()->count(1)->create(['assigned_to_id' => $user->id]);

        $mail = new ReacceptanceRequestMail($user, $acceptances);

        $mail->assertSeeInText(trans_choice('mail.reacceptance_body', 1));
    }

    public function test_body_uses_correct_wording_when_multiple_items(): void
    {
        $user = User::factory()->create();

        $acceptances = CheckoutAcceptance::factory()->count(3)->create(['assigned_to_id' => $user->id]);

        $mail = new ReacceptanceRequestMail($user, $acceptances);

        $mail->assertSeeInText(trans_choice('mail.reacceptance_body', 3));
    }

    public function test_a_single_acceptance_links_to_the_item_signature_page(): void
    {
        $acceptances = CheckoutAcceptance::factory()->count(1)->create();

        $mail = new ReacceptanceRequestMail(User::factory()->create(), $acceptances);

        $mail->assertSeeInHtml(route('account.accept.item', $acceptances->first()));
    }

    public function test_multiple_acceptances_link_to_the_acceptance_index(): void
    {
        $acceptances = CheckoutAcceptance::factory()->count(2)->create();

        $mail = new ReacceptanceRequestMail(User::factory()->create(), $acceptances);

        $mail->assertSeeInHtml(route('account.accept'));
        $mail->assertDontSeeInHtml(route('account.accept.item', $acceptances->first()));
    }
}
