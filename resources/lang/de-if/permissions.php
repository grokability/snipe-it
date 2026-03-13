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
        'name' => 'Zuweisung von Lizenzen Aufheben',
        'note' => 'Ermöglicht dem Benutzer, die Zuweisung von Lizenzen von Assets oder Benutzern aufzuheben.',
    ],
    'licensesfiles' => [
        'name' => 'Lizenzdateien Verwalten',
        'note' => 'Ermöglicht dem Benutzer das Hochladen, Herunterladen und Löschen in Verbindung mit Lizenzen.',
    ],
    'licenseskeys' => [
        'name' => 'Lizenzschlüssel Verwalten',
        'note' => 'Ermöglicht dem Benutzer, Produktschlüssel anzuzeigen, die mit Lizenzen verknüpft sind.',
    ],
    'components'   => [
        'name' => 'Komponenten',
        'note'       => 'Gewährt Zugriff auf den Bereich "Komponenten" in der Anwendung.',
    ],
    'componentsview' => [
        'name' => 'Komponenten Anzeigen',
    ],
    'componentscreate' => [
        'name' => 'Neue Komponenten Erstellen',
    ],
    'componentsedit' => [
        'name' => 'Komponenten Bearbeiten',
    ],
    'componentsdelete' => [
        'name' => 'Komponenten Löschen',
    ],
    'componentsfiles' => [
        'name' => 'Komponentendateien Verwalten',
        'note' => 'Ermöglicht dem Benutzer das Hochladen, Herunterladen und Löschen in Verbindung mit Komponenten.',
    ],
    'componentscheckout' => [
        'name' => 'Komponenten Auschecken',
        'note' => 'Komponenten im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],
    'componentscheckin' => [
        'name' => 'Komponenten einchecken',
        'note' => 'Checken Sie die derzeit ausgebuchten Komponenten wieder in das Inventar ein.',
    ],
    'kits'   => [
        'name' => 'Vordefinierte Kits',
        'note'       => 'Gewährt Zugriff auf den Abschnitt "Vordefinierte Kits" in der Anwendung.',
    ],
    'kitsview' => [
        'name' => 'Vordefinierte Kits Anzeigen',
    ],
    'kitscreate' => [
        'name' => 'Vordefiniertes Kits Erstellen',
    ],
    'kitsedit' => [
        'name' => 'Vordefinierte Kits Bearbeiten',
    ],
    'kitsdelete' => [
        'name' => 'Vordefinierte Kits Löschen',
    ],
    'users'   => [
        'name' => 'Benutzer',
        'note'       => 'Gewährt Zugriff auf den Bereich "Benutzer" in der Anwendung.',
    ],
    'usersview' => [
        'name' => 'Benutzer anzeigen',
    ],
    'userscreate' => [
        'name' => 'Neue Benutzer Anlegen',
    ],
    'usersedit' => [
        'name' => 'Benutzer Bearbeiten',
    ],
    'usersdelete' => [
        'name' => 'Benutzer löschen',
    ],
    'models'   => [
        'name' => 'Modelle',
        'note'       => 'Gewährt Zugriff auf den Bereich "Modelle" in der Anwendung.',
    ],
    'modelsview' => [
        'name' => 'Modelle anzeigen',
    ],

    'modelscreate' => [
        'name' => 'Neue Modelle Erstellen',
    ],
    'modelsedit' => [
        'name' => 'Modelle Bearbeiten',
    ],
    'modelsdelete' => [
        'name' => 'Modelle Löschen',
    ],
    'categories'   => [
        'name' => 'Kategorien',
        'note'       => 'Gewährt Zugriff auf den Bereich "Kategorien" in der Anwendung.',
    ],
    'categoriesview' => [
        'name' => 'Kategorien Anzeigen',
    ],
    'categoriescreate' => [
        'name' => 'Neue Kategorien Erstellen',
    ],
    'categoriesedit' => [
        'name' => 'Kategorien Bearbeiten',
    ],
    'categoriesdelete' => [
        'name' => 'Kategorien Löschen',
    ],
    'departments'   => [
        'name' => 'Abteilungen',
        'note'       => 'Gewährt Zugriff auf den Bereich "Abteilungen" in der Anwendung.',
    ],
    'departmentsview' => [
        'name' => 'Abteilungen Anzeigen',
    ],
    'departmentscreate' => [
        'name' => 'Neue Abteilungen Erstellen',
    ],
    'departmentsedit' => [
        'name' => 'Abteilungen Bearbeiten',
    ],
    'departmentsdelete' => [
        'name' => 'Abteilungen Löschen',
    ],
    'locations'   => [
        'name' => 'Standorte',
        'note'       => 'Gewährt Zugriff auf den Bereich "Standorte" in der Anwendung.',
    ],
    'locationsview' => [
        'name' => 'Standorte Anzeigen',
    ],
    'locationscreate' => [
        'name' => 'Neue Standorte Erstellen',
    ],
    'locationsedit' => [
        'name' => 'Standorte Bearbeiten',
    ],
    'locationsdelete' => [
        'name' => 'Standorte Löschen',
    ],
    'status-labels'   => [
        'name' => 'Statusbezeichnungen',
        'note'       => 'Gewährt Zugriff auf den Bereich "Statusbezeichnungen", die für Assets benutzt werden.',
    ],
    'statuslabelsview' => [
        'name' => 'Statusbezeichnungen Anzeigen',
    ],
    'statuslabelscreate' => [
        'name' => 'Neue Statusbezeichnungen Erstellen',
    ],
    'statuslabelsedit' => [
        'name' => 'Statusbezeichnungen Bearbeiten',
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

    'grant_all' => 'Erteile alle Berechtigungen für :area',
    'deny_all' => 'Verweigere alle Berechtigungen für :area',
    'inherit_all' => 'Alle Berechtigungen für :area von Berechtigungsgruppen vererben',
    'grant' => 'Erteile Berechtigungen für :area',
    'deny' => 'Verweigere Berechtigungen für :area',
    'inherit' => 'Alle Berechtigungen für :area von Berechtigungsgruppen vererben',
    'use_groups' => 'Wir empfehlen dringend, Berechtigungsgruppen zu verwenden, anstatt individuelle Berechtigungen für eine einfachere Verwaltung zuzuweisen.'

);
