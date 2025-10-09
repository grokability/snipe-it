<?php

return array(

    'does_not_exist' => 'Lisenssiä ei ole olemassa tai sinulla ei ole oikeuksia tarkastella sitä.',
    'user_does_not_exist' => 'Käyttäjää ei ole olemassa tai sinulla ei ole oikeuksia tarkastella niitä.',
    'asset_does_not_exist' 	=> 'Laitetta jolle yrität määrittää tämän lisenssin ei löydy.',
    'owner_doesnt_match_asset' => 'Laitteen jolle yrität määrittää tämän lisenssin omistaa joku muu kuin se, jonka olet valinnut alasvetovalikosta.',
    'assoc_users'	 => 'Lisenssin on luovutettu käyttäjälle eikä sitä voida poistaa. Palauta lisenssin ensin käyttäjältä, ja yritä sitten uudelleen. ',
    'select_asset_or_person' => 'Sinun on valittava laite tai käyttäjä, mutta ei molempia.',
    'not_found' => 'Lisenssiä ei löydy',
    'seats_available' => ':seat_count istuimet käytettävissä',


    'create' => array(
        'error'   => 'Lisenssiä ei luotu, yritä uudelleen.',
        'success' => 'Lisenssi luotiin onnistuneesti.'
    ),

    'deletefile' => array(
        'error'   => 'Tiedostoa ei poistettu. Ole hyvä ja yritä uudelleen.',
        'success' => 'Tiedosto poistettiin onnistuneesti.',
    ),

    'upload' => array(
        'error'   => 'Tiedostoja ei lähetetty. Ole hyvä ja yritä uudelleen.',
        'success' => 'Tiedostot lähetettiin onnistuneesti.',
        'nofiles' => 'Et ole valinnut lähetettäviä tiedostoja tai lataamasi tiedosto on liian suuri',
        'invalidfiles' => 'Yksi tai useampi tiedosto on liian suuri tai tiedostotyyppiä jota ei sallita. Sallitut tiedostotyypit ovat png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml ja lic.',
    ),

    'update' => array(
        'error'   => 'Lisenssiä ei päivitetty, yritä uudelleen',
        'success' => 'Lisenssi päivitettiin onnistuneesti.'
    ),

    'delete' => array(
        'confirm'   => 'Oletko varma että haluat poistaa tämän lisenssin?',
        'error'   => 'Lisenssin poistamisessa tapahtui virhe. Yritä uudelleen.',
        'success' => 'Lisenssi poistettiin onnistuneesti.'
    ),

    'checkout' => array(
        'error'   => 'Lisenssin luovutuksessa tapahtui virhe. Yritä uudelleen.',
        'success' => 'Lisenssi luovutettiin onnistuneesti',
        'not_enough_seats' => 'Lisenssipaikkoja ei ole riittävästi saatavilla kassalle',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Lisenssin palautuksessa tapahtui virhe. Yritä uudelleen.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Lisenssi palautettiin onnistuneesti'
    ),

);
