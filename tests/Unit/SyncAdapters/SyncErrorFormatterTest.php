<?php

use App\SyncAdapters\SyncErrorFormatter;

test('generic exception summary includes the message', function () {
    $exception = new \RuntimeException('OpenSSL unable to sign data');

    $summary = SyncErrorFormatter::summary($exception);

    expect($summary)->toBe('RuntimeException: OpenSSL unable to sign data');
});

test('generic exception summary falls back to class when message is empty', function () {
    $exception = new \RuntimeException('');

    $summary = SyncErrorFormatter::summary($exception);

    expect($summary)->toBe('RuntimeException');
});

test('generic exception summary truncates long messages', function () {
    $longMessage = str_repeat('a', 500);
    $exception = new \RuntimeException($longMessage);

    $summary = SyncErrorFormatter::summary($exception);

    expect($summary)->toBe('RuntimeException: '.str_repeat('a', 200).'…');
});
