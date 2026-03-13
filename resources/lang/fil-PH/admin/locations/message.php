<?php

return array(

    'does_not_exist' => 'Ang lokasyon ay hindi umiiral.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Ang lokasyong ito ay kasalukuyang naiugnay sa hindi bumaba sa isang asset at hindi maaaring mai-delete. Mangyaring i-update ang iyong mga asset upang hindi na magreperens sa lokasyong ito at paki-subok muli. ',
    'assoc_child_loc'	 => 'Ang lokasyong ito ay kasalukuyang pinagmumulan sa hindi bumaba sa lokasyon isang bata at hindi maaaring mai-delete. Mangyaring i-update ang iyong mga lokasyon upang hindi na magreperens sa lokasyong ito at paki-subok muli. ',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Buksan sa :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Ang lokasyon ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Ang lokasyon ay matagumpay na naisagawa.'
    ),

    'update' => array(
        'error'   => 'Ang lokasyon ay hindi nai-update, mangyaring subukang muli',
        'success' => 'Ang lokasyon ay matagumpay na nai-update.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Sigurado kaba na gusto mong i-delete ang lokasyong ito?',
        'error'   => 'Mayroong isyu sa pag-delete ng lokasyon. Mangyaring subukang muli.',
        'success' => 'Matagumpay na nai-delete ang lokasyon.'
    )

);
