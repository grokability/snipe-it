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
        'name' => 'Superanvändare',
        'note' => 'Avgör om användaren har full tillgång till alla aspekter av administrationen. Den här inställningen åsidosätter ALLA mer specifika och begränsande behörigheter i hela systemet. ',
    ],
    'admin' => [
        'name' => 'Admin-tillgång',
        'note' => 'Avgör om användaren har tillgång till de flesta aspekter av systemet UTOM Systemadmininställningarna. Dessa användare kan hantera användare, platser, kategorier etc., men ÄR begränsade av Full Multiple Company Support om det är aktiverat.',
    ],

    'import' => [
        'name' => 'CSV-import',
        'note' => 'Detta kommer att tillåta användare att importera även om åtkomst till användare, tillgångar etc. nekas någon annanstans.',
    ],

    'reports' => [
        'name' => 'Rapportåtkomst',
        'note' => 'Avgör om användaren har åtkomst till rapportavsnittet i applikationen.',
    ],

    'assets' => [
        'name' => 'Tillgångar',
        'note' => 'Beviljar åtkomst till avsnittet Tillgångar i applikationen. ',
    ],

    'assetsview' => [
        'name' => 'Visa tillgångar',
        'note' => 'Användare med den här behörigheten kan även visa, men inte ändra eller ta bort, filer som laddats upp till tillgångsmodellen. Det gör det lättare att dela gemensamma dokument, som användarhandböcker, mellan tillgångar utan att ladda upp dem till varje tillgång eller ge användaren rätt att ändra tillgångsfiler. Användarna kan även se redigerings- och incheckningshistorik.',
    ],

    'assetscreate' => [
        'name' => 'Skapa nya tillgångar',
    ],

    'assetsedit' => [
        'name' => 'Redigera tillgångar',
    ],

    'assetsdelete' => [
        'name' => 'Ta bort tillgångar',
    ],

    'assetscheckin' => [
        'name' => 'Checka in',
        'note' => 'Checka in tillgångar som för närvarande är utcheckade.',
    ],

    'assetscheckout' => [
        'name' => 'Checka ut',
        'note' => 'Tilldela tillgångar genom att checka ut dem.',
    ],

    'assetsaudit' => [
        'name' => 'Inventera tillgångar',
        'note' => 'Gör det möjligt för användaren att markera en tillgång som fysiskt inventerad.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Visa begärbara objekt',
        'note' => 'Gör det möjligt för användaren att visa objekt som är markerade som begärbara.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Visa krypterade anpassade fält',
        'note' => 'Gör det möjligt för användaren att visa och ändra krypterade anpassade fält på tillgångar.',
    ],

    'accessories' => [
        'name' => 'Tillbehör',
        'note' => 'Beviljar åtkomst till avsnittet Tillbehör i applikationen.',
    ],

    'accessoriesview' => [
        'name' => 'Visa tillbehör',
    ],
    'accessoriescreate' => [
        'name' => 'Skapa nya tillbehör',
    ],
    'accessoriesedit' => [
        'name' => 'Redigera tillbehör',
    ],
    'accessoriesdelete' => [
        'name' => 'Ta bort tillbehör',
    ],
    'accessoriescheckout' => [
        'name' => 'Checka ut tillbehör',
        'note' => 'Tilldela tillbehör i inventeringen genom att checka ut dem.',
    ],
    'accessoriescheckin' => [
        'name' => 'Checka in tillbehör',
        'note' => 'Checka in tillbehör som för närvarande är utcheckade tillbaka i inventeringen.',
    ],
    'accessoriesfiles' => [
        'name' => 'Hantera tillbehörsfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer kopplade till tillbehör. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'assetsfiles' => [
        'name' => 'Hantera tillgångsfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer kopplade till tillgångar. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'usersfiles' => [
        'name' => 'Hantera användarfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer kopplade till användare. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'modelsfiles' => [
        'name' => 'Hantera modellfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer kopplade till tillgångsmodeller på både modellvy- och tillgångsvyskärmar. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'departmentsfiles' => [
        'name' => 'Hantera avdelningsfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer kopplade till avdelningar. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'suppliersfiles' => [
        'name' => 'Hantera leverantörsfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer kopplade till leverantörer. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'locationsfiles' => [
        'name' => 'Hantera platsfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer som är kopplade till platser. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'companiesfiles' => [
        'name' => 'Hantera företagsfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer som är kopplade till företag. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'consumablesfiles' => [
        'name' => 'Hantera förbrukningsmaterialfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer som är kopplade till förbrukningsmaterial. (Detta ger bara mening med visningsrättigheter eller högre.)',
    ],

    'consumables' => [
        'name' => 'Förbrukningsmaterial',
        'note' => 'Ger åtkomst till avsnittet för förbrukningsmaterial i applikationen.',
    ],
    'consumablesview' => [
        'name' => 'Visa förbrukningsmaterial',
    ],
    'consumablescreate' => [
        'name' => 'Skapa nytt förbrukningsmaterial',
    ],
    'consumablesedit' => [
        'name' => 'Redigera förbrukningsmaterial',
    ],
    'consumablesdelete' => [
        'name' => 'Ta bort förbrukningsmaterial',
    ],
    'consumablescheckout' => [
        'name' => 'Checka ut förbrukningsmaterial',
        'note' => 'Tilldela förbrukningsmaterial i inventeringen genom att checka ut dem.',
    ],

    'licenses' => [
        'name' => 'Licenser',
        'note' => 'Ger åtkomst till avsnittet för licenser i applikationen.',
    ],
    'licensesview' => [
        'name' => 'Visa licenser',
    ],
    'licensescreate' => [
        'name' => 'Skapa ny licens',
    ],
    'licensesedit' => [
        'name' => 'Redigera licenser',
    ],
    'licensesdelete' => [
        'name' => 'Ta bort licenser',
    ],
    'licensescheckout' => [
        'name' => 'Tilldela licenser',
        'note' => 'Tillåter användaren att tilldela licenser till tillgångar eller användare.',
    ],
    'licensescheckin' => [
        'name' => 'Avsluta tilldelning av licenser',
        'note' => 'Tillåter användaren att ta bort tilldelning av licenser från tillgångar eller användare.',
    ],
    'licensesfiles' => [
        'name' => 'Hantera licensfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer som är kopplade till licenser.',
    ],
    'componentsfiles' => [
        'name' => 'Hantera komponentfiler',
        'note' => 'Tillåter användaren att ladda upp, ladda ner och ta bort filer som är kopplade till komponenter.',
    ],

    'licenseskeys' => [
        'name' => 'Hantera licensnycklar',
        'note' => 'Tillåter användaren att visa produkt nycklar som är kopplade till licenser.',
    ],
    'components' => [
        'name' => 'Komponenter',
        'note' => 'Ger åtkomst till avsnittet Komponenter i applikationen.',
    ],
    'componentsview' => [
        'name' => 'Visa komponenter',
    ],
    'componentscreate' => [
        'name' => 'Skapa nya komponenter',
    ],
    'componentsedit' => [
        'name' => 'Redigera komponenter',
    ],
    'componentsdelete' => [
        'name' => 'Ta bort komponenter',
    ],

    'componentscheckout' => [
        'name' => 'Checka ut komponenter',
        'note' => 'Tilldela komponenter i inventeringen genom att checka ut dem.',
    ],
    'componentscheckin' => [
        'name' => 'Checka in komponenter',
        'note' => 'Checka in komponenter som för närvarande är utcheckade tillbaka till inventeringen.',
    ],
    'kits' => [
        'name' => 'Fördefinierade paket',
        'note' => 'Ger åtkomst till avsnittet Fördefinierade kit i applikationen.',
    ],
    'kitsview' => [
        'name' => 'Visa fördefinierade kit',
    ],
    'kitscreate' => [
        'name' => 'Skapa nya fördefinierade kit',
    ],
    'kitsedit' => [
        'name' => 'Redigera fördefinierade kit',
    ],
    'kitsdelete' => [
        'name' => 'Ta bort fördefinierade kit',
    ],
    'users' => [
        'name' => 'Användare',
        'note' => 'Ger åtkomst till användarsektionen i applikationen.',
    ],
    'usersview' => [
        'name' => 'Visa användare',
        'note' => 'Users with this permission will also be able to view edit and checkin history. Access to files uploaded to a user is gated separately by the Manage User Files permission.',
    ],
    'userscreate' => [
        'name' => 'Skapa nya användare',
    ],
    'usersedit' => [
        'name' => 'Redigera användare',
    ],
    'usersdelete' => [
        'name' => 'Ta bort användare',
    ],
    'models' => [
        'name' => 'Modeller',
        'note' => 'Ger åtkomst till modellsektionen i applikationen.',
    ],
    'modelsview' => [
        'name' => 'Visa modeller',
    ],

    'modelscreate' => [
        'name' => 'Skapa nya modeller',
    ],
    'modelsedit' => [
        'name' => 'Redigera modeller',
    ],
    'modelsdelete' => [
        'name' => 'Ta bort modeller',
    ],
    'categories' => [
        'name' => 'Kategorier',
        'note' => 'Ger åtkomst till kategorisektionen i applikationen.',
    ],
    'categoriesview' => [
        'name' => 'Visa kategorier',
    ],
    'categoriescreate' => [
        'name' => 'Skapa nya kategorier',
    ],
    'categoriesedit' => [
        'name' => 'Redigera kategorier',
    ],
    'categoriesdelete' => [
        'name' => 'Ta bort kategorier',
    ],
    'departments' => [
        'name' => 'Avdelningar',
        'note' => 'Ger åtkomst till avdelningssektionen i applikationen.',
    ],
    'departmentsview' => [
        'name' => 'Visa avdelningar',
    ],
    'departmentscreate' => [
        'name' => 'Skapa nya avdelningar',
    ],
    'departmentsedit' => [
        'name' => 'Redigera avdelningar',
    ],
    'departmentsdelete' => [
        'name' => 'Ta bort avdelningar',
    ],
    'locations' => [
        'name' => 'Platser',
        'note' => 'Ger åtkomst till avsnittet Platser i applikationen.',
    ],
    'locationsview' => [
        'name' => 'Visa platser',
    ],
    'locationscreate' => [
        'name' => 'Skapa nya platser',
    ],
    'locationsedit' => [
        'name' => 'Redigera platser',
    ],
    'locationsdelete' => [
        'name' => 'Ta bort platser',
    ],
    'status-labels' => [
        'name' => 'Statusetiketter',
        'note' => 'Ger åtkomst till avsnittet Statusetiketter i applikationen som används av Tillgångar.',
    ],
    'statuslabelsview' => [
        'name' => 'Visa statusetiketter',
    ],
    'statuslabelscreate' => [
        'name' => 'Skapa nya statusetiketter',
    ],
    'statuslabelsedit' => [
        'name' => 'Redigera statusetiketter',
    ],
    'statuslabelsdelete' => [
        'name' => 'Ta bort statusetiketter',
    ],
    'custom-fields' => [
        'name' => 'Anpassade fält',
        'note' => 'Ger åtkomst till avsnittet Anpassade fält i applikationen som används av Tillgångar.',
    ],
    'customfieldsview' => [
        'name' => 'Visa anpassade fält',
    ],
    'customfieldscreate' => [
        'name' => 'Skapa nya anpassade fält',
    ],
    'customfieldsedit' => [
        'name' => 'Redigera anpassade fält',
    ],
    'customfieldsdelete' => [
        'name' => 'Ta bort anpassade fält',
    ],
    'suppliers' => [
        'name' => 'Leverantörer',
        'note' => 'Ger åtkomst till avsnittet Leverantörer i applikationen.',
    ],
    'suppliersview' => [
        'name' => 'Visa leverantörer',
    ],
    'supplierscreate' => [
        'name' => 'Skapa nya leverantörer',
    ],
    'suppliersedit' => [
        'name' => 'Redigera leverantörer',
    ],
    'suppliersdelete' => [
        'name' => 'Ta bort leverantörer',
    ],
    'manufacturers' => [
        'name' => 'Tillverkare',
        'note' => 'Ger åtkomst till avsnittet Tillverkare i applikationen.',
    ],
    'manufacturersview' => [
        'name' => 'Visa tillverkare',
    ],
    'manufacturerscreate' => [
        'name' => 'Skapa ny tillverkare',
    ],
    'manufacturersedit' => [
        'name' => 'Redigera tillverkare',
    ],
    'manufacturersdelete' => [
        'name' => 'Ta bort tillverkare',
    ],
    'companies' => [
        'name' => 'Företag',
        'note' => 'Ger åtkomst till avsnittet Företag i applikationen.',
    ],
    'companiesview' => [
        'name' => 'Visa företag',
    ],
    'companiescreate' => [
        'name' => 'Skapa nytt företag',
    ],
    'companiesedit' => [
        'name' => 'Redigera företag',
    ],
    'companiesdelete' => [
        'name' => 'Ta bort företag',
    ],
    'user-self-accounts' => [
        'name' => 'Användarens egna konton',
        'note' => 'Ger icke-administratörer möjlighet att hantera vissa delar av sina egna användarkonton.',
    ],
    'selftwo-factor' => [
        'name' => 'Hantera tvåfaktorsautentisering',
        'note' => 'Tillåter användare att aktivera, inaktivera och hantera tvåfaktorsautentisering för sina egna konton.',
    ],
    'selfapi' => [
        'name' => 'Hantera API-nycklar',
        'note' => 'Tillåter användare att skapa, visa och återkalla sina egna API-nycklar. Användarnycklar har samma behörigheter som den användare som skapade dem.',
    ],
    'selfedit-location' => [
        'name' => 'Redigera plats',
        'note' => 'Tillåter användare att redigera platsen kopplad till deras eget användarkonto.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Självchecka ut tillgångar',
        'note' => 'Tillåter användare att checka ut tillgångar till sig själva utan administratörsinblandning.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Visa inköpskostnad',
        'note' => 'Tillåter användare att visa inköpskostnaden för objekt i deras kontovisning.',
    ],

    'depreciations' => [
        'name' => 'Värdeminskningshantering',
        'note' => 'Tillåter användare att hantera och visa detaljer om tillgångars värdeminskning.',
    ],
    'depreciationsview' => [
        'name' => 'Visa värdeminskningsdetaljer',
    ],
    'depreciationsedit' => [
        'name' => 'Redigera inställningar för värdeminskning',
    ],
    'depreciationsdelete' => [
        'name' => 'Ta bort värdeminskningsposter',
    ],
    'depreciationscreate' => [
        'name' => 'Skapa värdeminskningsposter',
    ],

    'grant_all' => 'Bevilja alla behörigheter för :area',
    'deny_all' => 'Neka alla behörigheter för :area',
    'inherit_all' => 'Ärv alla behörigheter för :area från behörighetsgrupper',
    'grant' => 'Bevilja behörighet för :area',
    'deny' => 'Neka behörighet för :area',
    'inherit' => 'Ärv behörighet för :area från behörighetsgrupper',
    'use_groups' => 'Vi rekommenderar starkt att använda behörighetsgrupper istället för att tilldela individuella behörigheter för enklare hantering.',

];
