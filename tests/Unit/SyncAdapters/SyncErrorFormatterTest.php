<?php

namespace Tests\Unit\SyncAdapters;

use App\SyncAdapters\SyncErrorFormatter;
use Tests\TestCase;

class SyncErrorFormatterTest extends TestCase
{
    public function test_generic_exception_summary_includes_the_message(): void
    {
        $exception = new \RuntimeException('OpenSSL unable to sign data');

        $summary = SyncErrorFormatter::summary($exception);

        $this->assertSame('RuntimeException: OpenSSL unable to sign data', $summary);
    }

    public function test_generic_exception_summary_falls_back_to_class_when_message_is_empty(): void
    {
        $exception = new \RuntimeException('');

        $summary = SyncErrorFormatter::summary($exception);

        $this->assertSame('RuntimeException', $summary);
    }

    public function test_generic_exception_summary_truncates_long_messages(): void
    {
        $longMessage = str_repeat('a', 500);
        $exception = new \RuntimeException($longMessage);

        $summary = SyncErrorFormatter::summary($exception);

        $this->assertSame('RuntimeException: ' . str_repeat('a', 200) . '…', $summary);
    }
}
