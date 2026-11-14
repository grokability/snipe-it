<?php

return [

    'field' => [
        'invalid' => 'Takové pole neexistuje.',
        'already_added' => 'Takové pole již existuje',
        'none_selected' => 'Žádné pole nebylo označeno',

        'create' => [
            'error' => 'Pole se nepodařilo přidat, pokuste se o to znovu.',
            'success' => 'Pole se v pořádku vytvořilo.',
            'assoc_success' => 'Pole se v pořádku přidalo do sady.',
        ],

        'update' => [
            'error' => 'Pole se nepodařilo upravit, pokuste se o to znovu',
            'success' => 'Pole v pořádku upraveno.',
        ],

        'delete' => [
            'confirm' => 'Opravdu si přejete odstranit toto pole?',
            'error' => 'Nepodařilo se nám pole odstranit. Zkuste to prosím znovu.',
            'success' => 'Pole bylo v pořádku odstraněno.',
            'in_use' => 'Toto pole je stále používané.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset neexistuje',

        'create' => [
            'error' => 'Sadu se nám nepodařilo vytvořit, pokuste se o to znovu.',
            'success' => 'Sada byla úspěšně vytvořena.',
        ],

        'update' => [
            'error' => 'Sadu se nám nepodařilo upravit, pokuste se o to znovu',
            'success' => 'Sada byla v pořádku upravena.',
        ],

        'delete' => [
            'confirm' => 'Opravdu si přejete odstranit celou tuto sadu?',
            'error' => 'Nepodařilo se nám sadu odstranit. Zkuste to prosím znovu.',
            'success' => 'Sada byla v pořádku odstraněna.',
            'in_use' => 'Sada je stále užívána.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Chyba při ověřování hodnot ve fieldsetu.',

    ],

];
