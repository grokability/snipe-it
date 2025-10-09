<?php

return array(

    'does_not_exist' => 'Staðsetningin er ekki til.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'This location is currently associated with at least one asset and cannot be deleted. Please update your assets to no longer reference this location and try again. ',
    'assoc_child_loc'	 => 'This location is currently the parent of at least one child location and cannot be deleted. Please update your locations to no longer reference this location and try again. ',
    'assigned_assets' => 'Skráðar eignir',
    'current_location' => 'Núverandi staðsetning',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Þessi birgi var ekki stofnaður. Vinsamlegast reyndu aftur.',
        'success' => 'Stofnun staðsetingar gekk.'
    ),

    'update' => array(
        'error'   => 'Staðsetning var ekki uppfærð, vinsamlega reyndu aftur',
        'success' => 'Stofnun staðsetningar gekk.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Ertu viss um að þú vilkjir eyða þessari staðsetningu?',
        'error'   => 'Það er vandamál við að eyða þessari staðsetningu. Vinsamlega reyndu aftur.',
        'success' => 'Þessari staðsetningur var eytt.'
    )

);
