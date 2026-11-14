<?php

return [

    'field' => [
        'invalid' => 'Dat veld bestaat niet.',
        'already_added' => 'Veld is al toegevoegd',
        'none_selected' => 'Geen veld geselecteerd',

        'create' => [
            'error' => 'Veld is niet aangemaakt, probeer het opnieuw.',
            'success' => 'Veld is met succes aangemaakt.',
            'assoc_success' => 'Veld is met succes aan veldverzameling toegevoegd.',
        ],

        'update' => [
            'error' => 'Veld was niet gewijzigd, probeer het opnieuw',
            'success' => 'Veld is met succes gewijzigd.',
        ],

        'delete' => [
            'confirm' => 'Weet je zeker dat je dit veld wilt verwijderen?',
            'error' => 'Er was een probleem tijden het verwijderen van dit veld. Probeer het opnieuw.',
            'success' => 'Het veld is met succes verwijderd.',
            'in_use' => 'Veld is nog in gebruik.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Veldverzameling bestaat niet',

        'create' => [
            'error' => 'Veldverzameling is niet aangemaakt, probeer het opnieuw.',
            'success' => 'Veldverzameling is met succes aangemaakt.',
        ],

        'update' => [
            'error' => 'Veldverzameling is niet gewijzigd, probeer het opnieuw',
            'success' => 'Veldverzameling is met succes gewijzigd.',
        ],

        'delete' => [
            'confirm' => 'Weet je zeker dat je deze veldverzameling wilt verwijderen?',
            'error' => 'Er was een probleem tijdens het verwijderen van deze veldverzameling, probeer het opnieuw.',
            'success' => 'De veldverzameling is met succes verwijderd.',
            'in_use' => 'Veldverzamelingen is nog in gebruikt.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Fout bij het valideren van standaard veldset waarden.',

    ],

];
