<?php

return array(

    'deleted' => 'Birgja eytt',
    'does_not_exist' => 'Þessi birgir er ekki til.',


    'create' => array(
        'error'   => 'Þessi birgir var ekki skráður. Vinsamlegast reyndu aftur.',
        'success' => 'Supplier created successfully.'
    ),

    'update' => array(
        'error'   => 'Þessi birgi var ekki skráður. Vinsamlegast reyndu aftur',
        'success' => 'Supplier updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Ertu viss um að þú viljir afskrá þennan birgi?',
        'error'   => 'There was an issue deleting the supplier. Please try again.',
        'success' => 'Supplier was deleted successfully.',
        'assoc_assets'	 => 'This supplier is currently associated with :asset_count asset(s) and cannot be deleted. Please update your assets to no longer reference this supplier and try again. ',
        'assoc_licenses'	 => 'This supplier is currently associated with :licenses_count licences(s) and cannot be deleted. Please update your licenses to no longer reference this supplier and try again. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
