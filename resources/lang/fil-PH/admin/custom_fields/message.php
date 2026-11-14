<?php

return [

    'field' => [
        'invalid' => 'Ang field ay hindi umiiral.',
        'already_added' => 'Ang field ay naidagdag na',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Ang field ay hindi naisagawa, mangyaring subukang muli.',
            'success' => 'Ang field ay matagumpay na naisagawa.',
            'assoc_success' => 'Ang field ay matagumpay na naidagdag sa fieldset.',
        ],

        'update' => [
            'error' => 'Ang field ay hindi nai-update, mangyaring subukang muli',
            'success' => 'Ang field ay matagumpay na nai-update.',
        ],

        'delete' => [
            'confirm' => 'Sigurado kaba na gusto mong i-delete ang field na ito?',
            'error' => 'Mayroong isyu sa pag-delete ng field. Mangyaring subukang muli.',
            'success' => 'Matagumpay na nai-delete ang field.',
            'in_use' => 'Ang field ay kasalukuyang ginagamit.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Ang fieldset ay hindi umiiral',

        'create' => [
            'error' => 'Ang fieldset ay hindi naisagawa, mangyaring subukang muli.',
            'success' => 'Ang fieldset ay matagumpay na naisagawa.',
        ],

        'update' => [
            'error' => 'Ang fieldset ay hindi nai-update, mangyaring subukang muli',
            'success' => 'Ang fieldset ay matagumpay na nai-update.',
        ],

        'delete' => [
            'confirm' => 'Sigurado kaba na gusto mong i-delete ang fieldset na ito?',
            'error' => 'Mayroong isyu sa pag-delete ng fieldset. Mangyaring subukang muli.',
            'success' => 'Matagumpay na nai-delete ang fieldset.',
            'in_use' => 'Ang fieldset ay kasalukuyang ginagamit.',
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
