<?php

return array(

    'support_url_help' => 'Variabler <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, <code>{MODEL_NAME}</code> kan bruges i din URL til at have disse værdier automatisk udfyldes ved visning af aktiver - for eksempel https://checkcoverage. pple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Fabrikant eksistere ikke.',
    'assoc_users'	 => 'Denne producent er i øjeblikket forbundet med mindst en model og kan ikke slettes. Opdater dine modeller for ikke længere at henvise til denne producent, og prøv igen.',

    'create' => array(
        'error'   => 'Producenten blev ikke oprettet, prøv igen.',
        'success' => 'Producenten blev oprettet med succes.'
    ),

    'update' => array(
        'error'   => 'Producenten blev ikke opdateret, prøv igen',
        'success' => 'Producent opdateret med succes.'
    ),

    'restore' => array(
        'error'   => 'Producenten blev ikke gendannet, prøv igen',
        'success' => 'Producenten blev genoprettet med succes.'
    ),

    'delete' => array(
        'confirm' => 'Er du sikker på, at du vil slette denne producent?',
        'error'   => 'Der opstod et problem ved at slette producenten. Prøv igen.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
