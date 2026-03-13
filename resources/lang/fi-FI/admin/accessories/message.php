<?php

return array(

    'does_not_exist' => 'Oheistarviketta [:id] ei ole.',
    'not_found' => 'Tätä lisävarustetta ei löytynyt.',
    'assoc_users'	 => 'Oheistarviketta on tällä hetkellä luovutettuna :count nimikettä käyttäjille. Tarkista oheistarvikkeiden tila ja yritä uudelleen. ',

    'create' => array(
        'error'   => 'Oheistarviketta ei luotu, yritä uudelleen.',
        'success' => 'Oheistarvike on luotu.'
    ),

    'update' => array(
        'error'   => 'Oheistarviketta ei päivitetty, yritä uudelleen',
        'success' => 'Oheistarvike päivitettiin onnistuneesti.'
    ),

    'delete' => array(
        'confirm'   => 'Haluatko varmasti poistaa tämän oheistarvikkeen?',
        'error'   => 'Oheistarvikkeen poistaminen ei onnistunut. Yritä uudelleen.',
        'success' => 'Oheistarvike poistettiin onnistuneesti.'
    ),

     'checkout' => array(
        'error'   		=> 'Oheistarviketta ei luovutettu, yritä uudelleen',
        'success' 		=> 'Oheistarvike luovutettiin onnistuneesti.',
        'unavailable'   => 'Oheistarvike ei ole lainattavissa. Tarkista saatavilla oleva määrä',
        'user_does_not_exist' => 'Käyttäjä on virheellinen. Yritä uudelleen.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Oheistarviketta ei palautettu, yritä uudelleen',
        'success' 		=> 'Oheistarvike palautettiin onnistuneesti.',
        'user_does_not_exist' => 'Kyseinen käyttäjä on virheellinen. Yritä uudelleen.'
    )


);
