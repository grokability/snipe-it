<?php

return array(

    'does_not_exist' => 'Ligging bestaan ​​nie.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Hierdie ligging is tans geassosieer met ten minste een bate en kan nie uitgevee word nie. Dateer asseblief jou bates op om nie meer hierdie ligging te verwys nie en probeer weer.',
    'assoc_child_loc'	 => 'Hierdie ligging is tans die ouer van ten minste een kind se plek en kan nie uitgevee word nie. Werk asseblief jou liggings by om nie meer hierdie ligging te verwys nie en probeer weer.',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Ligging is nie geskep nie, probeer asseblief weer.',
        'success' => 'Ligging suksesvol geskep.'
    ),

    'update' => array(
        'error'   => 'Ligging is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Ligging suksesvol opgedateer.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Is jy seker jy wil hierdie ligging uitvee?',
        'error'   => 'Daar was \'n probleem met die verwydering van die ligging. Probeer asseblief weer.',
        'success' => 'Die ligging is suksesvol verwyder.'
    )

);
