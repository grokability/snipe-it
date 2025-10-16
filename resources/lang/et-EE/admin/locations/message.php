<?php

return array(

    'does_not_exist' => 'Asukohta ei eksisteeri.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Selle asukohaga on seotud vähemalt üks vahend ja seda ei saa kustutada. Palun uuenda oma vahendeid, et need ei kasutaks seda asukohta ning seejärel proovi uuesti. ',
    'assoc_child_loc'	 => 'Sel asukohal on hetkel all-asukohti ja seda ei saa kustutada. Palun uuenda oma asukohti nii, et need ei kasutaks seda asukohta ning seejärel proovi uuesti. ',
    'assigned_assets' => 'Määratud Varad',
    'current_location' => 'Praegune asukoht',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Asukohta ei loodud, palun proovi uuesti.',
        'success' => 'Asukoha loomine õnnestus.'
    ),

    'update' => array(
        'error'   => 'Asukohta ei uuendatud, palun proovi uuesti',
        'success' => 'Asukoha uuendamine õnnestus.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Oled sa kindel, et soovid selle asukoha kustutada?',
        'error'   => 'Asukoha kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Asukoha kustutamine õnnestus.'
    )

);
