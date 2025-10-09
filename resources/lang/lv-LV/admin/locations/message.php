<?php

return array(

    'does_not_exist' => 'Atrašanās vietas neeksistē.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Pašlaik šī atrašanās vieta ir saistīta ar vismaz vienu īpašumu un to nevar izdzēst. Lūdzu, atjauniniet savus aktīvus, lai vairs nerindotu šo atrašanās vietu, un mēģiniet vēlreiz.',
    'assoc_child_loc'	 => 'Pašlaik šī vieta ir vismaz viena bērna atrašanās vieta un to nevar izdzēst. Lūdzu, atjauniniet savas atrašanās vietas, lai vairs nerindotu šo atrašanās vietu, un mēģiniet vēlreiz.',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Atrašanās vieta nav izveidota, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Atrašanās vieta ir veiksmīgi izveidota.'
    ),

    'update' => array(
        'error'   => 'Atrašanās vieta nav atjaunināta, lūdzu, mēģiniet vēlreiz',
        'success' => 'Atrašanās vieta ir veiksmīgi atjaunināta.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Vai tiešām vēlaties dzēst šo atrašanās vietu?',
        'error'   => 'Radās problēma, dzēšot atrašanās vietu. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Atrašanās vieta tika veiksmīgi dzēsta.'
    )

);
