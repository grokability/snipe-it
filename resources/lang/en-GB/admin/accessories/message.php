<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'This accessory currently has :count items checked out to users. Please check in the accessories and and try again. ',

    'create' => array(
        'error'   => 'Accessory was not created, please try again.',
        'success' => 'Accessory created successfully.'
    ),

    'update' => array(
        'error'   => 'Accessory was not updated, please try again',
        'success' => 'Accessory updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this accessory?',
        'error'   => 'There was an issue deleting the accessory. Please try again.',
        'success' => 'The accessory was deleted successfully.'
    ),

     'checkout' => array(
        'error'   		=> 'Accessory was not checked out, please try again',
        'success' 		=> 'Accessory checked out successfully.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Accessory was not checked in, please try again',
        'success' 		=> 'Accessory checked in successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.'
    )


);
