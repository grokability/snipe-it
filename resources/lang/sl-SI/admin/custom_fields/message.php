<?php

return [

    'field' => [
        'invalid' => 'To polje ne obstaja.',
        'already_added' => 'Polje je že dodano',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Polje ni bilo ustvarjeno, poskusite znova.',
            'success' => 'Polje je uspešno ustvarjeno.',
            'assoc_success' => 'Polje je uspešno dodano v set polj.',
        ],

        'update' => [
            'error' => 'Polje ni bilo posodobljeno, poskusite znova',
            'success' => 'Polje je bilo uspešno posodobljeno.',
        ],

        'delete' => [
            'confirm' => 'Ali ste prepričani, da želite izbrisati to polje?',
            'error' => 'Prišlo je do težave z brisanjem polja. Prosim poskusite ponovno.',
            'success' => 'Polje je bilo uspešno izbrisano.',
            'in_use' => 'Polje je še vedno v uporabi.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Set polj ne obstaja',

        'create' => [
            'error' => 'Set polj ni bil ustvarjen, poskusite znova.',
            'success' => 'Set polkj je bil uspešno ustvarjen.',
        ],

        'update' => [
            'error' => 'Set polj ni bil posodobljen, poskusite znova',
            'success' => 'Set polj je bil uspešno posodobljen.',
        ],

        'delete' => [
            'confirm' => 'Ali ste prepričani, da želite izbrisati ta set polj?',
            'error' => 'Prišlo je do težave z brisanjem seta polj. Prosim poskusite ponovno.',
            'success' => 'Set polj je uspešno izbrisano.',
            'in_use' => 'Set polj je še vedno v uporabi.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
