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
        'name' => 'Superpoužívateľ',
        'note' => 'Určuje, či má používateľ plný prístup ku všetkým aspektom administrácie. Toto nastavenie prepíše VŠETKY špecifickejšie a reštriktívnejšie povolenia v celom systéme. ',
    ],
    'admin' => [
        'name' => 'Prístup pre správcu',
        'note' => 'Určuje, či má používateľ prístup k väčšine aspektov systému OKREM nastavení správcu systému. Títo používatelia budú môcť spravovať používateľov, lokality, kategórie atď., ale SÚ obmedzení plnou podporou viacerých spoločností, ak je povolená.',
    ],

    'import' => [
        'name' => 'CSV import',
        'note' => 'To umožní používateľom importovať aj keď je prístup k používateľom, majetkom atď. inde zamietnutý.',
    ],

    'reports' => [
        'name' => 'Prístup k reportom',
        'note' => 'Určuje, či má používateľ prístup k sekcii Reporty v aplikácii.',
    ],

    'assets' => [
        'name' => 'Položky majetku',
        'note' => 'Udeľuje prístup k sekcii Majetky v aplikácii. ',
    ],

    'assetsview' => [
        'name' => 'Zobraziť Majetky',
        'note' => 'Upozorňujeme, že používatelia s týmto oprávnením budú môcť zobraziť (nie upravovať ani odstraňovať) aj súbory nahrané k modelu majetku. Uľahčuje to zdieľanie bežných dokumentov, ako sú používateľské príručky, naprieč majetkom bez nutnosti nahrávať ich ku každému majetku a bez nutnosti udeľovať používateľovi oprávnenie upravovať súbory majetku. Používatelia s týmto oprávnením budú môcť zobraziť aj históriu úprav a prevzatí.',
    ],

    'assetscreate' => [
        'name' => 'Vytvoriť nové majetky',
    ],

    'assetsedit' => [
        'name' => 'Upraviť majetky',
    ],

    'assetsdelete' => [
        'name' => 'Odstrániť majetky',
    ],

    'assetscheckin' => [
        'name' => 'Prevziať',
        'note' => 'Prevziať majetky, ktoré sú momentálne odovzdané, späť do inventára.',
    ],

    'assetscheckout' => [
        'name' => 'Odovzdať',
        'note' => 'Odovzdať majetok z inventára.',
    ],

    'assetsaudit' => [
        'name' => 'Audit aktív',
        'note' => 'Umožňuje používateľovi označiť majetok ako fyzicky inventarizovaný.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Zobraziť položky na vyžiadanie',
        'note' => 'Umožňuje používateľovi zobraziť položky označené ako dostupné na vyžiadanie.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Zobraziť šifrované vlastné polia',
        'note' => 'Umožňuje používateľovi zobraziť a upraviť zašifrované vlastné polia v prvkoch.',
    ],

    'accessories' => [
        'name' => 'Príslušenstvo',
        'note' => 'Umožňuje prístup do sekcie Príslušenstvo v aplikácii.',
    ],

    'accessoriesview' => [
        'name' => 'Zobraziť príslušenstvo',
    ],
    'accessoriescreate' => [
        'name' => 'Vytvoriť nové príslušenstvo',
    ],
    'accessoriesedit' => [
        'name' => 'Upraviť príslušenstvo',
    ],
    'accessoriesdelete' => [
        'name' => 'Odstrániť príslušenstvo',
    ],
    'accessoriescheckout' => [
        'name' => 'Odovzdať príslušenstvo',
        'note' => 'Priradiť príslušenstvo odovzdaním.',
    ],
    'accessoriescheckin' => [
        'name' => 'Prevziať príslušenstvo',
        'note' => 'Prevziať späť do skladu príslušenstvo, ktoré je momentálne odovzdané.',
    ],
    'accessoriesfiles' => [
        'name' => 'Spravovať súbory príslušenstva',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k príslušenstvu. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'assetsfiles' => [
        'name' => 'Spravovať súbory majetku',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k majetku. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'usersfiles' => [
        'name' => 'Spravovať súbory používateľov',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k používateľom. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'modelsfiles' => [
        'name' => 'Spravovať súbory modelov',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k modelom majetku na obrazovke zobrazenia modelu aj majetku. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'departmentsfiles' => [
        'name' => 'Spravovať súbory oddelení',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k oddeleniam. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'suppliersfiles' => [
        'name' => 'Spravovať súbory dodávateľov',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k dodávateľom. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'locationsfiles' => [
        'name' => 'Spravovať súbory lokalít',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k lokalitám. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'companiesfiles' => [
        'name' => 'Spravovať súbory spoločností',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k spoločnostiam. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'consumablesfiles' => [
        'name' => 'Spravovať súbory spotrebného materiálu',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k spotrebnému materiálu. (Má to zmysel iba s oprávnením na zobrazenie alebo vyšším.)',
    ],

    'consumables' => [
        'name' => 'Spotrebný materiál',
        'note' => 'Udeľuje prístup do sekcie Spotrebný materiál v aplikácii.',
    ],
    'consumablesview' => [
        'name' => 'Zobraziť spotrebný materiál',
    ],
    'consumablescreate' => [
        'name' => 'Vytvárať nový spotrebný materiál',
    ],
    'consumablesedit' => [
        'name' => 'Upravovať spotrebný materiál',
    ],
    'consumablesdelete' => [
        'name' => 'Odstraňovať spotrebný materiál',
    ],
    'consumablescheckout' => [
        'name' => 'Odovzdávať spotrebný materiál',
        'note' => 'Priraďovať spotrebný materiál zo skladu jeho odovzdaním.',
    ],

    'licenses' => [
        'name' => 'Licencie',
        'note' => 'Udeľuje prístup do sekcie Licencie v aplikácii.',
    ],
    'licensesview' => [
        'name' => 'Zobraziť licencie',
    ],
    'licensescreate' => [
        'name' => 'Vytvárať nové licencie',
    ],
    'licensesedit' => [
        'name' => 'Upravovať licencie',
    ],
    'licensesdelete' => [
        'name' => 'Odstraňovať licencie',
    ],
    'licensescheckout' => [
        'name' => 'Priraďovať licencie',
        'note' => 'Umožňuje používateľovi priraďovať licencie k majetku alebo používateľom.',
    ],
    'licensescheckin' => [
        'name' => 'Rušiť priradenie licencií',
        'note' => 'Umožňuje používateľovi zrušiť priradenie licencií k majetku alebo používateľom.',
    ],
    'licensesfiles' => [
        'name' => 'Spravovať súbory licencií',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené k licenciám.',
    ],
    'componentsfiles' => [
        'name' => 'Spravovať súbory komponentov',
        'note' => 'Umožňuje používateľovi nahrávať, sťahovať a odstraňovať súbory priradené ku komponentom.',
    ],

    'licenseskeys' => [
        'name' => 'Spravovať licenčné kľúče',
        'note' => 'Umožňuje používateľovi zobraziť produktové kľúče priradené k licenciám.',
    ],
    'components' => [
        'name' => 'Komponenty',
        'note' => 'Udeľuje prístup do sekcie Komponenty v aplikácii.',
    ],
    'componentsview' => [
        'name' => 'Zobraziť komponenty',
    ],
    'componentscreate' => [
        'name' => 'Vytvárať nové komponenty',
    ],
    'componentsedit' => [
        'name' => 'Upravovať komponenty',
    ],
    'componentsdelete' => [
        'name' => 'Odstraňovať komponenty',
    ],

    'componentscheckout' => [
        'name' => 'Odovzdávať komponenty',
        'note' => 'Priraďovať komponenty zo skladu ich odovzdaním.',
    ],
    'componentscheckin' => [
        'name' => 'Preberať komponenty',
        'note' => 'Prevziať späť do skladu komponenty, ktoré sú momentálne odovzdané.',
    ],
    'kits' => [
        'name' => 'Predvolené sady',
        'note' => 'Udeľuje prístup do sekcie Preddefinované sady v aplikácii.',
    ],
    'kitsview' => [
        'name' => 'Zobraziť preddefinované sady',
    ],
    'kitscreate' => [
        'name' => 'Vytvárať nové preddefinované sady',
    ],
    'kitsedit' => [
        'name' => 'Upravovať preddefinované sady',
    ],
    'kitsdelete' => [
        'name' => 'Odstraňovať preddefinované sady',
    ],
    'users' => [
        'name' => 'Používatelia',
        'note' => 'Udeľuje prístup do sekcie Používatelia v aplikácii.',
    ],
    'usersview' => [
        'name' => 'Zobraziť používateľa',
        'note' => 'Používatelia s týmto oprávnením budú môcť zobraziť aj históriu úprav a prevzatí. Prístup k súborom nahraným k používateľovi sa riadi samostatne oprávnením Spravovať súbory používateľov.',
    ],
    'userscreate' => [
        'name' => 'Vytvárať nových používateľov',
    ],
    'usersedit' => [
        'name' => 'Upravovať používateľov',
    ],
    'usersdelete' => [
        'name' => 'Odstraňovať používateľov',
    ],
    'models' => [
        'name' => 'Modely',
        'note' => 'Udeľuje prístup do sekcie Modely v aplikácii.',
    ],
    'modelsview' => [
        'name' => 'Zobraziť modely',
    ],

    'modelscreate' => [
        'name' => 'Vytvárať nové modely',
    ],
    'modelsedit' => [
        'name' => 'Upravovať modely',
    ],
    'modelsdelete' => [
        'name' => 'Odstraňovať modely',
    ],
    'categories' => [
        'name' => 'Kategórie',
        'note' => 'Udeľuje prístup do sekcie Kategórie v aplikácii.',
    ],
    'categoriesview' => [
        'name' => 'Zobraziť kategórie',
    ],
    'categoriescreate' => [
        'name' => 'Vytvárať nové kategórie',
    ],
    'categoriesedit' => [
        'name' => 'Upravovať kategórie',
    ],
    'categoriesdelete' => [
        'name' => 'Odstraňovať kategórie',
    ],
    'departments' => [
        'name' => 'Oddelenia',
        'note' => 'Udeľuje prístup do sekcie Oddelenia v aplikácii.',
    ],
    'departmentsview' => [
        'name' => 'Zobraziť oddelenia',
    ],
    'departmentscreate' => [
        'name' => 'Vytvárať nové oddelenia',
    ],
    'departmentsedit' => [
        'name' => 'Upravovať oddelenia',
    ],
    'departmentsdelete' => [
        'name' => 'Odstraňovať oddelenia',
    ],
    'locations' => [
        'name' => 'Lokality',
        'note' => 'Udeľuje prístup do sekcie Lokality v aplikácii.',
    ],
    'locationsview' => [
        'name' => 'Zobraziť lokality',
    ],
    'locationscreate' => [
        'name' => 'Vytvárať nové lokality',
    ],
    'locationsedit' => [
        'name' => 'Upravovať lokality',
    ],
    'locationsdelete' => [
        'name' => 'Odstraňovať lokality',
    ],
    'status-labels' => [
        'name' => 'Stavy',
        'note' => 'Udeľuje prístup do sekcie Stavy v aplikácii, ktorú používa majetok.',
    ],
    'statuslabelsview' => [
        'name' => 'Zobraziť stavy',
    ],
    'statuslabelscreate' => [
        'name' => 'Vytvárať nové stavy',
    ],
    'statuslabelsedit' => [
        'name' => 'Upravovať stavy',
    ],
    'statuslabelsdelete' => [
        'name' => 'Odstraňovať stavy',
    ],
    'custom-fields' => [
        'name' => 'Vlastné polia',
        'note' => 'Udeľuje prístup do sekcie Vlastné polia v aplikácii, ktorú používa majetok.',
    ],
    'customfieldsview' => [
        'name' => 'Zobraziť vlastné polia',
    ],
    'customfieldscreate' => [
        'name' => 'Vytvárať nové vlastné polia',
    ],
    'customfieldsedit' => [
        'name' => 'Upravovať vlastné polia',
    ],
    'customfieldsdelete' => [
        'name' => 'Odstraňovať vlastné polia',
    ],
    'suppliers' => [
        'name' => 'Dodávatelia',
        'note' => 'Udeľuje prístup do sekcie Dodávatelia v aplikácii.',
    ],
    'suppliersview' => [
        'name' => 'Zobraziť dodávateľov',
    ],
    'supplierscreate' => [
        'name' => 'Vytvárať nových dodávateľov',
    ],
    'suppliersedit' => [
        'name' => 'Upravovať dodávateľov',
    ],
    'suppliersdelete' => [
        'name' => 'Odstraňovať dodávateľov',
    ],
    'manufacturers' => [
        'name' => 'Výrobcovia',
        'note' => 'Udeľuje prístup do sekcie Výrobcovia v aplikácii.',
    ],
    'manufacturersview' => [
        'name' => 'Zobraziť výrobcov',
    ],
    'manufacturerscreate' => [
        'name' => 'Vytvárať nových výrobcov',
    ],
    'manufacturersedit' => [
        'name' => 'Upravovať výrobcov',
    ],
    'manufacturersdelete' => [
        'name' => 'Odstraňovať výrobcov',
    ],
    'companies' => [
        'name' => 'Spoločnosti',
        'note' => 'Udeľuje prístup do sekcie Spoločnosti v aplikácii.',
    ],
    'companiesview' => [
        'name' => 'Zobraziť spoločnosti',
    ],
    'companiescreate' => [
        'name' => 'Vytvárať nové spoločnosti',
    ],
    'companiesedit' => [
        'name' => 'Upravovať spoločnosti',
    ],
    'companiesdelete' => [
        'name' => 'Odstraňovať spoločnosti',
    ],
    'user-self-accounts' => [
        'name' => 'Vlastné účty používateľov',
        'note' => 'Umožňuje používateľom bez administrátorských práv spravovať niektoré aspekty vlastného používateľského účtu.',
    ],
    'selftwo-factor' => [
        'name' => 'Spravovať dvojfaktorové overenie',
        'note' => 'Umožňuje používateľom zapínať, vypínať a spravovať dvojfaktorové overenie vo vlastnom účte.',
    ],
    'selfapi' => [
        'name' => 'Spravovať API tokeny',
        'note' => 'Umožňuje používateľom vytvárať, zobrazovať a rušiť vlastné API tokeny. Tokeny používateľa budú mať rovnaké oprávnenia ako používateľ, ktorý ich vytvoril.',
    ],
    'selfedit-location' => [
        'name' => 'Upraviť lokalitu',
        'note' => 'Umožňuje používateľom upraviť lokalitu priradenú k ich vlastnému používateľskému účtu.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Samoobslužné odovzdanie majetku',
        'note' => 'Umožňuje používateľom odovzdať si majetok sami sebe bez zásahu administrátora.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Zobraziť nákupnú cenu',
        'note' => 'Umožňuje používateľom zobraziť nákupnú cenu položiek v zobrazení ich účtu.',
    ],

    'depreciations' => [
        'name' => 'Správa odpisov',
        'note' => 'Umožňuje používateľom spravovať a zobrazovať podrobnosti o odpisoch majetku.',
    ],
    'depreciationsview' => [
        'name' => 'Zobraziť podrobnosti odpisov',
    ],
    'depreciationsedit' => [
        'name' => 'Upravovať nastavenia odpisov',
    ],
    'depreciationsdelete' => [
        'name' => 'Odstraňovať záznamy odpisov',
    ],
    'depreciationscreate' => [
        'name' => 'Vytvárať záznamy odpisov',
    ],

    'grant_all' => 'Udeliť všetky oprávnenia pre :area',
    'deny_all' => 'Zamietnuť všetky oprávnenia pre :area',
    'inherit_all' => 'Zdediť všetky oprávnenia pre :area zo skupín oprávnení',
    'grant' => 'Udeliť oprávnenie pre :area',
    'deny' => 'Zamietnuť oprávnenie pre :area',
    'inherit' => 'Zdediť oprávnenie pre :area zo skupín oprávnení',
    'use_groups' => 'Pre jednoduchšiu správu dôrazne odporúčame namiesto priraďovania jednotlivých oprávnení používať skupiny oprávnení.',

];
