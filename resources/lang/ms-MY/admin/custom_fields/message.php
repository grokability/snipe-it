<?php

return [

    'field' => [
        'invalid' => 'Bidang itu tidak wujud.',
        'already_added' => 'Bidang sudah ditambahkan',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Bidang tidak dibuat, sila cuba lagi.',
            'success' => 'Bidang berjaya dibuat.',
            'assoc_success' => 'Bidang berjaya ditambah ke fieldset.',
        ],

        'update' => [
            'error' => 'Bidang tidak dikemas kini, sila cuba lagi',
            'success' => 'Bidang dikemas kini berjaya.',
        ],

        'delete' => [
            'confirm' => 'Adakah anda pasti ingin memadamkan medan ini?',
            'error' => 'Terdapat masalah memadamkan medan. Sila cuba lagi.',
            'success' => 'Medan telah berjaya dipadam.',
            'in_use' => 'Medan masih digunakan.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset tidak wujud',

        'create' => [
            'error' => 'Fieldset tidak dibuat, sila cuba lagi.',
            'success' => 'Fieldset berjaya berjaya.',
        ],

        'update' => [
            'error' => 'Fieldset tidak dikemas kini, sila cuba lagi',
            'success' => 'Fieldset dikemas kini dengan jayanya.',
        ],

        'delete' => [
            'confirm' => 'Adakah anda pasti ingin memadamkan medan ini?',
            'error' => 'Terdapat isu memadamkan medan. Sila cuba lagi.',
            'success' => 'Bidang field telah berjaya dipadam.',
            'in_use' => 'Fieldset masih digunakan.',
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
