<?php

return [

    'field' => [
        'invalid' => 'Oznaka statusa ne postoji.',
        'already_added' => 'Polje je već dodato',
        'none_selected' => 'Nijedno polje nije izabrano',

        'create' => [
            'error' => 'Polje nije kreirano, pokušajte ponovo.',
            'success' => 'Polje je uspešno kreirano.',
            'assoc_success' => 'Polje je uspešno dodato u grupu polja.',
        ],

        'update' => [
            'error' => 'Polje nije kreirano, pokušajte ponovo',
            'success' => 'Polje je uspešno kreirano.',
        ],

        'delete' => [
            'confirm' => 'Jeste li sigurni da želite izbrisati ovo polje?',
            'error' => 'Postoji problem sa brisanjem ovog polja. Molimo Vas pokušajte ponovo.',
            'success' => 'Polje je uspešno izbrisano.',
            'in_use' => 'Polje je u upotrebi.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Grupa polja ne postoji',

        'create' => [
            'error' => 'Polje nije kreirano, pokušajte ponovo.',
            'success' => 'Grupa polja je uspešno kreirana.',
        ],

        'update' => [
            'error' => 'Grupa polja nije ažurirana, pokušajte ponovo',
            'success' => 'Grupa polja je uspešno ažurirana.',
        ],

        'delete' => [
            'confirm' => 'Jeste li sigurni da želite da izbrišete ovo polje?',
            'error' => 'Postoji problem sa brisanjem ove grupe polja. Molimo Vas pokušajte ponovo.',
            'success' => 'Grupa polja je uspešno obrisana.',
            'in_use' => 'Polje je u upotrebi.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Greška u veriifikaciji podrazumevanih vrednosti grupe polja.',

    ],

];
