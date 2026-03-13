<?php

return array(

    'does_not_exist' => 'Component does not exist.',

    'create' => array(
        'error'   => 'Component was not created, please try again.',
        'success' => 'Component created successfully.'
    ),

    'update' => array(
        'error'   => 'Component was not updated, please try again',
        'success' => 'Component updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this component?',
        'error'   => 'There was an issue deleting the component. Please try again.',
        'success' => 'The component was deleted successfully.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Component was not checked out, please try again',
        'success' 		=> 'Component checked out successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
        'unavailable'      => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ),

    'checkin' => array(
        'error'   		=> 'Component was not checked in, please try again',
        'success' 		=> 'Component checked in successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.'
    )


);
