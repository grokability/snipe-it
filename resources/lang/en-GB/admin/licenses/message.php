<?php

return [

    'does_not_exist' => 'Licence does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' => 'The asset you are trying to associate with this license does not exist.',
    'owner_doesnt_match_asset' => 'The asset you are trying to associate with this license is owned by somene other than the person selected in the assigned to dropdown.',
    'assoc_users' => 'This license is currently checked out to a user and cannot be deleted. Please check the license in first, and then try deleting again. ',
    'select_asset_or_person' => 'You must select an asset or a user, but not both.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',

    'create' => [
        'error' => 'License was not created, please try again.',
        'success' => 'License created successfully.',
    ],

    'deletefile' => [
        'error' => 'File not deleted. Please try again.',
        'success' => 'File successfully deleted.',
    ],

    'upload' => [
        'error' => 'File(s) not uploaded. Please try again.',
        'success' => 'File(s) successfully uploaded.',
        'nofiles' => 'You did not select any files for upload, or the file you are trying to upload is too large',
        'invalidfiles' => 'One or more of your files is too large or is a filetype that is not allowed. Allowed filetypes are png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, and lic.',
    ],

    'update' => [
        'error' => 'License was not updated, please try again',
        'success' => 'License updated successfully.',
    ],

    'delete' => [
        'confirm' => 'Are you sure you wish to delete this license?',
        'error' => 'There was an issue deleting the license. Please try again.',
        'success' => 'The license was deleted successfully.',
    ],

    'checkout' => [
        'error' => 'There was an issue checking out the license. Please try again.',
        'success' => 'The license was checked out successfully',
        'not_enough_seats' => 'Not enough licence seats available for checkout',
        'mismatch' => 'The licence seat provided does not match the licence',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ],

    'checkin' => [
        'error' => 'There was an issue checking in the license. Please try again.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'The license was checked in successfully',
    ],

];
