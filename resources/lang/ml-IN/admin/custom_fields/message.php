<?php

return [

    'field' => [
        'invalid' => 'That field does not exist.',
        'already_added' => 'Field already added',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Field was not created, please try again.',
            'success' => 'Field created successfully.',
            'assoc_success' => 'Field successfully added to fieldset.',
        ],

        'update' => [
            'error' => 'Field was not updated, please try again',
            'success' => 'Field updated successfully.',
        ],

        'delete' => [
            'confirm' => 'Are you sure you wish to delete this field?',
            'error' => 'There was an issue deleting the field. Please try again.',
            'success' => 'The field was deleted successfully.',
            'in_use' => 'Field is still in use.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset does not exist',

        'create' => [
            'error' => 'Fieldset was not created, please try again.',
            'success' => 'Fieldset created successfully.',
        ],

        'update' => [
            'error' => 'Fieldset was not updated, please try again',
            'success' => 'Fieldset updated successfully.',
        ],

        'delete' => [
            'confirm' => 'Are you sure you wish to delete this fieldset?',
            'error' => 'There was an issue deleting the fieldset. Please try again.',
            'success' => 'The fieldset was deleted successfully.',
            'in_use' => 'Fieldset is still in use.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'ഫീൽഡ്‌സെറ്റ് വിജയകരമായി ക്ലോൺ ചെയ്‌തു.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
