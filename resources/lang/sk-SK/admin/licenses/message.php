<?php

return array(

    'does_not_exist' => 'Licencia neexistuje alebo nemáte oprávnenie na jej zobrazenie.',
    'user_does_not_exist' => 'Používateľ neexistuje alebo nemáte oprávnenie na jeho zobrazenie.',
    'asset_does_not_exist' 	=> 'Majetok, ktorý sa pokúšate spojiť s touto licenciou, neexistuje.',
    'owner_doesnt_match_asset' => 'Majetok, ktorý sa snažíte spojiť s touto licenciou je vlastné niekým iným ako zvolenou osobou.',
    'assoc_users'	 => 'Táto licencia je aktuálne priradená používateľovi a preto nemôže byť zmazaná. Prosím odoberte najprv licenciou používateľovi a následne skúste zmazať znovu. ',
    'select_asset_or_person' => 'Musíte vybrať majetok alebo používatelia, ale nie oboje.',
    'not_found' => 'Licencia nebol nájdená',
    'seats_available' => ':seat_count miest k dispozícií',


    'create' => array(
        'error'   => 'Licencia nebola pridaná, prosím skúste znovu.',
        'success' => 'Licencia bol úspešne pridaná.'
    ),

    'deletefile' => array(
        'error'   => 'Súbor nebol odstránený. Prosím skúste znovu.',
        'success' => 'Súbor bol úspešne odstránený.',
    ),

    'upload' => array(
        'error'   => 'Súbor(y) sa nepodarilo nahrať. Skúste prosím znovu.',
        'success' => 'Súbor(y) boli úspešne nahraté.',
        'nofiles' => 'Nevybrali ste žiadne súbory na nahranie alebo sa pokúšate nahrať príliž veľký súbor',
        'invalidfiles' => 'Jeden alebo viacero súborov je príliš veľkých alebo nie su podporované. Podporované typy súborov sú png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, and lic.',
    ),

    'update' => array(
        'error'   => 'Licencia nebola aktualizovaná, skúste prosím znovu',
        'success' => 'Licencia bola úspešne aktualizovaná.'
    ),

    'delete' => array(
        'confirm'   => 'Ste si istý, že chcete odstrániť túto licenciu?',
        'error'   => 'Pri odstraňovaní licencie nastala chyba. Skúste prosím znovu.',
        'success' => 'Licencia bola úspešne odstránená.'
    ),

    'checkout' => array(
        'error'   => 'Pri odovzdaní licencie nastala chyba. Skúste prosím znovu.',
        'success' => 'Licencia bola úspešne odovzdaná',
        'not_enough_seats' => 'Nedostatok licenčných miest pre odovzdanie',
        'mismatch' => 'Poskytnutý licenčný slot sa nezhoduje s licenciou',
        'unavailable' => 'Tento slot nie je dostupné pre odovzdanie.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Pri prevzatí licencie nastala chyba. Skúste prosím znovu.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licencia bola úspešne prevzatá'
    ),

);
