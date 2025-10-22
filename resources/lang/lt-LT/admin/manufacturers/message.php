<?php

return array(

    'support_url_help' => 'Kintamieji: <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code> ir <code>{MODEL_NAME}</code> gali būti naudojami jūsų URL, kad šios reikšmės būtų automatiškai įvestos, kai peržiūrite turtą. Pvz., https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Tokio gamintojo nėra.',
    'assoc_users'	 => 'Šis gamintojas šiuo metu yra susieta bent su vienu modeliu ir negali būti panaikintas. Atnaujinkite savo modelius, kad nebebūtų sąsajos su šiuo gamintoju, ir bandykite dar kartą. ',

    'create' => array(
        'error'   => 'Gamintojas nebuvo sukurtas, bandykite dar kartą.',
        'success' => 'Gamintojas sukurtas sėkmingai.'
    ),

    'update' => array(
        'error'   => 'Gamintojas nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Gamintojas atnaujintas sėkmingai.'
    ),

    'restore' => array(
        'error'   => 'Gamintojas nebuvo atkurtas, bandykite dar kartą',
        'success' => 'Gamintojas atkurtas sėkmingai.'
    ),

    'delete' => array(
        'confirm' => 'Ar jūs tikrai norite panaikinti šį gamintoją?',
        'error'   => 'Bandant panaikinti gamintoją įvyko klaida. Bandykite dar kartą.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
