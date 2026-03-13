<?php

return array(

    'does_not_exist' => 'Níl an suíomh ann.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Tá an suíomh seo bainteach le sócmhainn amháin ar a laghad agus ní féidir é a scriosadh. Déan do sócmhainní a thabhairt cothrom le dáta gan tagairt a dhéanamh don áit seo agus déan iarracht arís.',
    'assoc_child_loc'	 => 'Faoi láthair tá an suíomh seo ina tuismitheoir ar a laghad ar shuíomh leanbh amháin ar a laghad agus ní féidir é a scriosadh. Nuashonraigh do láithreacha le do thoil gan tagairt a dhéanamh don suíomh seo agus déan iarracht arís.',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Níor cruthaíodh an suíomh, déan iarracht arís.',
        'success' => 'Suíomh cruthaithe go rathúil.'
    ),

    'update' => array(
        'error'   => 'Níor nuashonraíodh an suíomh, déan iarracht arís',
        'success' => 'Suíomh curtha chun dáta go rathúil.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'An bhfuil tú cinnte gur mian leat an suíomh seo a scriosadh?',
        'error'   => 'Bhí ceist ann a scriosadh an suíomh. Arís, le d\'thoil.',
        'success' => 'Scriosadh an suíomh go rathúil.'
    )

);
