<?php

return [

    'field' => [
        'invalid' => 'Daardie veld bestaan ​​nie.',
        'already_added' => 'Veld reeds bygevoeg',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Veld is nie geskep nie, probeer asseblief weer.',
            'success' => 'Veld geskep suksesvol.',
            'assoc_success' => 'Veld suksesvol bygevoeg tot veldtog.',
        ],

        'update' => [
            'error' => 'Veld is nie opgedateer nie, probeer asseblief weer',
            'success' => 'Veld suksesvol opgedateer.',
        ],

        'delete' => [
            'confirm' => 'Is jy seker jy wil hierdie veld uitvee?',
            'error' => 'Daar was \'n probleem met die verwydering van die veld. Probeer asseblief weer.',
            'success' => 'Die veld is suksesvol verwyder.',
            'in_use' => 'Veld is nog steeds in gebruik.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Veldset bestaan ​​nie',

        'create' => [
            'error' => 'Veldtipe is nie geskep nie, probeer asseblief weer.',
            'success' => 'Veldtipe suksesvol geskep.',
        ],

        'update' => [
            'error' => 'Fieldset is nie opgedateer nie, probeer asseblief weer',
            'success' => 'Veldset suksesvol opgedateer.',
        ],

        'delete' => [
            'confirm' => 'Is jy seker jy wil hierdie veldtog uitvee?',
            'error' => 'Daar was \'n probleem met die verwydering van die veldtog. Probeer asseblief weer.',
            'success' => 'Die veldtog is suksesvol verwyder.',
            'in_use' => 'Veldset is nog steeds in gebruik.',
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
