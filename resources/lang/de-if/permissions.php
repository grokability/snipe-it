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
        'name' => 'Superbenutzer',
        'note'       => 'Legt fest, ob der Benutzer vollen Zugriff auf alle Aspekte des Administrators hat. Diese Einstellung überschreibt ALLE spezifischeren und restriktiveren Berechtigungen im gesamten System. ',
    ],
    'admin' => [
        'name' => 'Admin-Zugriff',
        'note'       => 'Legt fest, ob der Benutzer Zugriff auf die meisten Aspekte des Systems AUSSER in den Systemeinstellungen hat. Diese Benutzer werden in der Lage sein, Benutzer, Standorte, Kategorien, etc, zu verwalten, aber SIND beschränkt durch die Volle Unterstützung für mehrere Unternehmen, wenn es aktiviert ist.',
    ],

    'import' => [
        'name' => 'CSV-Import',
        'note'       => 'Dies wird Benutzern erlauben zu importieren, auch wenn der Zugriff auf Benutzer, Assets, usw. an anderer Stelle verweigert wird.',
    ],

    'reports' => [
        'name' => 'Berichtszugriff',
        'note'       => 'Legt fest, ob der Benutzer Zugriff auf den Reports-Abschnitt der Anwendung hat.',
    ],

    'assets' =>
        [
            'name' => 'Assets',
            'note' => 'Gewährt Zugriff auf den Abschnitt Assets der Anwendung.',
    ],

    'assetsview' => [
        'name' => 'Asset ansehen',
    ],

    'assetscreate' => [
        'name' => 'Asset erstellen',
    ],

    'assetsedit' => [
        'name' => 'Asset bearbeiten',
    ],

    'assetsdelete' => [
        'name' => 'Assets löschen',
    ],

    'assetscheckin' => [
        'name' => 'Einchecken',
        'note' => 'Check die Assets wieder in das derzeit ausgebuchte Inventar.',
    ],

    'assetscheckout' => [
        'name' => 'Auschecken',
        'note' => 'Assets im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],

    'assetsaudit' => [
        'name' => 'Assets prüfen',
        'note' => 'Ermöglicht dem Benutzer, ein Asset als physisch inventarisiert zu markieren.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Anforderbare Assets anzeigen',
        'note' => 'Ermöglicht dem Benutzer, Assets anzuzeigen, die als anforderbar markiert sind.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Verschlüsselte Felder anzeigen',
        'note' => 'Ermöglicht dem Benutzer, verschlüsselte Felder auf Assets anzuzeigen und zu ändern.',
    ],

    'accessories'   => [
        'name' => 'Zubehör',
        'note'       => 'Gewährt Zugriff auf den Abschnitt Zubehör der Anwendung.',
    ],

    'accessoriesview' => [
        'name' => 'Zubehör ansehen',
    ],
    'accessoriescreate' => [
        'name' => 'Neues Zubehör erstellen',
    ],
    'accessoriesedit' => [
        'name' => 'Zubehör bearbeiten',
    ],
    'accessoriesdelete' => [
        'name' => 'Zubehör löschen',
    ],
    'accessoriescheckout' => [
        'name' => 'Zubehör auschecken',
        'note' => 'Zubehör im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],
    'accessoriescheckin' => [
        'name' => 'Zubehör einchecken',
        'note' => 'Check das Zubehör wieder ins Inventar, dass derzeit ausgebucht ist.',
    ],
    'accessoriesfiles' => [
        'name' => 'Zubehördateien verwalten',
        'note' => 'Ermöglicht dem Benutzer das Hochladen, Herunterladen und Löschen von Zubehör Dateien.',
    ],
    'consumables'   => [
        'name' => 'Verbrauchsmaterialien',
        'note'       => 'Gewährt Zugriff auf den Bereich Verbrauchsmaterialien der Anwendung.',
    ],
    'consumablesview' => [
        'name' => 'Verbrauchsmaterialien anzeigen',
    ],
    'consumablescreate' => [
        'name' => 'Neue Verbrauchsmaterialien erstellen',
    ],
    'consumablesedit' => [
        'name' => 'Verbrauchsmaterial aktualisieren',
    ],
    'consumablesdelete' => [
        'name' => 'Verbrauchsmaterialien löschen',
    ],
    'consumablescheckout' => [
        'name' => 'Verbrauchsmaterialien auschecken',
        'note' => 'Verbrauchsmaterialien im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],
    'consumablesfiles' => [
        'name' => 'Verbrauchsdateien verwalten',
        'note' => 'Ermöglicht dem Benutzer das Hochladen, Herunterladen und Löschen von Verbrauchsmaterialien.',
    ],
    'licenses'   => [
        'name' => 'Lizenzen',
        'note'       => 'Gewährt Zugriff auf den Abschnitt Lizenzen der Anwendung.',
    ],
    'licensesview' => [
        'name' => 'Lizenzen ansehen',
    ],
    'licensescreate' => [
        'name' => 'Neue Lizenzen erstellen',
    ],
    'licensesedit' => [
        'name' => 'Lizenzen bearbeiten',
    ],
    'licensesdelete' => [
        'name' => 'Lizenzen löschen',
    ],
    'licensescheckout' => [
        'name' => 'Lizenzen zuweisen',
        'note' => 'Ermöglicht dem Benutzer, Assets oder Benutzern Lizenzen zuzuweisen.',
        ],
    'licensescheckin' => [
        'name' => 'Unassign Licenses',
        'note' => 'Allows the user to unassign licenses from assets or users.',
    ],
    'licensesfiles' => [
        'name' => 'Manage License Files',
        'note' => 'Allows the user to upload, download, and delete files associated with licenses.',
    ],
    'licenseskeys' => [
        'name' => 'Manage License Keys',
        'note' => 'Allows the user to view product keys associated with licenses.',
    ],
    'components'   => [
        'name' => 'Komponenten',
        'note'       => 'Grants access to the Components section of the application.',
    ],
    'componentsview' => [
        'name' => 'View Components',
    ],
    'componentscreate' => [
        'name' => 'Create New Components',
    ],
    'componentsedit' => [
        'name' => 'Edit Components',
    ],
    'componentsdelete' => [
        'name' => 'Delete Components',
    ],
    'componentsfiles' => [
        'name' => 'Manage Component Files',
        'note' => 'Allows the user to upload, download, and delete files associated with components.',
    ],
    'componentscheckout' => [
        'name' => 'Check Out Components',
        'note' => 'Assign components in inventory by checking them out.',
    ],
    'componentscheckin' => [
        'name' => 'Check In Components',
        'note' => 'Check components back into inventory that are currently checked out.',
    ],
    'kits'   => [
        'name' => 'Vordefinierte Kits',
        'note'       => 'Grants access to the Predefined Kits section of the application.',
    ],
    'kitsview' => [
        'name' => 'View Predefined Kits',
    ],
    'kitscreate' => [
        'name' => 'Create New Predefined Kits',
    ],
    'kitsedit' => [
        'name' => 'Edit Predefined Kits',
    ],
    'kitsdelete' => [
        'name' => 'Delete Predefined Kits',
    ],
    'users'   => [
        'name' => 'Benutzer',
        'note'       => 'Grants access to the Users section of the application.',
    ],
    'predefinedfilters'   => [
        'name' => 'Vordefinierte Filter',
        'note'       => 'Gewährt Zugriff auf Vordefinierte Filter in der App.',
    ],
    'predefinedfiltercreate' => [
        'name' => 'Öffentliche vordefinierte Filter erstellen',
    ],
    'predefinedfilterview' => [
        'name' => 'Öffentliche vordefinierte Filter anzeigen',
    ],
    'predefinedfilteredit' => [
        'name' => 'Öffentliche vordefinierte Filter bearbeiten',
    ],
    'predefinedfilterdelete' => [
        'name' => 'Öffentliche vordefinierte Filter löschen',
    ],
    'usersview' => [
        'name' => 'Benutzer anzeigen',
    ],
    'userscreate' => [
        'name' => 'Create New Users',
    ],
    'usersedit' => [
        'name' => 'Edit Users',
    ],
    'usersdelete' => [
        'name' => 'Delete Users',
    ],
    'models'   => [
        'name' => 'Models',
        'note'       => 'Grants access to the Models section of the application.',
    ],
    'modelsview' => [
        'name' => 'Modelle anzeigen',
    ],

    'modelscreate' => [
        'name' => 'Create New Models',
    ],
    'modelsedit' => [
        'name' => 'Edit Models',
    ],
    'modelsdelete' => [
        'name' => 'Delete Models',
    ],
    'categories'   => [
        'name' => 'Kategorien',
        'note'       => 'Grants access to the Categories section of the application.',
    ],
    'categoriesview' => [
        'name' => 'View Categories',
    ],
    'categoriescreate' => [
        'name' => 'Create New Categories',
    ],
    'categoriesedit' => [
        'name' => 'Edit Categories',
    ],
    'categoriesdelete' => [
        'name' => 'Delete Categories',
    ],
    'departments'   => [
        'name' => 'Abteilungen',
        'note'       => 'Grants access to the Departments section of the application.',
    ],
    'departmentsview' => [
        'name' => 'View Departments',
    ],
    'departmentscreate' => [
        'name' => 'Create New Departments',
    ],
    'departmentsedit' => [
        'name' => 'Edit Departments',
    ],
    'departmentsdelete' => [
        'name' => 'Delete Departments',
    ],
    'locations'   => [
        'name' => 'Standorte',
        'note'       => 'Grants access to the Locations section of the application.',
    ],
    'locationsview' => [
        'name' => 'View Locations',
    ],
    'locationscreate' => [
        'name' => 'Create New Locations',
    ],
    'locationsedit' => [
        'name' => 'Edit Locations',
    ],
    'locationsdelete' => [
        'name' => 'Delete Locations',
    ],
    'status-labels'   => [
        'name' => 'Statusbezeichnungen',
        'note'       => 'Grants access to the Status Labels section of the application used by Assets.',
    ],
    'statuslabelsview' => [
        'name' => 'View Status Labels',
    ],
    'statuslabelscreate' => [
        'name' => 'Create New Status Labels',
    ],
    'statuslabelsedit' => [
        'name' => 'Edit Status Labels',
    ],
    'statuslabelsdelete' => [
        'name' => 'Statusbezeichnung Löschen',
    ],
    'custom-fields'   => [
        'name' => 'Benutzerdefinierte Felder',
        'note'       => 'Gewährt Zugriff auf den Abschnitt Benutzerdefinierte Felder der Anwendung, die von Assets verwendet wird.',
    ],
    'customfieldsview' => [
        'name' => 'Benutzerdefinierte Felder Ansehen',
    ],
    'customfieldscreate' => [
        'name' => 'Neue Benutzerdefinierte Felder erstellen',
    ],
    'customfieldsedit' => [
        'name' => 'Benutzerdefinierte Felder Bearbeiten',
    ],
    'customfieldsdelete' => [
        'name' => 'Benutzerdefinierte Felder Löschen',
    ],
    'suppliers'   => [
        'name' => 'Lieferanten',
        'note'       => 'Gewährt Zugriff auf den Abschnitt Lieferanten der Anwendung.',
    ],
    'suppliersview' => [
        'name' => 'Lieferanten Ansehen',
    ],
    'supplierscreate' => [
        'name' => 'Neue Lieferanten Erstellen',
    ],
    'suppliersedit' => [
        'name' => 'Lieferanten Bearbeiten',
    ],
    'suppliersdelete' => [
        'name' => 'Lieferanten Löschen',
    ],
    'manufacturers'   => [
        'name' => 'Hersteller',
        'note'       => 'Gewährt Zugriff auf den Abschnitt Hersteller der Anwendung.',
    ],
    'manufacturersview' => [
        'name' => 'Hersteller Ansehen',
    ],
    'manufacturerscreate' => [
        'name' => 'Neue Hersteller Erstellen',
    ],
    'manufacturersedit' => [
        'name' => 'Hersteller Bearbeiten',
    ],
    'manufacturersdelete' => [
        'name' => 'Hersteller Löschen',
    ],
    'companies'   => [
        'name' => 'Firmen',
        'note'       => 'Gewährt Zugriff auf den Bereich Firmen der Anwendung.',
    ],
    'companiesview' => [
        'name' => 'Firmen Ansehen',
    ],
    'companiescreate' => [
        'name' => 'Neue Firmen Erstellen',
    ],
    'companiesedit' => [
        'name' => 'Firmen bearbeiten',
    ],
    'companiesdelete' => [
        'name' => 'Firmen Löschen',
    ],
    'user-self-accounts' => [
        'name' => 'Benutzerkonten',
        'note'       => 'Erlaubt Nicht-Administratoren die Möglichkeit, bestimmte Aspekte ihrer eigenen Benutzerkonten zu verwalten.',
    ],
    'selftwo-factor' => [
        'name' => 'Zwei-Faktor-Authentifizierung verwalten',
        'note'       => 'Erlaubt Benutzern die Zwei-Faktor-Authentifizierung für ihre eigenen Konten zu aktivieren, zu deaktivieren und zu verwalten.',
    ],
    'selfapi' => [
        'name' => 'API-Token verwalten',
        'note'       => 'Ermöglicht Benutzern, eigene API-Token zu erstellen, anzuschauen und zu widerrufen. Benutzer-Token haben die gleichen Berechtigungen wie der Benutzer, der sie erstellt hat.',
    ],
    'selfedit-location' => [
        'name' => 'Standort Aktualisieren',
        'note'       => 'Ermöglicht Benutzern den Standort zu bearbeiten, der mit ihrem eigenen Benutzerkonto verknüpft ist.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Assets Selbst Auschecken',
        'note'       => 'Erlaubt es Benutzern Assets ohne Admin-Intervention selbst auszuchecken.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Einkaufspreis Anzeigen',
        'note'       => 'Ermöglicht den Benutzern, den Einkaufspreis von Artikeln in ihrer Account-Ansicht anzuzeigen.',
    ],

    'depreciations' => [
        'name' => 'Abschreibungs-Management',
        'note'       => 'Ermöglicht Benutzern das Verwalten und Anzeigen von Vermögensabschreibungsdaten.',
    ],
    'depreciationsview' => [
        'name' => 'Abschreibungsdetails anzeigen',
    ],
    'depreciationsedit' => [
        'name' => 'Abschreibungseinstellungen bearbeiten',
    ],
    'depreciationsdelete' => [
        'name' => 'Abschreibungen löschen',
    ],
    'depreciationscreate' => [
        'name' => 'Abschreibung erstellen',
    ],

    'predefinedfilters'   => [
        'name' => 'Vordefinierte Filter',
        'note'       => 'Gewährt Zugriff auf Vordefinierte Filter in der App.',
    ],
    'predefinedfiltercreate' => [
        'name' => 'Öffentliche vordefinierte Filter anzeigen',
    ],
    'predefinedfilterview' => [
        'name' => 'Öffentliche vordefinierte Filter erstellen',
    ],
    'predefinedfilteredit' => [
        'name' => 'Öffentliche vordefinierte Filter bearbeiten',
    ],
    'predefinedfilterdelete' => [
        'name' => 'Öffentliche vordefinierte Filter löschen',
    ],

    'grant_all' => 'Erteile alle Berechtigungen für :area',
    'deny_all' => 'Verweigere alle Berechtigungen für :area',
    'inherit_all' => 'Alle Berechtigungen für :area von Berechtigungsgruppen vererben',
    'grant' => 'Erteile Berechtigungen für :area',
    'deny' => 'Verweigere Berechtigungen für :area',
    'inherit' => 'Alle Berechtigungen für :area von Berechtigungsgruppen vererben',
    'use_groups' => 'Wir empfehlen dringend, Berechtigungsgruppen zu verwenden, anstatt individuelle Berechtigungen für eine einfachere Verwaltung zuzuweisen.'

);
