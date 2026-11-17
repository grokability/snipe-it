<?php

return [

    'field' => [
        'invalid' => 'To polje ne postoji.',
        'already_added' => 'Polje je već dodano',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Polje nije izrađeno, pokušajte ponovo.',
            'success' => 'Polje je uspješno izrađeno.',
            'assoc_success' => 'Polje je uspješno dodano u fieldset.',
        ],

        'update' => [
            'error' => 'Polje nije ažurirano, pokušajte ponovo',
            'success' => 'Polje je uspješno ažurirano.',
        ],

        'delete' => [
            'confirm' => 'Jeste li sigurni da želite izbrisati ovo polje?',
            'error' => 'Došlo je do problema s brisanjem polja. Molim te pokušaj ponovno.',
            'success' => 'Polje je uspješno obrisano.',
            'in_use' => 'Polje je još u upotrebi.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset ne postoji',

        'create' => [
            'error' => 'Fieldset nije izrađen, pokušajte ponovo.',
            'success' => 'Fieldset je uspješno izrađen.',
        ],

        'update' => [
            'error' => 'Fieldset nije ažuriran, pokušajte ponovo',
            'success' => 'Fieldset je uspješno ažuriran.',
        ],

        'delete' => [
            'confirm' => 'Jeste li sigurni da želite izbrisati ovu grupu polja?',
            'error' => 'Došlo je do problema s brisanjem fieldseta. Molim te pokušaj ponovno.',
            'success' => 'Fieldset je uspješno izbrisan.',
            'in_use' => 'Fieldset je još uvijek u upotrebi.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Grupa polja uspješno je klonirana.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
