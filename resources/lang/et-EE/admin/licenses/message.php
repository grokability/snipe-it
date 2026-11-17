<?php

return [

    'does_not_exist' => 'Litsentsi pole olemas või sul puudub õigus seda vaadata.',
    'user_does_not_exist' => 'Kasutajat pole olemas või sul puudub õigus teda vaadata.',
    'asset_does_not_exist' => 'Vara, mida proovite selle litsentsiga siduda, ei ole olemas.',
    'owner_doesnt_match_asset' => 'Vara, mida proovite selle litsentsiga siduda, kuulub somene-le, välja arvatud rippmenüüs määratud isik.',
    'assoc_users' => 'See litsents on kasutaja jaoks praegu välja jäetud ja seda ei saa kustutada. Kontrollige kõigepealt litsentsi ja seejärel proovige uuesti kustutada.',
    'select_asset_or_person' => 'Peate valima vara või kasutaja, kuid mitte mõlemad.',
    'not_found' => 'Litsentsi ei leitud',
    'seats_available' => ':seat_count vaba kasutuskohta',

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
        'bulk_success' => 'Valitud litsentside kustutamine õnnestus.',
        'partial_success' => 'Litsentsi kustutamine õnnestus. Vaata lisateavet allpool. | :count litsentsi kustutamine õnnestus. Vaata lisateavet allpool.',
        'bulk_checkout_warning' => 'Litsentsil :license_name on praegu väljastatud kasutuskohti, mistõttu seda ei saa kustutada. Tagasta enne kustutamist kõik kasutuskohad.',
    ],

    'delete_with_checkin' => [
        'bulk_success' => ':count litsentsi kustutamine õnnestus pärast :seats kasutuskoha tagastamist.',
        'partial_success' => ':count litsentsi kustutamine õnnestus pärast :seats kasutuskoha tagastamist. Vaata lisateavet allpool.',
    ],

    'checkout' => [
        'error' => 'Litsentsi kontrollides oli küsimus. Palun proovi uuesti.',
        'success' => 'Litsents oli edukalt välja võetud',
        'not_enough_seats' => 'Väljastamiseks pole piisavalt vabu litsentsi kasutuskohti',
        'mismatch' => 'Määratud litsentsi kasutuskoht ei vasta litsentsile',
        'unavailable' => 'See kasutuskoht pole väljastamiseks saadaval.',
        'license_is_inactive' => 'See litsents on aegunud või lõpetatud.',
    ],

    'checkin' => [
        'error' => 'Litsentsis kontrolliti probleemi. Palun proovi uuesti.',
        'not_reassignable' => 'Kasutuskoht on kasutusel',
        'success' => 'Litsents märgiti edukalt',
    ],

    'import' => [
        'no_free_seats' => 'Litsentsil „:license” pole vabu kasutuskohti. „:target” jäi kasutuskohale määramata.',
    ],

];
