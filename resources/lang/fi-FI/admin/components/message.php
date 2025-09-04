<?php

return array(

    'does_not_exist' => 'Komponenttia ei ole olemassa.',

    'create' => array(
        'error'   => 'Komponenttia ei luotu, yritä uudelleen.',
        'success' => 'Komponentti on luotu onnistuneesti.'
    ),

    'update' => array(
        'error'   => 'Komponenttia ei ole päivitetty, yritä uudelleen',
        'success' => 'Komponentti on päivitetty onnistuneesti.'
    ),

    'delete' => array(
        'confirm'   => 'Haluatko varmasti poistaa tämän komponentin?',
        'error'   => 'Komponentti poisto ei onnistunut. Yritä uudelleen.',
        'success' => 'Komponentti poistettiin.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> 'Komponenttia ei luovutettu, yritä uudelleen',
        'success' 		=> 'Komponentin luovutus onnistui.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.',
        'unavailable'      => 'Ei riittävästi komponentteja: :remaining jäljellä, :requested pyydetty ',
    ),

    'checkin' => array(
        'error'   		=> 'Komponenttia ei palautettu, yritä uudelleen',
        'success' 		=> 'Komponentti palautettiin onnistuneesti.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.'
    )


);
