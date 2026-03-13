<?php

return array(

    'support_url_help' => 'Variabler <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>og <code>{MODEL_NAME}</code> kan brukes i din URL for å få disse verdiene auto-populere når du ser ressurser - for eksempel https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Produsent eksisterer ikke.',
    'assoc_users'	 => 'Denne produsenten er tilknyttet minst en modell og kan ikke slettes. Oppdatèr modellen(e) til ikke å bruke denne produsenten, og prøv igjen. ',

    'create' => array(
        'error'   => 'Produsent ble ikke opprettet. Prøv igjen.',
        'success' => 'Opprettelse av produsent vellykket.'
    ),

    'update' => array(
        'error'   => 'Produsent ble ikke oppdatert. Prøv igjen',
        'success' => 'Oppdatering av produsent vellykket.'
    ),

    'restore' => array(
        'error'   => 'Produsent ble ikke gjenopprettet. Prøv igjen',
        'success' => 'Gjenopprettelse av produsent vellykket.'
    ),

    'delete' => array(
        'confirm' => 'Er du sikker på at du vil slette denne produsenten?',
        'error'   => 'Det oppstod et problem under sletting av produsenten. Prøv igjen.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
