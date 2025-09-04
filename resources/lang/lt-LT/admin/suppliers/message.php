<?php

return array(

    'deleted' => 'Ištrintas tiekėjas',
    'does_not_exist' => 'Tokio tiekėjo nėra.',


    'create' => array(
        'error'   => 'Tiekėjas nebuvo sukurtas, bandykite dar kartą.',
        'success' => 'Tiekėjas sukurtas sėkmingai.'
    ),

    'update' => array(
        'error'   => 'Tiekėjas nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Tiekėjas atnaujintas sėkmingai.'
    ),

    'delete' => array(
        'confirm'   => 'Ar tikrai norite panaikinti šį tiekėją?',
        'error'   => 'Bandant panaikinti tiekėją įvyko klaida. Bandykite dar kartą.',
        'success' => 'Tiekėjas panaikintas sėkmingai.',
        'assoc_assets'	 => 'Šis tiekėjas šiuo metu yra susietas su :asset_count turto vienetu (-ais) ir negali būti panaikintas. Atnaujinkite savo turtą, kad nebebūtų sąsajos su šiuo tiekėju, ir bandykite dar kartą. ',
        'assoc_licenses'	 => 'Šis tiekėjas šiuo metu yra susietas su :licenses_count licencija (-omis) ir negali būti panaikintas. Atnaujinkite savo licencijas, kad nebebūtų sąsajos su šiuo tiekėju, ir bandykite dar kartą. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
