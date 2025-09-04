<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Hindi umiiral ang tagapagsuplay.',


    'create' => array(
        'error'   => 'Ang tagapagsuplay ay hindi naisagawa, mangyaring subukang muli.',
        'success' => 'Matagumpay na naisagawa nga tagapagsuplay.'
    ),

    'update' => array(
        'error'   => 'Hindi nai-update ang tagapagsuplay, mangyaring subukang muli',
        'success' => 'Matagumpay na nai-update ang tagapagsuplay.'
    ),

    'delete' => array(
        'confirm'   => 'Sigurado kaba na gusto mong i-delete ang tagapagsuplay na ito?',
        'error'   => 'Mayroong isyu sa pag-delete ng tagapagsuplay. Mangyaring subukang muli.',
        'success' => 'Matagumpay na nai-delete ang tagapagsuplay.',
        'assoc_assets'	 => 'Ang tagapagsuplay ay kasalukuyang naiugnay sa :asset_count asset(s) at hindi maaaring mai-delete. Manyaring i-update ang iyong mga asset upang hindi na magreperens sa tagapagsuplay na ito at pakisubok muli. ',
        'assoc_licenses'	 => 'Ang tagapagsuplay ay kasalukuyang naiugnay sa :licenses_count licences(s) at hindi maaaring mai-delete. Manyaring i-update ang iyong mga lisensya upang hindi na magreperens sa tagapagsuplay na ito at pakisubok muli. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
