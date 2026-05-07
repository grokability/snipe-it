<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Consumable does not exist.',

    'create' => [
        'error' => 'Consumable was not created, please try again.',
        'success' => 'Consumable created successfully.',
    ],

    'update' => [
        'error' => 'Consumable was not updated, please try again',
        'success' => 'Consumable updated successfully.',
    ],

    'delete' => [
        'confirm' => 'Are you sure you wish to delete this consumable?',
        'error' => 'There was an issue deleting the consumable. Please try again.',
        'success' => 'The consumable was deleted successfully.',
    ],

    'checkout' => [
        'error' => 'Consumable was not checked out, please try again',
        'success' => 'Consumable checked out successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'checkin' => [
        'error' => 'Consumable was not checked in, please try again',
        'success' => 'Consumable checked in successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
    ],

];
