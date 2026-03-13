<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Manufacturer does not exist.',
    'assoc_users'	 => 'This manufacturer is currently associated with at least one model and cannot be deleted. Please update your models to no longer reference this manufacturer and try again. ',

    'create' => array(
        'error'   => 'Manufacturer was not created, please try again.',
        'success' => 'Manufacturer created successfully.'
    ),

    'update' => array(
        'error'   => 'Manufacturer was not updated, please try again',
        'success' => 'Manufacturer updated successfully.'
    ),

    'restore' => array(
        'error'   => 'Manufacturer was not restored, please try again',
        'success' => 'Manufacturer restored successfully.'
    ),

    'delete' => array(
        'confirm' => 'Are you sure you wish to delete this manufacturer?',
        'error'   => 'There was an issue deleting the manufacturer. Please try again.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
