<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\TextPart;

/*
 * Quoted-printable soft-wraps at col 76 end up inside long
 * markdown-table URLs (issue #19998).
 *
 * A compliant MTA strips "=\n" per RFC 2045 §6.7, but some intermediates
 * (SafeLinks, older Exchange) rewrite that soft-break into a literal "." or similar joining
 * char, producing user-visible double-dots in wrapped links that end up breaking links.
 * Base64 has no soft-wrap at all so the URL stays normal no matter
 * what downstream mangling happens.
 */
class ForceBase64MailEncoding
{
    public function handle(MessageSending $event): void
    {
        $this->switchTextPartsToBase64($event->message->getBody());
    }

    private function switchTextPartsToBase64(AbstractPart $part): void
    {
        if ($part instanceof AbstractMultipartPart) {
            foreach ($part->getParts() as $child) {
                $this->switchTextPartsToBase64($child);
            }

            return;
        }

        // DataPart (attachments / inline images) already defaults to base64;
        // leave it untouched to avoid re-encoding binary payloads.
        if (! $part instanceof TextPart || $part instanceof DataPart) {
            return;
        }

        if ($part->getMediaType() !== 'text') {
            return;
        }

        // TextPart::$encoding is private and has no public setter. Reflection
        // is the only way to flip the content-transfer-encoding on an
        // already-constructed part without rebuilding the whole MIME tree
        // (which would otherwise require re-doing CID replacement, inline
        // image detection, and nested Multipart/Alternative/Related/Mixed
        // stuff that Symfony's Email::generateBody() does for us).
        $reflection = new \ReflectionProperty(TextPart::class, 'encoding');
        $reflection->setValue($part, 'base64');
    }
}
