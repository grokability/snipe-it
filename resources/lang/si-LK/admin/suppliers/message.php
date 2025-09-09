<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Supplier does not exist.',


    'create' => array(
        'error'   => 'Supplier was not created, please try again.',
        'success' => 'Supplier created successfully.'
    ),

    'update' => array(
        'error'   => 'Supplier was not updated, please try again',
        'success' => 'Supplier updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this supplier?',
        'error'   => 'There was an issue deleting the supplier. Please try again.',
        'success' => 'Supplier was deleted successfully.',
        'assoc_assets'	 => 'This supplier is currently associated with :asset_count asset(s) and cannot be deleted. Please update your assets to no longer reference this supplier and try again. ',
        'assoc_licenses'	 => 'This supplier is currently associated with :licenses_count licences(s) and cannot be deleted. Please update your licenses to no longer reference this supplier and try again. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
