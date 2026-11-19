<?php

// the 'meatiest' of these tests will explicitly choose non-English as the language, because otherwise
// the fallback-logic (which is to fall-back to 'en-US') will be conflated in with the translation logic

// WARNING: If these translation strings are updated, these tests will start to fail. Update them as appropriate.

test('basic', function () {
    expect(trans('general.admin_tooltip', [], 'en-US'))->toEqual('This user has admin privileges');
});

test('portuguese', function () {
    expect(trans('general.accessory', [], 'pt-PT'))->toEqual('Acessório');
});

test('fallback', function () {
    expect(trans('general.admin_tooltip', [], 'xx-ZZ'))->toEqual('This user has admin privileges', 'Nonexistent locale should fall-back to en-US');
});

test('backup string', function () {
    expect(trans('backup::notifications.no_backups_info', [], 'nb-NO'))->toEqual('Ingen sikkerhetskopier ble gjort ennå', "Norwegian 'no backups info' message should be here");
});

test('backup fallback', function () {
    expect(trans('backup::notifications.no_backups_info', [], 'xx-ZZ'))->toEqual('No backups were made yet', "'no backups info' string should fallback to 'en'");
});

test('trans choice singular', function () {
    expect(trans_choice('general.countable.consumables', 1, [], 'pt-PT'))->toEqual('1 Consumível');
});

test('trans choice plural', function () {
    expect(trans_choice('general.countable.consumables', 2, [], 'pt-PT'))->toEqual('2 Consumíveis');
});

test('totally bogus key', function () {
    expect(trans('bogus_key', [], 'pt-PT'))->toEqual('bogus_key', 'Translating a completely bogus key should at least just return back that key');
});

test('replacements', function () {
    expect(trans('admin/users/general.assets_user', ['name' => 'Some Name Here'], 'pt-PT'))->toEqual('Artigos alocados a Some Name Here', 'Text should get replaced in translations when given');
});

test('nonlegacy backup locale', function () {
    // Spatie backup *usually* uses two-character locales, but pt-BR is an exception
    expect(trans('backup::notifications.exception_message', ['message' => 'MESSAGE'], 'pt_BR'))->toEqual('Mensagem de exceção: MESSAGE');
});

test('trans choice plural on informal locale', function () {
    // Regression coverage for GH #18260: Laravel's MessageSelector doesn't
    // know the `-if` informal-variant codes, so it used to fall through to
    // `default: return 0` and always pick the singular form. SnipeTranslator
    // strips the `-if` suffix before the plural-rule lookup so the base
    // language's rule kicks in.
    expect(trans_choice('general.months_plural', 1, [], 'de-if'))->toEqual('1 Monat');
    expect(trans_choice('general.months_plural', 24, [], 'de-if'))->toEqual('24 Monate');
});
