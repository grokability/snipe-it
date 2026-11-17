<?php

return [

    'field' => [
        'invalid' => 'Níl an réimse sin ann.',
        'already_added' => 'Réimse curtha cheana féin',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Níor cruthaíodh réimse, déan iarracht arís.',
            'success' => 'Réimse cruthaithe go rathúil.',
            'assoc_success' => 'Réimse curtha leis go rathúil go rathúil.',
        ],

        'update' => [
            'error' => 'Níor nuashonraíodh an réimse, déan iarracht arís',
            'success' => 'Réimse cothrom le dáta go rathúil',
        ],

        'delete' => [
            'confirm' => 'An bhfuil tú cinnte gur mian leat an réimse seo a scriosadh?',
            'error' => 'Bhí ceist ann a scriosadh an réimse. Arís, le d\'thoil.',
            'success' => 'Scriosadh an réimse go rathúil.',
            'in_use' => 'Tá réimse fós in úsáid.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Ní bhíonn Fieldset ann',

        'create' => [
            'error' => 'Níor cruthaíodh Fieldset, déan iarracht arís.',
            'success' => 'Cruthaíodh Fieldset go rathúil.',
        ],

        'update' => [
            'error' => 'Níor cuireadh nuashonrú ar Fieldset, déan iarracht arís',
            'success' => 'Fieldset uasdátú go rathúil.',
        ],

        'delete' => [
            'confirm' => 'An bhfuil tú cinnte go dteastaíonn uait an teip seo a scriosadh?',
            'error' => 'Bhí ceist ann a scriosadh an slí. Arís, le d\'thoil.',
            'success' => 'Scriosadh an frithbhealach go rathúil.',
            'in_use' => 'Tá Fieldset fós in úsáid.',
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
