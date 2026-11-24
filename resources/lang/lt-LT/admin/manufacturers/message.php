<?php

return [

    'support_url_help' => 'Kintamieji: <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code> ir <code>{MODEL_NAME}</code> gali būti naudojami jūsų URL, kad šios reikšmės būtų automatiškai įvestos, kai peržiūrite turtą. Pvz., https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Tokio gamintojo nėra.',
    'assoc_users' => 'Šis gamintojas šiuo metu yra susieta bent su vienu modeliu ir negali būti ištrintas. Atnaujinkite savo modelius, kad nebebūtų sąsajos su šiuo gamintoju, ir bandykite dar kartą. ',

    'create' => [
        'error' => 'Gamintojas nebuvo sukurtas, bandykite dar kartą.',
        'success' => 'Gamintojas sėkmingai sukurtas.',
    ],

    'update' => [
        'error' => 'Gamintojas nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Gamintojas sėkmingai atnaujintas.',
    ],

    'restore' => [
        'error' => 'Gamintojas nebuvo atkurtas, bandykite dar kartą',
        'success' => 'Gamintojas sėkmingai atkurtas.',
    ],

    'delete' => [
        'confirm' => 'Ar jūs tikrai norite ištrinti šį gamintoją?',
        'error' => 'Bandant ištrinti gamintoją įvyko klaida. Bandykite dar kartą.',
        'success' => 'Gamintojas sėkmingai ištrintas.',
        'bulk_success' => 'Gamintojas sėkmingai ištrintas.|Gamintojai (:count) sėkmingai ištrinti.',
        'partial_success' => 'Gamintojas sėkmingai ištrintas. Daugiau informacijos rasite žemiau. | Gamintojai (:count) buvo sėkmingai ištrinti. Daugiau informacijos rasite žemiau.',
    ],

];
