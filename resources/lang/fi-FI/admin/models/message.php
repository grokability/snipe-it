<?php

return array(

    'deleted' => 'Poistettu assettimalli',
    'does_not_exist' => 'Malli ei löydy.',
    'no_association' => 'VAROITUS! Omaisuusmalli tälle tuotteelle on virheellinen tai puuttuu!',
    'no_association_fix' => 'Tämä tulee rikkomaam asioita oudoilla ja kauhistuttavilla tavoilla. Muokkaa tätä laitetta nyt määrittääksesi sille mallin.',
    'assoc_users'	 => 'Tämä malli on käytössä yhdellä tai useammalla laitteella joten sitä ei voida poistaa. Poista malli käytöstä kaikilta laitteilta ja yritä uudelleen. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Mallia ei luotu, yritä uudelleen.',
        'success' => 'Malli luotiin onnistuneesti.',
        'duplicate_set' => 'Tämän nimen, valmistajan ja mallinumeron omaava laitemalli on jo olemassa.',
    ),

    'update' => array(
        'error'   => 'Mallia ei päivitetty, yritä uudelleen',
        'success' => 'Malli päivitettiin onnistuneesti.',
    ),

    'delete' => array(
        'confirm'   => 'Oletko varma että haluat poistaa tämän laitemallin?',
        'error'   => 'Laitemallin poistossa tapahtui virhe. Yritä uudelleen.',
        'success' => 'Malli poistettiin onnistuneesti.'
    ),

    'restore' => array(
        'error'   		=> 'Mallia ei voitu palauttaa, yritä uudelleen',
        'success' 		=> 'Mallin palautus onnistui.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Mitään kentistä ei ollut muutettu, joten mitään ei päivitetty.',
        'success' 		=> 'Malli päivitetty onnistuneesti. |:model_count mallia päivitetty onnistuneesti.',
        'warn'          => 'Olet päivittämässä seuraavan mallin ominaisuuksia: Olet muokkaamassa seuraavien :model_count mallien ominaisuuksia:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Ei malleja valittuna, mitään ei poistettu.',
        'success' 		    => 'Malli poistettu!|:success_count mallia poistettu!',
        'success_partial' 	=> ':success_count malli(a) poistettiin, mutta :fail_count ei voitu poistaa, koska niillä on vielä niihin liittyviä laitteita.'
    ),

);
