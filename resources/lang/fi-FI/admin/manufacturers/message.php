<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Valmistajaa ei löydy.',
    'assoc_users'	 => 'Valmistaja on määritetty käyttöön yhdelle tai useammalle mallille joten sitä ei voida poistaa. Poista valmistaja käytöstä kaikilta malleilta ja yritä uudelleen. ',

    'create' => array(
        'error'   => 'Valmistajaa ei luotu, ole hyvä ja yritä uudelleen.',
        'success' => 'Valmistaja luotiin onnistuneesti.'
    ),

    'update' => array(
        'error'   => 'Valmistajaa ei päivitetty, ole hyvä ja yritä uudelleen',
        'success' => 'Valmistaja päivitettiin onnistuneesti.'
    ),

    'restore' => array(
        'error'   => 'Valmistajaa ei palautettu, ole hyvä ja yritä uudelleen',
        'success' => 'Valmistaja palautettu onnistuneesti.'
    ),

    'delete' => array(
        'confirm' => 'Oletko varma että haluat poistaa tämän valmistajan?',
        'error'   => 'Valmistajan poistamisessa tapahtui virhe. Ole hyvä ja yritä uudelleen.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
