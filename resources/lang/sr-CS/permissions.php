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
        'name' => 'Super korisnik',
        'note'       => 'Određuje da li korisnik ima potpuni pristup svim aspektima administracije. Ovo podešavanje predupređuje SVA specifična i ograničavajuća ovlašćenja u celom sistemu. ',
    ],
    'admin' => [
        'name' => 'Administratorski pristup',
        'note'       => 'Određuje da li korisnik ima pristup većini aspekata sistema OSIM podešavanjima sistemske administracije. Ovi korisnici će moći da upravljaju korisnicima, lokacijama, kategorijama, itd, ali su ograničeni Punom višekompanijskom podrškom ako je omogućena.',
    ],

    'import' => [
        'name' => 'Uvoženje CSV-a',
        'note'       => 'Ovo će dozvoliti korisnicima da uvoze čak i ako pristup korisnicima, imovinom, itd. je onemogućeno negde drugde.',
    ],

    'reports' => [
        'name' => 'Pristup izveštajima',
        'note'       => 'Određuje da li korisnik ima pristup sekciji sa izveštajima aplikacije.',
    ],

    'assets' =>
        [
            'name' => 'Imovina',
            'note' => 'Dozvoljava pristup sekciji sa imovinom aplikacije.',
    ],

    'assetsview' => [
        'name' => 'Pregled imovine',
    ],

    'assetscreate' => [
        'name' => 'Kreiranje nove imovine',
    ],

    'assetsedit' => [
        'name' => 'Uređivanje imovine',
    ],

    'assetsdelete' => [
        'name' => 'Brisanje imovine',
    ],

    'assetscheckin' => [
        'name' => 'Razduživanje',
        'note' => 'Razduživanje imovine koja je zadužena nazad u magacin.',
    ],

    'assetscheckout' => [
        'name' => 'Zaduživanje',
        'note' => 'Dodeljivanje imovine iz magacina zaduživanjem.',
    ],

    'assetsaudit' => [
        'name' => 'Popisivanje imovine',
        'note' => 'Dozvoljava korisniku da označi imovinu fizički popisanu.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Pregled zatražene imovine',
        'note' => 'Omogućava korisniku pregled imovine koja je označene kao zatraživa.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Pregled enkriptovanih prilagođenih polja',
        'note' => 'Dozvoljava korisniku pregled i izmenu eknriptovanih prilagođenih polja imovine.',
    ],

    'accessories'   => [
        'name' => 'Dodatna oprema',
        'note'       => 'Dozvoljava pristup sekciji dodatne opreme aplikacije.',
    ],

    'accessoriesview' => [
        'name' => 'Pregled dodatne opreme',
    ],
    'accessoriescreate' => [
        'name' => 'Kreiranje nove dodatne opreme',
    ],
    'accessoriesedit' => [
        'name' => 'Izmena dodatne opreme',
    ],
    'accessoriesdelete' => [
        'name' => 'Brisanje dodatne opreme',
    ],
    'accessoriescheckout' => [
        'name' => 'Zaduživanje dodatne opreme',
        'note' => 'Dodeljivanje dodatne opreme u magacinu zaduživanjem.',
    ],
    'accessoriescheckin' => [
        'name' => 'Razduživanje dodatne opreme',
        'note' => 'Vraćanje zadužene dodatne opreme natrag u magacin.',
    ],
    'accessoriesfiles' => [
        'name' => 'Upravljanje datotekama dodatne opreme',
        'note' => 'Dozvoljava korisniku da postavlja, preuzima i briše datoteke povezane sa dodatnom opremom.',
    ],
    'consumables'   => [
        'name' => 'Potrošni materijal',
        'note'       => 'Dozvoljava pristup sekciji potrošne opreme aplikacije.',
    ],
    'consumablesview' => [
        'name' => 'Pregled potrošne opreme',
    ],
    'consumablescreate' => [
        'name' => 'Kreiranje nove potrošne opreme',
    ],
    'consumablesedit' => [
        'name' => 'Izmena potrošne opreme',
    ],
    'consumablesdelete' => [
        'name' => 'Brisanje potrošne opreme',
    ],
    'consumablescheckout' => [
        'name' => 'Zaduživanje potrošne opreme',
        'note' => 'Dodeljivanje potrošne opreme u magacinu zaduživanjem.',
    ],
    'consumablesfiles' => [
        'name' => 'Upravljanje datotekama potrošne opreme',
        'note' => 'Dozvoljava korisniku da postavlja, preuzima i briše datoteke povezane sa potrošnom opremom.',
    ],
    'licenses'   => [
        'name' => 'Licence',
        'note'       => 'Omogućava pristup sekciji sa licencama aplikacije.',
    ],
    'licensesview' => [
        'name' => 'Pregled licenci',
    ],
    'licensescreate' => [
        'name' => 'Kreiranje novih licenci',
    ],
    'licensesedit' => [
        'name' => 'Uređivanje licenci',
    ],
    'licensesdelete' => [
        'name' => 'Brisanje licenci',
    ],
    'licensescheckout' => [
        'name' => 'Dodeljivanje licenci',
        'note' => 'Omogućava korisniku da dodeljuje licence imovini ili korisnicima.',
        ],
    'licensescheckin' => [
        'name' => 'Oduzimanje licenci',
        'note' => 'Omogućava korisniku da oduzme licence od imovine ili korisnika.',
    ],
    'licensesfiles' => [
        'name' => 'Upravljanje datotekama licenci',
        'note' => 'Dozvoljava korisniku da postavlja, preuzima i briše datoteke povezane sa licencama.',
    ],
    'licenseskeys' => [
        'name' => 'Upravljanje licencnim ključevima',
        'note' => 'Omogućava korisniku da pregleda ključeve proizvoda povezanih sa licencama.',
    ],
    'components'   => [
        'name' => 'Komponente',
        'note'       => 'Dozvoljava pristup sekciji komponenti aplikacije.',
    ],
    'componentsview' => [
        'name' => 'Pregled komponenti',
    ],
    'componentscreate' => [
        'name' => 'Kreiranje novih komponenti',
    ],
    'componentsedit' => [
        'name' => 'Uređivanje komponenti',
    ],
    'componentsdelete' => [
        'name' => 'Brisanje komponenti',
    ],
    'componentsfiles' => [
        'name' => 'Upravljanje datotekama komponenti',
        'note' => 'Dozvoljava korisniku da postavlja, preuzima i briše datoteke povezane sa komponentama.',
    ],
    'componentscheckout' => [
        'name' => 'Zaduživanje komponenti',
        'note' => 'Dodeljivanje komponenti zaduživanjem iz magacina.',
    ],
    'componentscheckin' => [
        'name' => 'Razduživanje komponenti',
        'note' => 'Vraćanje komponenti koja je zadužena natrag u magacin.',
    ],
    'kits'   => [
        'name' => 'Kompleti opreme',
        'note'       => 'Dozvoljava pristup sekciji predefinisanih skupova aplikacije.',
    ],
    'kitsview' => [
        'name' => 'Pregled predefinisanih skupova',
    ],
    'kitscreate' => [
        'name' => 'Kreiranje novih predefinisanih skupova',
    ],
    'kitsedit' => [
        'name' => 'Uređivanje predefinisanih skupova',
    ],
    'kitsdelete' => [
        'name' => 'Brisanje predefinisanih skupova',
    ],
    'users'   => [
        'name' => 'Korisnici',
        'note'       => 'Dozvoljava pristup sekciji korisnika aplikacije.',
    ],
    'usersview' => [
        'name' => 'Prikaži korisnike',
    ],
    'userscreate' => [
        'name' => 'Kreiranje novih korisnika',
    ],
    'usersedit' => [
        'name' => 'Uređivanje korisnika',
    ],
    'usersdelete' => [
        'name' => 'Brisanje korisnika',
    ],
    'models'   => [
        'name' => 'Modeli',
        'note'       => 'Dozvoljava pristup sekciji modela aplikacije.',
    ],
    'modelsview' => [
        'name' => 'Pogledajte modele',
    ],

    'modelscreate' => [
        'name' => 'Kreiranje novih modela',
    ],
    'modelsedit' => [
        'name' => 'Uređivanje modela',
    ],
    'modelsdelete' => [
        'name' => 'Brisanje modela',
    ],
    'categories'   => [
        'name' => 'Kategorije',
        'note'       => 'Dozvoljava pristup sekciji kategorija aplikacije.',
    ],
    'categoriesview' => [
        'name' => 'Pregled kategorija',
    ],
    'categoriescreate' => [
        'name' => 'Kreiranje novih kategorija',
    ],
    'categoriesedit' => [
        'name' => 'Uređivanje kategorija',
    ],
    'categoriesdelete' => [
        'name' => 'Brisanje kategorija',
    ],
    'departments'   => [
        'name' => 'Departments',
        'note'       => 'Dozvoljava pristup sekciji odeljenja aplikacije.',
    ],
    'departmentsview' => [
        'name' => 'Pregled odeljenja',
    ],
    'departmentscreate' => [
        'name' => 'Kreiranje novih odeljenja',
    ],
    'departmentsedit' => [
        'name' => 'Uređivanje odeljenja',
    ],
    'departmentsdelete' => [
        'name' => 'Brisanje odeljenja',
    ],
    'locations'   => [
        'name' => 'Lokacije',
        'note'       => 'Dozvoljava pristup sekciji lokacija aplikacije.',
    ],
    'locationsview' => [
        'name' => 'Pregled lokacija',
    ],
    'locationscreate' => [
        'name' => 'Kreiranje novih lokacija',
    ],
    'locationsedit' => [
        'name' => 'Uređivanje lokacija',
    ],
    'locationsdelete' => [
        'name' => 'Brisanje lokacija',
    ],
    'status-labels'   => [
        'name' => 'Oznake statusa',
        'note'       => 'Dozvoljava pristup sekciji oznaka statusa aplikacije koje koriste imovine.',
    ],
    'statuslabelsview' => [
        'name' => 'Pregled oznaka statusa',
    ],
    'statuslabelscreate' => [
        'name' => 'Kreiranje novih oznaka statusa',
    ],
    'statuslabelsedit' => [
        'name' => 'Uređivanje oznaka statusa',
    ],
    'statuslabelsdelete' => [
        'name' => 'Brisanje oznaka statusa',
    ],
    'custom-fields'   => [
        'name' => 'Dodatna Polja',
        'note'       => 'Dozvoljava pristup sekciji prilagođenih polja aplikacije koje koriste imovine.',
    ],
    'customfieldsview' => [
        'name' => 'Pregled prilagođenih polja',
    ],
    'customfieldscreate' => [
        'name' => 'Kreiranje novih prilagođenih polja',
    ],
    'customfieldsedit' => [
        'name' => 'Uređivanje prilagođenih polja',
    ],
    'customfieldsdelete' => [
        'name' => 'Brisanje prilagođenih polja',
    ],
    'suppliers'   => [
        'name' => 'Dobavljači',
        'note'       => 'Dozvoljava pristup sekciji dobavljača aplikacije.',
    ],
    'suppliersview' => [
        'name' => 'Pregled dobavljača',
    ],
    'supplierscreate' => [
        'name' => 'Kreiranje novih dobavljača',
    ],
    'suppliersedit' => [
        'name' => 'Uređivanje dobavljača',
    ],
    'suppliersdelete' => [
        'name' => 'Brisanje dobavljača',
    ],
    'manufacturers'   => [
        'name' => 'Proizvođači',
        'note'       => 'Dozvoljava pristup sekciji proizvođača aplikacije.',
    ],
    'manufacturersview' => [
        'name' => 'Pregled proizvođača',
    ],
    'manufacturerscreate' => [
        'name' => 'Kreiranje novih proizvođača',
    ],
    'manufacturersedit' => [
        'name' => 'Uređivanje proizvođača',
    ],
    'manufacturersdelete' => [
        'name' => 'Brisanje proizvođača',
    ],
    'companies'   => [
        'name' => 'Firme',
        'note'       => 'Dozvoljava pristup sekciji kompanija aplikacije.',
    ],
    'companiesview' => [
        'name' => 'Pregled kompanija',
    ],
    'companiescreate' => [
        'name' => 'Kreiranje novih kompanija',
    ],
    'companiesedit' => [
        'name' => 'Uređivanje kompanija',
    ],
    'companiesdelete' => [
        'name' => 'Brisanje kompanija',
    ],
    'user-self-accounts' => [
        'name' => 'Rukovođenje sopstvenim nalozima',
        'note'       => 'Dozvoljava standardnim korisnicima da upravljaju određenim aspektima njihovih naloga.',
    ],
    'selftwo-factor' => [
        'name' => 'Upravljanje autentifikacijom u dva koraka',
        'note'       => 'Dozvoljava korisnicima da omoguće, onemoguće i upravljaju autentifikacijom u dva koraka za njihove naloge.',
    ],
    'selfapi' => [
        'name' => 'Upravljanje API tokenima',
        'note'       => 'Dozvoljava korisnicima kreiranje, pregled i poništavanje sopstvenih API tokena. Korisnički tokeni će imati ista ovlašćenja kao i korisnik koji ih je kreirao.',
    ],
    'selfedit-location' => [
        'name' => 'Uređivanje lokacije',
        'note'       => 'Dozvoljava korisnicima da uređuju lokaciju povezanu sa njihovim nalogom.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Samostalno zaduživanje imovine',
        'note'       => 'Dozvoljava korisnicima da zadužuju imovinu sebi bez intervencije administratora.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Pregled cene koštanja',
        'note'       => 'Dozvoljava korisnicima da pregledaju cenu kupovine stavki u prikazu njihovog naloga.',
    ],

    'depreciations' => [
        'name' => 'Upravljanje amortizacijom',
        'note'       => 'Dozvoljava korisnicima da upravljaju i pregledaju detalje o amortizaciji imovina.',
    ],
    'depreciationsview' => [
        'name' => 'Pregled detalja amortizacije',
    ],
    'depreciationsedit' => [
        'name' => 'Uređivanje podešavanja amortizacije',
    ],
    'depreciationsdelete' => [
        'name' => 'Brisanje zapisa o amortizaciji',
    ],
    'depreciationscreate' => [
        'name' => 'Kreiranje zapisa o amortizaciji',
    ],

    'grant_all' => 'Dodeli sve dozvole za :area',
    'deny_all' => 'Onemogući sve dozvole za :area',
    'inherit_all' => 'Nasledi sve dozvole za :area iz grupe dozvola',
    'grant' => 'Dodeli dozvole za :area',
    'deny' => 'Onemogući dozvole za :area',
    'inherit' => 'Nasledi dozvole za :area iz grupe dozvola',
    'use_groups' => 'Strogo preporučujemo korišćenje Grupa dozvola umesto dodeljivanja pojedinačnih dozvola zbog lakšeg upravljanja.'

);
