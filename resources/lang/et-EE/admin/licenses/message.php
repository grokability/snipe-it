<?php

return [

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' => 'Vara, mida proovite selle litsentsiga siduda, ei ole olemas.',
    'owner_doesnt_match_asset' => 'Vara, mida proovite selle litsentsiga siduda, kuulub somene-le, välja arvatud rippmenüüs määratud isik.',
    'assoc_users' => 'See litsents on kasutaja jaoks praegu välja jäetud ja seda ei saa kustutada. Kontrollige kõigepealt litsentsi ja seejärel proovige uuesti kustutada.',
    'select_asset_or_person' => 'Peate valima vara või kasutaja, kuid mitte mõlemad.',
    'not_found' => 'Litsentsi ei leitud',
    'seats_available' => ':seat_count seats available',

    'create' => [
        'error' => 'Litsentsi ei loodud, proovige uuesti.',
        'success' => 'Litsents on edukalt loodud.',
    ],

    'deletefile' => [
        'error' => 'Faili pole kustutatud. Palun proovi uuesti.',
        'success' => 'Fail edukalt kustutatud.',
    ],

    'upload' => [
        'error' => 'Faili (d) pole üles laaditud. Palun proovi uuesti.',
        'success' => 'Fail (id) edukalt üles laaditud.',
        'nofiles' => 'Te ei valinud üleslaadimiseks ühtegi faili või fail, mille üritate üles laadida, on liiga suur',
        'invalidfiles' => 'Üks või mitu teie faili on liiga suured või failitüüp pole lubatud. Lubatud failitüübid on png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml ja lic.',
    ],

    'update' => [
        'error' => 'Litsentsi ei värskendatud, proovige uuesti',
        'success' => 'Litsents värskendati edukalt.',
    ],

    'delete' => [
        'confirm' => 'Kas olete kindel, et soovite selle litsentsi kustutada?',
        'error' => 'Litsentsi kustutati. Palun proovi uuesti.',
        'success' => 'Litsents kustutati edukalt.',
    ],

    'checkout' => [
        'error' => 'Litsentsi kontrollides oli küsimus. Palun proovi uuesti.',
        'success' => 'Litsents oli edukalt välja võetud',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ],

    'checkin' => [
        'error' => 'Litsentsis kontrolliti probleemi. Palun proovi uuesti.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Litsents märgiti edukalt',
    ],

];
