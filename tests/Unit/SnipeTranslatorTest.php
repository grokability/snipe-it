<?php

namespace Tests\Unit;

use Tests\TestCase;

class SnipeTranslatorTest extends TestCase
{
    // the 'meatiest' of these tests will explicitly choose non-English as the language, because otherwise
    // the fallback-logic (which is to fall-back to 'en-US') will be conflated in with the translation logic

    // WARNING: If these translation strings are updated, these tests will start to fail. Update them as appropriate.

    public function test_basic()
    {
        $this->assertEquals('This user has admin privileges', trans('general.admin_tooltip', [], 'en-US'));
    }

    public function test_portuguese()
    {
        $this->assertEquals('Acessório', trans('general.accessory', [], 'pt-PT'));
    }

    public function test_fallback()
    {
        $this->assertEquals(
            'This user has admin privileges',
            trans('general.admin_tooltip', [], 'xx-ZZ'),
            'Nonexistent locale should fall-back to en-US'
        );
    }

    public function test_backup_string()
    {
        $this->assertEquals(
            'Ingen sikkerhetskopier ble gjort ennå',
            trans('backup::notifications.no_backups_info', [], 'nb-NO'),
            "Norwegian 'no backups info' message should be here"
        );
    }

    public function test_backup_fallback()
    {
        $this->assertEquals(
            'No backups were made yet',
            trans('backup::notifications.no_backups_info', [], 'xx-ZZ'),
            "'no backups info' string should fallback to 'en'"
        );

    }

    public function test_trans_choice_singular()
    {
        $this->assertEquals(
            '1 Consumível',
            trans_choice('general.countable.consumables', 1, [], 'pt-PT')
        );
    }

    public function test_trans_choice_plural()
    {
        $this->assertEquals(
            '2 Consumíveis',
            trans_choice('general.countable.consumables', 2, [], 'pt-PT')
        );
    }

    public function test_totally_bogus_key()
    {
        $this->assertEquals(
            'bogus_key',
            trans('bogus_key', [], 'pt-PT'),
            'Translating a completely bogus key should at least just return back that key'
        );
    }

    public function test_replacements()
    {
        $this->assertEquals(
            'Artigos alocados a Some Name Here',
            trans('admin/users/general.assets_user', ['name' => 'Some Name Here'], 'pt-PT'),
            'Text should get replaced in translations when given'
        );
    }

    public function test_nonlegacy_backup_locale()
    {
        // Spatie backup *usually* uses two-character locales, but pt-BR is an exception
        $this->assertEquals(
            'Mensagem de exceção: MESSAGE',
            trans('backup::notifications.exception_message', ['message' => 'MESSAGE'], 'pt_BR')
        );
    }
}
