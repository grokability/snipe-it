<?php

return [
    'does_not_exist' => 'Tokios įmonės nėra.',
    'deleted' => 'Panaikinta įmonė',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
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
        'error' => 'Bandant panaikinti įmonę įvyko klaida. Bandykite dar kartą.',
        'success' => 'Įmonė sėkmingai panaikinta.',
        'bulk_success' => 'Įmonė sėkmingai ištrinta.|Įmonės (:count) sėkmingai ištrintos.',
        'partial_success' => 'Įmonė sėkmingai ištrinta. Daugiau informacijos rasite žemiau. | Įmonės (:count) buvo sėkmingai ištrintos. Daugiau informacijos rasite žemiau.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
