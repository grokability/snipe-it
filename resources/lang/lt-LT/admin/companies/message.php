<?php

return [
    'does_not_exist' => 'Tokios įmonės nėra.',
    'deleted' => 'Ištrinta įmonė',
    'assoc_users' => 'Ši įmonė šiuo metu yra susieta bent su vienu kitu elementu („Snipe-IT“ sistemoje esančia vieta, naudotoju, licencija, turtu ir kt.), todėl negali būti ištrinta. Atnaujinkite savo modelius, kad jie nebebūtų susieti su šia įmone, ir bandykite dar kartą. ',
    'create' => [
        'error' => 'Įmonė nebuvo sukurta, bandykite dar kartą.',
        'success' => 'Įmonė sėkmingai sukurta.',
    ],
    'update' => [
        'error' => 'Įmonė nebuvo atnaujinta, bandykite dar kartą',
        'success' => 'Įmonė sėkmingai atnaujinta.',
    ],
    'delete' => [
        'confirm' => 'Ar tikrai norite ištrinti šią įmonę?',
        'error' => 'Bandant ištrinti įmonę įvyko klaida. Bandykite dar kartą.',
        'success' => 'Įmonė sėkmingai ištrinta.',
        'bulk_success' => 'Įmonė sėkmingai ištrinta.|Įmonės (:count) sėkmingai ištrintos.',
        'partial_success' => 'Įmonė sėkmingai ištrinta. Daugiau informacijos rasite žemiau. | Įmonės (:count) buvo sėkmingai ištrintos. Daugiau informacijos rasite žemiau.',
    ],
    'webhook_fields_require_superuser' => '„Webhook“ konfigūracijos laukus (:fields) gali keisti tik supernaudotojas.',
];
