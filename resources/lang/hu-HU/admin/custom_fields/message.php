<?php

return [

    'field' => [
        'invalid' => 'A rekordmező nem létezik.',
        'already_added' => 'Mező már hozzáadva',
        'none_selected' => 'Nincs mező kiválasztva',

        'create' => [
            'error' => 'A mező nem lett létrehozva, próbálkozz újra.',
            'success' => 'Mező sikeresen létrehozva.',
            'assoc_success' => 'Mező sikeresen hozzáadva a mezőcsoporthoz.',
        ],

        'update' => [
            'error' => 'A mező nem lett frissítve, próbálkozz újra',
            'success' => 'A mező sikeresen frissítve.',
        ],

        'delete' => [
            'confirm' => 'Biztosan törölni szeretné ezt a mezőt?',
            'error' => 'Egy probléma támadt a mező törlése közben. Próbálkozz újra.',
            'success' => 'A mező sikeresen törölve.',
            'in_use' => 'A mező még használatban van.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'A Fieldset nem létezik',

        'create' => [
            'error' => 'A mezőcsoport nem lett létrehozva, próbálkozz újra.',
            'success' => 'Mezőcsoport sikeresen létrehozva.',
        ],

        'update' => [
            'error' => 'A mezőcsoport nem lett frissítve, próbálkozz újra',
            'success' => 'A mezőcsoport sikeresen frissítve.',
        ],

        'delete' => [
            'confirm' => 'Biztosan törölni szeretné ezt a mezőcsoportot?',
            'error' => 'Egy probléma támadt a mezőcsoport törlése közben. Próbálkozz újra.',
            'success' => 'A mezőcsoport sikeresen törölve.',
            'in_use' => 'A mezőcsoport még használatban van.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Hiba az alapértelmezett mezőkészlet értékek érvényesítésében.',

    ],

];
