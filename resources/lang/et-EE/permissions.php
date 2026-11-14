<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    | The following language lines are used in the user permissions system.
    | Each permission has a 'name' and a 'note' that describes
    | the permission in detail.
    |
    | DO NOT edit the keys (left-hand side) of each permission as these are
    | used throughout the system for translations.
    |---------------------------------------------------------------------------
    */

    'superuser' => [
        'name' => 'Superkasutaja',
        'note' => 'Määrab, kas kasutajal on täielik juurdepääs kõigile haldusfunktsioonidele. See seade tühistab KÕIK täpsemad ja piiravamad õigused kogu süsteemis. ',
    ],
    'admin' => [
        'name' => 'Administraatori juurdepääs',
        'note' => 'Määrab, kas kasutajal on juurdepääs enamikule süsteemi funktsioonidele, VÄLJA ARVATUD süsteemi haldusseadetele. Need kasutajad saavad hallata kasutajaid, asukohti, kategooriaid jne, kuid kui täielik mitme ettevõtte tugi on lubatud, kehtivad neile selle piirangud.',
    ],

    'import' => [
        'name' => 'CSV-import',
        'note' => 'Lubab kasutajatel importida ka siis, kui juurdepääs kasutajatele, vahenditele jne on mujal keelatud.',
    ],

    'reports' => [
        'name' => 'Juurdepääs aruannetele',
        'note' => 'Määrab, kas kasutajal on juurdepääs rakenduse aruannete jaotisele.',
    ],

    'assets' => [
        'name' => 'Vahendid',
        'note' => 'Annab juurdepääsu rakenduse vahendite jaotisele. ',
    ],

    'assetsview' => [
        'name' => 'Vaata vahendeid',
        'note' => 'Pane tähele, et selle õigusega kasutajad saavad vaadata ka vahendimudelile üles laaditud faile (kuid mitte neid muuta ega kustutada). Nii on lihtsam jagada vahendite vahel ühiseid dokumente, näiteks kasutusjuhendeid, ilma neid igale vahendile eraldi üles laadimata ja kasutajale vahendifailide muutmise õigust andmata. Selle õigusega kasutajad saavad vaadata ka muutmis- ja tagastamisajalugu.',
    ],

    'assetscreate' => [
        'name' => 'Loo uusi vahendeid',
    ],

    'assetsedit' => [
        'name' => 'Muuda vahendeid',
    ],

    'assetsdelete' => [
        'name' => 'Kustuta vahendeid',
    ],

    'assetscheckin' => [
        'name' => 'Tagasta',
        'note' => 'Tagasta praegu väljastatud vahendid lattu.',
    ],

    'assetscheckout' => [
        'name' => 'Väljasta',
        'note' => 'Määra laos olevad vahendid saajatele neid väljastades.',
    ],

    'assetsaudit' => [
        'name' => 'Auditeeri vahendeid',
        'note' => 'Lubab kasutajal märkida vahendi füüsiliselt inventeerituks.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Vaata taotletavaid kirjeid',
        'note' => 'Lubab kasutajal vaadata kirjeid, mis on märgitud taotletavaks.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Vaata krüpteeritud kohandatud välju',
        'note' => 'Lubab kasutajal vaadata ja muuta vahendite krüpteeritud kohandatud välju.',
    ],

    'accessories' => [
        'name' => 'Tarvikud',
        'note' => 'Annab juurdepääsu rakenduse tarvikute jaotisele.',
    ],

    'accessoriesview' => [
        'name' => 'Vaata tarvikuid',
    ],
    'accessoriescreate' => [
        'name' => 'Loo uusi tarvikuid',
    ],
    'accessoriesedit' => [
        'name' => 'Muuda tarvikuid',
    ],
    'accessoriesdelete' => [
        'name' => 'Kustuta tarvikuid',
    ],
    'accessoriescheckout' => [
        'name' => 'Väljasta tarvikuid',
        'note' => 'Määra laos olevad tarvikud saajatele neid väljastades.',
    ],
    'accessoriescheckin' => [
        'name' => 'Tagasta tarvikuid',
        'note' => 'Tagasta praegu väljastatud tarvikud lattu.',
    ],
    'accessoriesfiles' => [
        'name' => 'Halda tarvikute faile',
        'note' => 'Lubab kasutajal tarvikutega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'assetsfiles' => [
        'name' => 'Halda vahendite faile',
        'note' => 'Lubab kasutajal vahenditega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'usersfiles' => [
        'name' => 'Halda kasutajate faile',
        'note' => 'Lubab kasutajal kasutajatega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'modelsfiles' => [
        'name' => 'Halda mudelite faile',
        'note' => 'Lubab kasutajal vahendimudelitega seotud faile üles ja alla laadida ning kustutada nii mudeli- kui ka vahendivaates. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'departmentsfiles' => [
        'name' => 'Halda osakondade faile',
        'note' => 'Lubab kasutajal osakondadega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'suppliersfiles' => [
        'name' => 'Halda tarnijate faile',
        'note' => 'Lubab kasutajal tarnijatega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'locationsfiles' => [
        'name' => 'Halda asukohtade faile',
        'note' => 'Lubab kasutajal asukohtadega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'companiesfiles' => [
        'name' => 'Halda ettevõtete faile',
        'note' => 'Lubab kasutajal ettevõtetega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'consumablesfiles' => [
        'name' => 'Halda kulumaterjalide faile',
        'note' => 'Lubab kasutajal kulumaterjalidega seotud faile üles ja alla laadida ning kustutada. (See on mõttekas ainult vaatamisõiguse või kõrgemate õigustega.)',
    ],

    'consumables' => [
        'name' => 'Kulumaterjalid',
        'note' => 'Annab juurdepääsu rakenduse kulumaterjalide jaotisele.',
    ],
    'consumablesview' => [
        'name' => 'Vaata kulumaterjale',
    ],
    'consumablescreate' => [
        'name' => 'Loo uusi kulumaterjale',
    ],
    'consumablesedit' => [
        'name' => 'Muuda kulumaterjale',
    ],
    'consumablesdelete' => [
        'name' => 'Kustuta kulumaterjale',
    ],
    'consumablescheckout' => [
        'name' => 'Väljasta kulumaterjale',
        'note' => 'Määra laos olevad kulumaterjalid saajatele neid väljastades.',
    ],

    'licenses' => [
        'name' => 'Litsentsid',
        'note' => 'Annab juurdepääsu rakenduse litsentside jaotisele.',
    ],
    'licensesview' => [
        'name' => 'Vaata litsentse',
    ],
    'licensescreate' => [
        'name' => 'Loo uusi litsentse',
    ],
    'licensesedit' => [
        'name' => 'Muuda litsentse',
    ],
    'licensesdelete' => [
        'name' => 'Kustuta litsentse',
    ],
    'licensescheckout' => [
        'name' => 'Määra litsentse',
        'note' => 'Lubab kasutajal määrata litsentse vahenditele või kasutajatele.',
    ],
    'licensescheckin' => [
        'name' => 'Eemalda litsentside määrangud',
        'note' => 'Lubab kasutajal eemaldada litsentside määranguid vahenditelt või kasutajatelt.',
    ],
    'licensesfiles' => [
        'name' => 'Halda litsentside faile',
        'note' => 'Lubab kasutajal litsentsidega seotud faile üles ja alla laadida ning kustutada.',
    ],
    'componentsfiles' => [
        'name' => 'Halda komponentide faile',
        'note' => 'Lubab kasutajal komponentidega seotud faile üles ja alla laadida ning kustutada.',
    ],

    'licenseskeys' => [
        'name' => 'Halda litsentsivõtmeid',
        'note' => 'Lubab kasutajal vaadata litsentsidega seotud tootevõtmeid.',
    ],
    'components' => [
        'name' => 'Komponendid',
        'note' => 'Annab juurdepääsu rakenduse komponentide jaotisele.',
    ],
    'componentsview' => [
        'name' => 'Vaata komponente',
    ],
    'componentscreate' => [
        'name' => 'Loo uusi komponente',
    ],
    'componentsedit' => [
        'name' => 'Muuda komponente',
    ],
    'componentsdelete' => [
        'name' => 'Kustuta komponente',
    ],

    'componentscheckout' => [
        'name' => 'Väljasta komponente',
        'note' => 'Määra laos olevad komponendid saajatele neid väljastades.',
    ],
    'componentscheckin' => [
        'name' => 'Tagasta komponente',
        'note' => 'Tagasta praegu väljastatud komponendid lattu.',
    ],
    'kits' => [
        'name' => 'Eelnevalt määratud komplektid',
        'note' => 'Annab juurdepääsu rakenduse eelmääratud komplektide jaotisele.',
    ],
    'kitsview' => [
        'name' => 'Vaata eelmääratud komplekte',
    ],
    'kitscreate' => [
        'name' => 'Loo uusi eelmääratud komplekte',
    ],
    'kitsedit' => [
        'name' => 'Muuda eelmääratud komplekte',
    ],
    'kitsdelete' => [
        'name' => 'Kustuta eelmääratud komplekte',
    ],
    'users' => [
        'name' => 'Kasutajad',
        'note' => 'Annab juurdepääsu rakenduse kasutajate jaotisele.',
    ],
    'usersview' => [
        'name' => 'Vaata kasutajaid',
        'note' => 'Selle õigusega kasutajad saavad vaadata ka muutmis- ja tagastamisajalugu. Kasutajale üles laaditud failidele juurdepääsu reguleerib eraldi õigus „Halda kasutajate faile”.',
    ],
    'userscreate' => [
        'name' => 'Loo uusi kasutajaid',
    ],
    'usersedit' => [
        'name' => 'Muuda kasutajaid',
    ],
    'usersdelete' => [
        'name' => 'Kustuta kasutajaid',
    ],
    'models' => [
        'name' => 'Mudelid',
        'note' => 'Annab juurdepääsu rakenduse mudelite jaotisele.',
    ],
    'modelsview' => [
        'name' => 'Vaata mudeleid',
    ],

    'modelscreate' => [
        'name' => 'Loo uusi mudeleid',
    ],
    'modelsedit' => [
        'name' => 'Muuda mudeleid',
    ],
    'modelsdelete' => [
        'name' => 'Kustuta mudeleid',
    ],
    'categories' => [
        'name' => 'Kategooriad',
        'note' => 'Annab juurdepääsu rakenduse kategooriate jaotisele.',
    ],
    'categoriesview' => [
        'name' => 'Vaata kategooriaid',
    ],
    'categoriescreate' => [
        'name' => 'Loo uusi kategooriaid',
    ],
    'categoriesedit' => [
        'name' => 'Muuda kategooriaid',
    ],
    'categoriesdelete' => [
        'name' => 'Kustuta kategooriaid',
    ],
    'departments' => [
        'name' => 'Osakonnad',
        'note' => 'Annab juurdepääsu rakenduse osakondade jaotisele.',
    ],
    'departmentsview' => [
        'name' => 'Vaata osakondi',
    ],
    'departmentscreate' => [
        'name' => 'Loo uusi osakondi',
    ],
    'departmentsedit' => [
        'name' => 'Muuda osakondi',
    ],
    'departmentsdelete' => [
        'name' => 'Kustuta osakondi',
    ],
    'locations' => [
        'name' => 'Asukohad',
        'note' => 'Annab juurdepääsu rakenduse asukohtade jaotisele.',
    ],
    'locationsview' => [
        'name' => 'Vaata asukohti',
    ],
    'locationscreate' => [
        'name' => 'Loo uusi asukohti',
    ],
    'locationsedit' => [
        'name' => 'Muuda asukohti',
    ],
    'locationsdelete' => [
        'name' => 'Kustuta asukohti',
    ],
    'status-labels' => [
        'name' => 'Staatuse sildid',
        'note' => 'Annab juurdepääsu rakenduse olekusiltide jaotisele, mida kasutatakse vahendite jaoks.',
    ],
    'statuslabelsview' => [
        'name' => 'Vaata olekusilte',
    ],
    'statuslabelscreate' => [
        'name' => 'Loo uusi olekusilte',
    ],
    'statuslabelsedit' => [
        'name' => 'Muuda olekusilte',
    ],
    'statuslabelsdelete' => [
        'name' => 'Kustuta olekusilte',
    ],
    'custom-fields' => [
        'name' => 'Kohandatud väljad',
        'note' => 'Annab juurdepääsu rakenduse kohandatud väljade jaotisele, mida kasutatakse vahendite jaoks.',
    ],
    'customfieldsview' => [
        'name' => 'Vaata kohandatud välju',
    ],
    'customfieldscreate' => [
        'name' => 'Loo uusi kohandatud välju',
    ],
    'customfieldsedit' => [
        'name' => 'Muuda kohandatud välju',
    ],
    'customfieldsdelete' => [
        'name' => 'Kustuta kohandatud välju',
    ],
    'suppliers' => [
        'name' => 'Tarnijad',
        'note' => 'Annab juurdepääsu rakenduse tarnijate jaotisele.',
    ],
    'suppliersview' => [
        'name' => 'Vaata tarnijaid',
    ],
    'supplierscreate' => [
        'name' => 'Loo uusi tarnijaid',
    ],
    'suppliersedit' => [
        'name' => 'Muuda tarnijaid',
    ],
    'suppliersdelete' => [
        'name' => 'Kustuta tarnijaid',
    ],
    'manufacturers' => [
        'name' => 'Tootjad',
        'note' => 'Annab juurdepääsu rakenduse tootjate jaotisele.',
    ],
    'manufacturersview' => [
        'name' => 'Vaata tootjaid',
    ],
    'manufacturerscreate' => [
        'name' => 'Loo uusi tootjaid',
    ],
    'manufacturersedit' => [
        'name' => 'Muuda tootjaid',
    ],
    'manufacturersdelete' => [
        'name' => 'Kustuta tootjaid',
    ],
    'companies' => [
        'name' => 'Ettevõtted',
        'note' => 'Annab juurdepääsu rakenduse ettevõtete jaotisele.',
    ],
    'companiesview' => [
        'name' => 'Vaata ettevõtteid',
    ],
    'companiescreate' => [
        'name' => 'Loo uusi ettevõtteid',
    ],
    'companiesedit' => [
        'name' => 'Muuda ettevõtteid',
    ],
    'companiesdelete' => [
        'name' => 'Kustuta ettevõtteid',
    ],
    'user-self-accounts' => [
        'name' => 'Kasutajate iseteeninduskontod',
        'note' => 'Lubab administraatoriõigusteta kasutajatel hallata oma kasutajakonto teatud andmeid ja seadeid.',
    ],
    'selftwo-factor' => [
        'name' => 'Halda kaheastmelist autentimist',
        'note' => 'Lubab kasutajatel oma kontol kaheastmelist autentimist lubada, keelata ja hallata.',
    ],
    'selfapi' => [
        'name' => 'Halda API juurdepääsutõendeid',
        'note' => 'Lubab kasutajatel oma API juurdepääsutõendeid luua, vaadata ja tühistada. Tõenditel on samad õigused kui need loonud kasutajal.',
    ],
    'selfedit-location' => [
        'name' => 'Muuda asukohta',
        'note' => 'Lubab kasutajatel muuta oma kasutajakontoga seotud asukohta.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Väljasta vahendeid iseendale',
        'note' => 'Lubab kasutajatel väljastada vahendeid iseendale ilma administraatori sekkumiseta.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Vaata ostuhinda',
        'note' => 'Lubab kasutajatel oma kontovaates näha kirjete ostuhinda.',
    ],

    'depreciations' => [
        'name' => 'Amortisatsiooni haldamine',
        'note' => 'Lubab kasutajatel hallata ja vaadata vahendite amortisatsiooniandmeid.',
    ],
    'depreciationsview' => [
        'name' => 'Vaata amortisatsiooniandmeid',
    ],
    'depreciationsedit' => [
        'name' => 'Muuda amortisatsiooniseadeid',
    ],
    'depreciationsdelete' => [
        'name' => 'Kustuta amortisatsioonikirjeid',
    ],
    'depreciationscreate' => [
        'name' => 'Loo amortisatsioonikirjeid',
    ],

    'grant_all' => 'Anna kõik õigused valdkonnas :area',
    'deny_all' => 'Keela kõik õigused valdkonnas :area',
    'inherit_all' => 'Päri kõik valdkonna :area õigused õigusrühmadest',
    'grant' => 'Anna õigus valdkonnas :area',
    'deny' => 'Keela õigus valdkonnas :area',
    'inherit' => 'Päri valdkonna :area õigus õigusrühmadest',
    'use_groups' => 'Haldamise lihtsustamiseks soovitame tungivalt kasutada üksikõiguste määramise asemel õigusrühmi.',

];
