<?php

namespace Tests\Feature\Notifications\Email;

use App\Listeners\ForceBase64MailEncoding;
use Illuminate\Mail\Events\MessageSending;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\TextPart;
use Tests\TestCase;

#[Group('notifications')]
class ForceBase64MailEncodingTest extends TestCase
{
    /*
     * Regression test for issue #19998: 
     */
    public function test_html_and_text_parts_use_base64_encoding(): void
    {
        $email = (new Email)
            ->from('from@example.test')
            ->to('to@example.test')
            ->subject('t')
            ->html('<p>Hello <a href="https://snipe.school.k12.blah.us/hardware/1">asset</a></p>')
            ->text("Hello https://snipe.school.k12.blah.us/hardware/1\n");

        (new ForceBase64MailEncoding)->handle(new MessageSending($email));

        $raw = $email->toString();

        preg_match_all('/Content-Transfer-Encoding: (\S+)/', $raw, $m);
        $encodings = array_filter($m[1], fn($e) => in_array($e, ['base64', 'quoted-printable', '8bit', '7bit'], true));

        $this->assertNotEmpty($encodings, 'Expected at least one Content-Transfer-Encoding header on text/html parts.');
        foreach ($encodings as $encoding) {
            $this->assertSame('base64', $encoding, 'All text parts must be base64, found: ' . $encoding);
        }
    }

    public function test_no_url_host_is_split_across_a_soft_wrap(): void
    {
        $longLink = str_repeat('X', 50) . ' https://snipe.school.k12.blah.us/hardware/' . str_repeat('9', 20);
        $email = (new Email)
            ->from('from@example.test')
            ->to('to@example.test')
            ->subject('t')
            ->html('<p>' . e($longLink) . '</p>')
            ->text($longLink);

        (new ForceBase64MailEncoding)->handle(new MessageSending($email));

        $raw = $email->toString();

        // Base64 output has no soft-wrap continuation. It only emits "=" as
        // terminal padding. Confirm no QP soft-wrap artifact sits inside.
        $this->assertStringNotContainsString('=3D', $raw, 'Encoded body should contain no QP escapes (=3D).');
        $this->assertDoesNotMatchRegularExpression(
            '/=\r?\n[A-Za-z0-9][a-z0-9]/',
            $raw,
            'No QP soft-wrap continuations should appear in a base64-encoded body.',
        );
    }

    public function test_url_survives_round_trip_through_base64(): void
    {
        $host = 'snipe.school.k12.blah.us';
        $html = '<a href="https://' . $host . '/hardware/42">asset</a>';

        $email = (new Email)
            ->from('from@example.test')
            ->to('to@example.test')
            ->subject('t')
            ->html($html);

        (new ForceBase64MailEncoding)->handle(new MessageSending($email));

        $raw = $email->toString();

        $this->assertTrue(
            (bool) preg_match('/Content-Transfer-Encoding: base64\r?\n\r?\n([A-Za-z0-9+\/\r\n=]+?)(?=\r?\n--|$)/s', $raw, $m),
            'Expected to find a base64-encoded body section.',
        );

        $decoded = base64_decode($m[1]);
        $this->assertStringContainsString($host, $decoded);
        $this->assertStringNotContainsString('snipe..', $decoded);
        $this->assertStringNotContainsString('..school', $decoded);
    }

    public function test_data_part_attachments_are_not_touched(): void
    {
        /*
         * DataPart (attachments / inline images) extends TextPart but holds
         * binary payloads already encoded as base64. The listener must skip
         * them so we don't double-encode or disturb inline-image CIDs.
         */
        $email = (new Email)
            ->from('from@example.test')
            ->to('to@example.test')
            ->subject('t')
            ->html('<p>has attachment</p>')
            ->attach('binary-payload', 'file.bin', 'application/octet-stream');

        (new ForceBase64MailEncoding)->handle(new MessageSending($email));

        $textParts = $this->collectTextParts($email->getBody());

        $sawHtml = false;
        $sawAttachment = false;
        foreach ($textParts as $part) {
            if ($part instanceof DataPart) {
                $sawAttachment = true;

                continue;
            }
            if ($part->getMediaType() === 'text' && $part->getMediaSubtype() === 'html') {
                $sawHtml = true;
                $header = $part->getPreparedHeaders()->get('Content-Transfer-Encoding');
                $this->assertNotNull($header);
                $this->assertSame('base64', $header->getBodyAsString());
            }
        }

        $this->assertTrue($sawHtml, 'Expected to find an html TextPart.');
        $this->assertTrue($sawAttachment, 'Expected to find a DataPart attachment.');
    }

    /**
     * @return list<TextPart>
     */
    private function collectTextParts($part): array
    {
        if ($part instanceof AbstractMultipartPart) {
            $out = [];
            foreach ($part->getParts() as $child) {
                $out = array_merge($out, $this->collectTextParts($child));
            }

            return $out;
        }

        return $part instanceof TextPart ? [$part] : [];
    }
}
