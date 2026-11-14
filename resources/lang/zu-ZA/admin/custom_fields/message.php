<?php

return [

    'field' => [
        'invalid' => 'Leyo nsiyo ayikho.',
        'already_added' => 'Inkambu isivele iseziwe',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Inkundla ayidalwanga, sicela uzame futhi.',
            'success' => 'Inkundla idalwe ngempumelelo.',
            'assoc_success' => 'Inkambu ingezwe ngempumelelo ku-fieldset.',
        ],

        'update' => [
            'error' => 'Inkambu ayizange ibuyekezwe, sicela uzame futhi',
            'success' => 'Inkundla ibuyekezwe ngempumelelo.',
        ],

        'delete' => [
            'confirm' => 'Uqinisekile ukuthi ufisa ukususa le nsimu?',
            'error' => 'Kube nenkinga yokususa insimu. Ngicela uzame futhi.',
            'success' => 'Insimu isusiwe ngempumelelo.',
            'in_use' => 'Insimu isasebenza.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'I-Fieldset ayikho',

        'create' => [
            'error' => 'I-Fieldset ayidalwanga, sicela uzame futhi.',
            'success' => 'I-Fieldset idalwe ngempumelelo.',
        ],

        'update' => [
            'error' => 'I-Fieldset ayizange ibuyekezwe, sicela uzame futhi',
            'success' => 'I-Fieldset ibuyekezwe ngempumelelo.',
        ],

        'delete' => [
            'confirm' => 'Ingabe uqinisekile ukuthi ufisa ukususa lesi sigaba sendawo?',
            'error' => 'Kube nenkinga ekususeni ukusethwa kwenkambu. Ngicela uzame futhi.',
            'success' => 'I-fieldset isusiwe ngempumelelo.',
            'in_use' => 'I-Fieldset isasebenza.',
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
