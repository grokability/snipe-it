<?php

return [

    'field' => [
        'invalid' => 'Dette felt findes ikke.',
        'already_added' => 'Feltet allerede tilføjet',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Feltet blev ikke oprettet, prøve igen.',
            'success' => 'Feltet oprettet.',
            'assoc_success' => 'Felt tilføjet til feltsættet.',
        ],

        'update' => [
            'error' => 'Feltet blev ikke opdateret, prøv igen',
            'success' => 'Feltet opdateret.',
        ],

        'delete' => [
            'confirm' => 'Er du sikker på du ønsker at slette feltet?',
            'error' => 'Der opstod et problem under sletning af feltet. Prøv venligst igen.',
            'success' => 'Feltet blev slettet.',
            'in_use' => 'Feltet er stadig i brug.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Feltsættet findes ikke',

        'create' => [
            'error' => 'Feltsættet blev ikke opdateret, prøv igen.',
            'success' => 'Feltsættet oprettet.',
        ],

        'update' => [
            'error' => 'Feltsættet blev ikke opdateret, prøv igen',
            'success' => 'Feltsættet opdateret.',
        ],

        'delete' => [
            'confirm' => 'Er du sikker på du vil slette dette feltsæt?',
            'error' => 'Der opstod et problem under sletning af feltsættet. Prøv venligst igen.',
            'success' => 'Feltsættet blev slettet.',
            'in_use' => 'Feltsættet er stadig i brug.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Fejl under validering af standard feltsæt-værdier.',

    ],

];
