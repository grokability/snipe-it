<?php

return [

    'does_not_exist' => 'Tilamerkintää ei löydy.',
    'deleted_label' => 'Poistettu Tilamerkintä',
    'assoc_assets'	 => 'Tilamerkintä on määritetty käyttöön yhdelle tai useammalle laitteelle joten sitä ei voida poistaa. Poista tilamerkintä käytöstä kaikilta laitteilta ja yritä uudelleen. ',

    'create' => [
        'error'   => 'Tilamerkintää ei luotu, yritä uudelleen.',
        'success' => 'Tilamerkintä luotiin onnistuneesti.',
    ],

    'update' => [
        'error'   => 'Tilamerkintää ei päivitetty, yritä uudelleen',
        'success' => 'Tilamerkintä päivitettiin onnistuneesti.',
    ],

    'delete' => [
        'confirm'   => 'Oletko varma että haluat poistaa tämän tilamerkinnän?',
        'error'   => 'Tilamerkinnän poistamisessa tapahtui virhe. Yritä uudelleen.',
        'success' => 'Tilamerkintä poistettiin onnistuneesti.',
    ],

    'help' => [
        'undeployable'   => 'Näitä laitteita ei voida luovuttaa kenellekään.',
        'deployable'   => 'Nämä laitteet voidaan luovuttaa. Kun ne on luovutettu, ne saavat tilamerkinnän <i class="fas fa-circle text-blue"></i> <strong>Käyttöönotettu</strong>.',
        'archived'   => 'Näitä laitteita ei voi luovuttaa, ja ne näkyvät vain Arkistoitu-näkymässä. Tämä on hyödyllistä, kun säilytetään tietoja laitteista budjetointiin / historiallisiin tarkoituksiin.',
        'pending'   => 'Näitä laitteita ei voida vielä antaa kenellekään. Käytä vaikka laitteille jotka ovat korjauksessa, mutta joiden odotetaan palaavan käyttöön.',
    ],

];
