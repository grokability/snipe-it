<?php

return [

    'deleted' => 'Kustutatud vahendimudel',
    'does_not_exist' => 'Mudelit pole olemas.',
    'no_association' => 'HOIATUS! Selle kirje vahendimudel on vigane või puudub!',
    'no_association_fix' => 'See võib põhjustada ootamatuid ja tõsiseid tõrkeid. Muuda seda vahendit kohe ja määra sellele mudel.',
    'assoc_users' => 'See mudel on seostus ühe või mitme vahendiga ja seda ei saa kustutada. Palun kustuta vahendid ja seejärel proovi uuesti kustutada. ',
    'invalid_category_type' => 'See kategooria peab olema vahendite kategooria.',

    'create' => [
        'error' => 'Mudelit ei loodud, proovi uuesti.',
        'success' => 'Mudeli loomine õnnestus.',
        'duplicate_set' => 'Sellise nime, tootja ja mudeli numbriga mudel on juba olemas.',
    ],

    'update' => [
        'error' => 'Mudelit ei uuendatud, proovige uuesti',
        'success' => 'Mudeli uuendamine õnnestus.',
    ],

    'delete' => [
        'confirm' => 'Kas oled kindel, et soovid selle mudeli kustutada?',
        'error' => 'Mudeli kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Mudeli kustutamine õnnestus.',
    ],

    'restore' => [
        'error' => 'Mudeli taastamine ei õnnestunud, proovi uuesti',
        'success' => 'Mudeli taastamine õnnestus.',
    ],

    'bulkedit' => [
        'error' => 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
        'success' => 'Mudeli uuendamine õnnestus. |:model_count mudeli uuendamine õnnestus.',
        'warn' => 'Oled muutmas järgmise mudeli omadusi:|Oled muutmas järgmise :model_count mudeli omadusi:',

    ],

    'bulkdelete' => [
        'error' => 'Mudeleid ei valitud, nii et midagi ei kustutatud.',
        'nothing_deletable' => 'Ühtegi valitud mudelit ei saa kustutada, sest nendega on endiselt seotud vahendeid.',
        'success' => 'Mudel kustutatud!|:success_count mudelit kustutatud!',
        'success_partial' => ':success_count mudel(it) kustutati, kuid :fail_count ei õnnestunud kustutada kuna nendega on ikka veel vara seotud.',
    ],

    'merge' => [
        'min_two' => 'Vali ühendamiseks vähemalt kaks mudelit.',
        'no_target' => 'Vali enne ühendamist, milline mudel alles jätta.',
        'not_found' => 'Üht või mitut valitud mudelit ei saanud laadida. Värskenda mudelite loendit ja proovi uuesti.',
        'information' => 'Oled ühendamas :count mudelit. Vali mudel, mille soovid alles jätta. Kõik teiste mudelitega seotud vahendid määratakse valitud mudelile ümber ning seejärel kustutatakse lähtemudelid.',
        'warning' => 'Seda toimingut ei saa tagasi võtta. Ümbermääratud vahendid pärivad allesjääva mudeli kategooria, väljakomplekti ja amortisatsiooniseaded.',
        'pick_target' => 'Millise mudeli soovid alles jätta?',
        'success' => ':source_count mudelit ühendati mudeliks „:target”. :asset_count vahendit määrati ümber.',
    ],

];
