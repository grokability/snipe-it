<?php

return array(

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

    "superuser" => [
        'name' => 'Super naudotojas',
        'note'       => 'Nustato, ar naudotojas turi visišką prieigą prie visų administratoriaus funkcijų. Šis nustatymas pakeičia VISAS konkretesnes ir ribojančias teises visoje sistemoje. ',
    ],
    'admin' => [
        'name' => 'Administratoriaus prieiga',
        'note'       => 'Nustato, ar naudotojas turi prieigą prie daugumos sistemos funkcijų, IŠSKYRUS sistemos administratoriaus nustatymus. Šie naudotojai galės valdyti naudotojus, vietas, kategorijas ir kt., tačiau jų prieiga YRA ribojama Pilno kelių įmonių palaikymo, kai jis įjungtas.',
    ],

    'import' => [
        'name' => 'CSV importavimas',
        'note'       => 'Tai leis naudotojams importuoti duomenis, net jei prieiga prie naudotojų, turto ir pan. visur kitur yra draudžiama.',
    ],

    'reports' => [
        'name' => 'Prieiga prie ataskaitų',
        'note'       => 'Nustato, ar naudotojas turi prieigą prie programos Ataskaitų skilties.',
    ],

    'assets' =>
        [
            'name' => 'Turtas',
            'note' => 'Suteikia prieigą prie programos Turto skilties.',
    ],

    'assetsview' => [
        'name' => 'Peržiūrėti turtą',
    ],

    'assetscreate' => [
        'name' => 'Sukurti turtą',
    ],

    'assetsedit' => [
        'name' => 'Redaguoti turtą',
    ],

    'assetsdelete' => [
        'name' => 'Ištrinti turtą',
    ],

    'assetscheckin' => [
        'name' => 'Paimti',
        'note' => 'Paimti šiuo metu išduotą turtą ir grąžinti atgal į inventorių.',
    ],

    'assetscheckout' => [
        'name' => 'Išduoti',
        'note' => 'Priskirti inventoriuje esantį turtą, jį išduodant.',
    ],

    'assetsaudit' => [
        'name' => 'Audituoti turtą',
        'note' => 'Leidžia naudotojui pažymėti turtą kaip fiziškai inventorizuotą.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Peržiūrėti užsakomą turtą',
        'note' => 'Leidžia naudotojui peržiūrėti turtą, kuris pažymėtas kaip užsakomas.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Peržiūrėti šifruotus pritaikytus laukus',
        'note' => 'Leidžia naudotojui peržiūrėti ir redaguoti turto šifruotus pritaikytus laukus.',
    ],

    'accessories'   => [
        'name' => 'Priedai',
        'note'       => 'Suteikia prieigą prie programos Priedų skilties.',
    ],

    'accessoriesview' => [
        'name' => 'Peržiūrėti priedus',
    ],
    'accessoriescreate' => [
        'name' => 'Sukurti priedus',
    ],
    'accessoriesedit' => [
        'name' => 'Redaguoti priedus',
    ],
    'accessoriesdelete' => [
        'name' => 'Ištrinti priedus',
    ],
    'accessoriescheckout' => [
        'name' => 'Išduoti priedus',
        'note' => 'Priskirti inventoriuje esančius priedus, juos išduodant.',
    ],
    'accessoriescheckin' => [
        'name' => 'Paimti priedus',
        'note' => 'Paimti šiuo metu išduotus priedus ir grąžinti atgal į inventorių.',
    ],
    'accessoriesfiles' => [
        'name' => 'Tvarkyti priedų failus',
        'note' => 'Leidžia naudotojui įkelti, atsisiųsti ir ištrinti su priedais susijusius failus.',
    ],
    'consumables'   => [
        'name' => 'Eksploatacinės medžiagos',
        'note'       => 'Suteikia prieigą prie programos Eksploatacinių medžiagų skilties.',
    ],
    'consumablesview' => [
        'name' => 'Peržiūrėti eksploatacines medžiagas',
    ],
    'consumablescreate' => [
        'name' => 'Sukurti eksploatacines medžiagas',
    ],
    'consumablesedit' => [
        'name' => 'Redaguoti eksploatacines medžiagas',
    ],
    'consumablesdelete' => [
        'name' => 'Ištrinti eksploatacines medžiagas',
    ],
    'consumablescheckout' => [
        'name' => 'Išduoti eksploatacines medžiagas',
        'note' => 'Priskirti inventoriuje esančias eksploatacines medžiagas, jas išduodant.',
    ],
    'consumablesfiles' => [
        'name' => 'Tvarkyti eksploatacinių medžiagų failus',
        'note' => 'Leidžia naudotojui įkelti, atsisiųsti ir ištrinti su eksploatacinėmis medžiagomis susijusius failus.',
    ],
    'licenses'   => [
        'name' => 'Licencijos',
        'note'       => 'Suteikia prieigą prie programos Licencijų skilties.',
    ],
    'licensesview' => [
        'name' => 'Peržiūrėti licencijas',
    ],
    'licensescreate' => [
        'name' => 'Sukurti licencijas',
    ],
    'licensesedit' => [
        'name' => 'Redaguoti licencijas',
    ],
    'licensesdelete' => [
        'name' => 'Ištrinti licencijas',
    ],
    'licensescheckout' => [
        'name' => 'Priskirti licencijas',
        'note' => 'Leidžia naudotojui priskirti licencijas turtui arba naudotojams.',
        ],
    'licensescheckin' => [
        'name' => 'Atšaukti licencijų priskyrimą',
        'note' => 'Leidžia naudotojui atšaukti licencijų priskyrimą turtui arba naudotojams.',
    ],
    'licensesfiles' => [
        'name' => 'Tvarkyti licencijų failus',
        'note' => 'Leidžia naudotojui įkelti, atsisiųsti ir ištrinti su licencijomis susijusius failus.',
    ],
    'licenseskeys' => [
        'name' => 'Tvarkyti licencijų raktus',
        'note' => 'Leidžia naudotojui peržiūrėti su licencijomis susietus produkto kodus.',
    ],
    'components'   => [
        'name' => 'Komponentai',
        'note'       => 'Suteikia prieigą prie programos Komponentų skilties.',
    ],
    'componentsview' => [
        'name' => 'Peržiūrėti komponentus',
    ],
    'componentscreate' => [
        'name' => 'Sukurti komponentus',
    ],
    'componentsedit' => [
        'name' => 'Redaguoti komponentus',
    ],
    'componentsdelete' => [
        'name' => 'Ištrinti komponentus',
    ],
    'componentsfiles' => [
        'name' => 'Tvarkyti komponentų failus',
        'note' => 'Leidžia naudotojui įkelti, atsisiųsti ir ištrinti su komponentais susijusius failus.',
    ],
    'componentscheckout' => [
        'name' => 'Išduoti komponentus',
        'note' => 'Priskirti inventoriuje esančius komponentus, juos išduodant.',
    ],
    'componentscheckin' => [
        'name' => 'Paimti komponentus',
        'note' => 'Paimti šiuo metu išduotus komponentus ir grąžinti atgal į inventorių.',
    ],
    'kits'   => [
        'name' => 'Turto rinkiniai',
        'note'       => 'Suteikia prieigą prie programos Turto rinkinių skilties.',
    ],
    'kitsview' => [
        'name' => 'Peržiūrėti iš anksto nustatytus rinkinius',
    ],
    'kitscreate' => [
        'name' => 'Sukurti iš anksto nustatytus rinkinius',
    ],
    'kitsedit' => [
        'name' => 'Redaguoti iš anksto nustatytus rinkinius',
    ],
    'kitsdelete' => [
        'name' => 'Ištrinti iš anksto nustatytus rinkinius',
    ],
    'users'   => [
        'name' => 'Naudotojai',
        'note'       => 'Suteikia prieigą prie programos Naudotojų skilties.',
    ],
    'usersview' => [
        'name' => 'Peržiūrėti naudotojus',
    ],
    'userscreate' => [
        'name' => 'Sukurti naudotojus',
    ],
    'usersedit' => [
        'name' => 'Redaguoti naudotojus',
    ],
    'usersdelete' => [
        'name' => 'Ištrinti naudotojus',
    ],
    'models'   => [
        'name' => 'Modeliai',
        'note'       => 'Suteikia prieigą prie programos Modelių skilties.',
    ],
    'modelsview' => [
        'name' => 'Peržiūrėti modelius',
    ],

    'modelscreate' => [
        'name' => 'Sukurti modelius',
    ],
    'modelsedit' => [
        'name' => 'Redaguoti modelius',
    ],
    'modelsdelete' => [
        'name' => 'Ištrinti modelius',
    ],
    'categories'   => [
        'name' => 'Kategorijos',
        'note'       => 'Suteikia prieigą prie programos Kategorijų skilties.',
    ],
    'categoriesview' => [
        'name' => 'Peržiūrėti kategorijas',
    ],
    'categoriescreate' => [
        'name' => 'Sukurti kategorijas',
    ],
    'categoriesedit' => [
        'name' => 'Redaguoti kategorijas',
    ],
    'categoriesdelete' => [
        'name' => 'Ištrinti kategorijas',
    ],
    'departments'   => [
        'name' => 'Skyriai',
        'note'       => 'Suteikia prieigą prie programos Skyrių skilties.',
    ],
    'departmentsview' => [
        'name' => 'Peržiūrėti skyrius',
    ],
    'departmentscreate' => [
        'name' => 'Sukurti skyrius',
    ],
    'departmentsedit' => [
        'name' => 'Atnaujinti skyrius',
    ],
    'departmentsdelete' => [
        'name' => 'Ištrinti skyrius',
    ],
    'locations'   => [
        'name' => 'Vietos',
        'note'       => 'Suteikia prieigą prie programos Vietų skilties.',
    ],
    'locationsview' => [
        'name' => 'Peržiūrėti vietas',
    ],
    'locationscreate' => [
        'name' => 'Sukurti vietas',
    ],
    'locationsedit' => [
        'name' => 'Redaguoti vietas',
    ],
    'locationsdelete' => [
        'name' => 'Ištrinti vietas',
    ],
    'status-labels'   => [
        'name' => 'Būsenos žymos',
        'note'       => 'Suteikia prieigą prie turto naudojamų Būsenos žymų skilties programoje.',
    ],
    'statuslabelsview' => [
        'name' => 'Peržiūrėti būsenos žymas',
    ],
    'statuslabelscreate' => [
        'name' => 'Sukurti būsenos žymas',
    ],
    'statuslabelsedit' => [
        'name' => 'Redaguoti būsenos žymas',
    ],
    'statuslabelsdelete' => [
        'name' => 'Ištrinti būsenos žymas',
    ],
    'custom-fields'   => [
        'name' => 'Pritaikyti laukai',
        'note'       => 'Suteikia prieigą prie programos Pritaikytų laukų skilties.',
    ],
    'customfieldsview' => [
        'name' => 'Peržiūrėti pritaikytus laukus',
    ],
    'customfieldscreate' => [
        'name' => 'Sukurti pritaikytus laukus',
    ],
    'customfieldsedit' => [
        'name' => 'Redaguoti pritaikytus laukus',
    ],
    'customfieldsdelete' => [
        'name' => 'Ištrinti pritaikytus laukus',
    ],
    'suppliers'   => [
        'name' => 'Tiekėjai',
        'note'       => 'Suteikia prieigą prie programos Tiekėjų skilties.',
    ],
    'suppliersview' => [
        'name' => 'Peržiūrėti tiekėjus',
    ],
    'supplierscreate' => [
        'name' => 'Sukurti tiekėją',
    ],
    'suppliersedit' => [
        'name' => 'Redaguoti tiekėjus',
    ],
    'suppliersdelete' => [
        'name' => 'Ištrinti tiekėjus',
    ],
    'manufacturers'   => [
        'name' => 'Gamintojai',
        'note'       => 'Suteikia prieigą prie programos Gamintojų skilties.',
    ],
    'manufacturersview' => [
        'name' => 'Peržiūrėti gamintojus',
    ],
    'manufacturerscreate' => [
        'name' => 'Sukurti gamintojus',
    ],
    'manufacturersedit' => [
        'name' => 'Redaguoti gamintojus',
    ],
    'manufacturersdelete' => [
        'name' => 'Ištrinti gamintojus',
    ],
    'companies'   => [
        'name' => 'Įmonės',
        'note'       => 'Suteikia prieigą prie programos Įmonių skilties.',
    ],
    'companiesview' => [
        'name' => 'Peržiūrėti įmones',
    ],
    'companiescreate' => [
        'name' => 'Sukurti įmones',
    ],
    'companiesedit' => [
        'name' => 'Redaguoti įmones',
    ],
    'companiesdelete' => [
        'name' => 'Ištrinti įmones',
    ],
    'user-self-accounts' => [
        'name' => 'Naudotojų asmeninės paskyros',
        'note'       => 'Administratoriaus teisių neturintiems naudotojams suteikia galimybę tvarkyti tam tikrus jų naudotojo paskyros aspektus.',
    ],
    'selftwo-factor' => [
        'name' => 'Tvarkyti dviejų veiksnių autentifikaciją',
        'note'       => 'Leidžia naudotojams įjungti, išjungti ir valdyti dviejų veiksnių autentifikavimą savo paskyroms.',
    ],
    'selfapi' => [
        'name' => 'Tvarkyti API prieigos raktus',
        'note'       => 'Leidžia naudotojams kurti, peržiūrėti ir atšaukti savo API prieigos raktus. Naudotojo prieigos raktai turės tokias pačias teises kaip ir juos sukūręs naudotojas.',
    ],
    'selfedit-location' => [
        'name' => 'Redaguoti vietą',
        'note'       => 'Leidžia naudotojams redaguoti su jų naudotojo paskyra susietą vietą.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Savarankiškai prisiskirti turtą',
        'note'       => 'Leidžia naudotojams patiems prisiskirti turtą be administratoriaus įsikišimo.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Peržiūrėti įsigijimo kainą',
        'note'       => 'Leidžia naudotojams peržiūrėti įsigijimo kainą jų paskyros rodinyje.',
    ],

    'depreciations' => [
        'name' => 'Nusidėvėjimo valdymas',
        'note'       => 'Leidžia naudotojams valdyti ir peržiūrėti turto nusidėvėjimo informaciją.',
    ],
    'depreciationsview' => [
        'name' => 'Peržiūrėti nusidėvėjimo informaciją',
    ],
    'depreciationsedit' => [
        'name' => 'Redaguoti nusidėvėjimo nustatymus',
    ],
    'depreciationsdelete' => [
        'name' => 'Ištrinti nusidėvėjimo įrašus',
    ],
    'depreciationscreate' => [
        'name' => 'Sukurti nusidėvėjimo įrašus',
    ],

    'grant_all' => 'Suteikti visas teises į :area',
    'deny_all' => 'Nesuteikti visų teisių į :area',
    'inherit_all' => 'Paveldėti visas teises į :area iš teisių grupių',
    'grant' => 'Suteikti teisę į :area',
    'deny' => 'Nesuteikti teisės į :area',
    'inherit' => 'Paveldėti teisę į :area iš teisių grupių',
    'use_groups' => 'Siekiant lengvesnio valdymo, primygtinai rekomenduojame naudoti leidimų grupes, o ne priskirti individualius leidimus.'

);
