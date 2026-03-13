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
        'name' => 'Super Benutzer',
        'note'       => 'Legt fest, ob der Benutzer vollen Zugriff auf alle Aspekte des Administrators hat. Diese Einstellung überschreibt ALLE spezifischeren und restriktiveren Berechtigungen im gesamten System. ',
    ],
    'admin' => [
        'name' => 'Admin-Zugriff',
        'note'       => 'Legt fest, ob der Benutzer Zugriff auf die meisten Aspekte des Systems AUSSER in den Systemeinstellungen hat. Diese Benutzer werden in der Lage sein, Benutzer, Standorte, Kategorien, etc, zu verwalten, aber SIND beschränkt durch die Volle Mehrmandanten-Unterstützung für Firmen, wenn sie aktiviert ist.',
    ],

    'import' => [
        'name' => 'CSV-Import',
        'note'       => 'Dies wird Benutzern erlauben zu importieren, auch wenn der Zugriff auf Benutzer, Gegenstände usw. an anderer Stelle verweigert wird.',
    ],

    'reports' => [
        'name' => 'Berichtszugriff',
        'note'       => 'Legt fest, ob der Benutzer Zugriff auf den Berichte-Abschnitt der Anwendung hat.',
    ],

    'assets' =>
        [
            'name' => 'Assets',
            'note' => 'Gewährt Zugriff auf den Bereich "Assets" in der Anwendung.',
    ],

    'assetsview' => [
        'name' => 'Assets Anzeigen',
    ],

    'assetscreate' => [
        'name' => 'Neue Assets Erstellen',
    ],

    'assetsedit' => [
        'name' => 'Assets Bearbeiten',
    ],

    'assetsdelete' => [
        'name' => 'Assets Löschen',
    ],

    'assetscheckin' => [
        'name' => 'Einchecken',
        'note' => 'Checken Sie die derzeit ausgebuchten Assets wieder in das Inventar ein.',
    ],

    'assetscheckout' => [
        'name' => 'Auschecken',
        'note' => 'Assets im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],

    'assetsaudit' => [
        'name' => 'Assets Prüfung',
        'note' => 'Ermöglicht dem Benutzer, ein Asset als physisch inventarisiert zu markieren.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Anforderbare Assets anzeigen',
        'note' => 'Ermöglicht dem Benutzer, Assets anzusehen, die als anforderbar markiert sind.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Verschlüsselte Benutzerdefinierte Felder ansehen',
        'note' => 'Ermöglicht dem Benutzer, verschlüsselte benutzerdefinierte Felder auf Assets anzusehen und zu ändern.',
    ],

    'accessories'   => [
        'name' => 'Zubehör',
        'note'       => 'Gewährt Zugriff auf den Bereich "Zubehör" in der Anwendung.',
    ],

    'accessoriesview' => [
        'name' => 'Zubehör Ansehen',
    ],
    'accessoriescreate' => [
        'name' => 'Neues Zubehör erstellen',
    ],
    'accessoriesedit' => [
        'name' => 'Zubehör Bearbeiten',
    ],
    'accessoriesdelete' => [
        'name' => 'Zubehör Löschen',
    ],
    'accessoriescheckout' => [
        'name' => 'Zubehör Auschecken',
        'note' => 'Zubehör im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],
    'accessoriescheckin' => [
        'name' => 'Zubehör Einchecken',
        'note' => 'Checken Sie die derzeit ausgebuchtes Zubehör wieder in das Inventar ein.',
    ],
    'accessoriesfiles' => [
        'name' => 'Zubehördateien Verwalten',
        'note' => 'Ermöglicht dem Benutzer das Hochladen, Herunterladen und Löschen in Verbindung mit Zubehör.',
    ],
    'consumables'   => [
        'name' => 'Verbrauchsmaterialien',
        'note'       => 'Gewährt Zugriff auf den Bereich "Verbrauchsmaterialien" in der Anwendung.',
    ],
    'consumablesview' => [
        'name' => 'Verbrauchsmaterialien Ansehen',
    ],
    'consumablescreate' => [
        'name' => 'Neue Verbrauchsmaterialien erstellen',
    ],
    'consumablesedit' => [
        'name' => 'Verbrauchsmaterialien Bearbeiten',
    ],
    'consumablesdelete' => [
        'name' => 'Verbrauchsmaterialien Löschen',
    ],
    'consumablescheckout' => [
        'name' => 'Verbrauchsmaterialien Auschecken',
        'note' => 'Verbrauchsmaterialien im Inventar zuweisen, indem sie ausgecheckt werden.',
    ],
    'consumablesfiles' => [
        'name' => 'Verbrauchsdateien verwalten',
        'note' => 'Ermöglicht dem Benutzer das Hochladen, Herunterladen und Löschen in Verbindung mit Verbrauchsmaterialien.',
    ],
    'licenses'   => [
        'name' => 'Lizenzen',
        'note'       => 'Gewährt Zugriff auf den Bereich "Lizenzen" in der Anwendung.',
    ],
    'licensesview' => [
        'name' => 'Lizenzen Ansehen',
    ],
    'licensescreate' => [
        'name' => 'Neue Lizenz erstellen',
    ],
    'licensesedit' => [
        'name' => 'Lizenz Bearbeiten',
    ],
    'licensesdelete' => [
        'name' => 'Lizenzen Löschen',
    ],
    'licensescheckout' => [
        'name' => 'Lizenzen Zuweisen',
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
        'name' => 'Statusbezeichnungen Löschen',
    ],
    'custom-fields'   => [
        'name' => 'Benutzerdefinierte Felder',
        'note'       => 'Gewährt Zugriff auf den Bereich "Benutzerdefinierte Felder", die für Assets benutzt werden.',
    ],
    'customfieldsview' => [
        'name' => 'Benutzerdefinierte Felder Anzeigen',
    ],
    'customfieldscreate' => [
        'name' => 'Neue Benutzerdefinierte Felder Erstellen',
    ],
    'customfieldsedit' => [
        'name' => 'Benutzerdefinierte Felder Bearbeiten',
    ],
    'customfieldsdelete' => [
        'name' => 'Benutzerdefinierte Felder Löschen',
    ],
    'suppliers'   => [
        'name' => 'Lieferanten',
        'note'       => 'Gewährt Zugriff auf den Bereich "Lieferanten" in der Anwendung.',
    ],
    'suppliersview' => [
        'name' => 'Lieferanten Anzeigen',
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
        'note'       => 'Gewährt Zugriff auf den Bereich "Hersteller" in der Anwendung.',
    ],
    'manufacturersview' => [
        'name' => 'Hersteller Anzeigen',
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
        'note'       => 'Gewährt Zugriff auf den Bereich "Firmen" in der Anwendung.',
    ],
    'companiesview' => [
        'name' => 'Firmen Anzeigen',
    ],
    'companiescreate' => [
        'name' => 'Neue Firmen Erstellen',
    ],
    'companiesedit' => [
        'name' => 'Firmen Bearbeiten',
    ],
    'companiesdelete' => [
        'name' => 'Firmen Löschen',
    ],
    'user-self-accounts' => [
        'name' => 'Benutzerkonten',
        'note'       => 'Erlaubt Nicht-Administratoren die Möglichkeit, bestimmte Aspekte ihrer eigenen Benutzerkonten zu verwalten.',
    ],
    'selftwo-factor' => [
        'name' => 'Zwei-Faktor-Authentifizierung Verwalten',
        'note'       => 'Erlaubt Benutzern die Zwei-Faktor-Authentifizierung für ihre eigenen Konten zu aktivieren, zu deaktivieren und zu verwalten.',
    ],
    'selfapi' => [
        'name' => 'API-Schlüssel Verwalten',
        'note'       => 'Ermöglicht Benutzern, eigene API-Token zu erstellen, anzuschauen und zu widerrufen. Benutzer-Token haben die gleichen Berechtigungen wie der Benutzer, der sie erstellt hat.',
    ],
    'selfedit-location' => [
        'name' => 'Standort Bearbeiten',
        'note'       => 'Ermöglicht Benutzern den Standort zu bearbeiten, der mit ihrem eigenen Benutzerkonto verknüpft ist.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Assets Selbst Auschecken',
        'note'       => 'Erlaubt es Benutzern Assets ohne Admin-Intervention selbst auszuchecken.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Kaufpreis Anzeigen',
        'note'       => 'Ermöglicht den Benutzern, die Kaufpreis von Artikeln in ihrer Account-Ansicht anzuzeigen.',
    ],

    'depreciations' => [
        'name' => 'Abschreibungs-Verwaltung',
        'note'       => 'Ermöglicht Benutzern das Verwalten und Anzeigen von Vermögensabschreibungsdaten.',
    ],
    'depreciationsview' => [
        'name' => 'Abschreibungsdetails Anzeigen',
    ],
    'depreciationsedit' => [
        'name' => 'Abschreibungseinstellungen Bearbeiten',
    ],
    'depreciationsdelete' => [
        'name' => 'Abschreibungs-Aufzeichnungen Löschen',
    ],
    'depreciationscreate' => [
        'name' => 'Abschreibungs-Aufzeichnungen Erstellen',
    ],

    'grant_all' => 'Erteilen Sie alle Berechtigungen für :area',
    'deny_all' => 'Verweigerung aller Berechtigungen für :area',
    'inherit_all' => 'Alle Berechtigungen für :area von Berechtigungsgruppen Vererben',
    'grant' => 'Erteilung von Berechtigungen für :area',
    'deny' => 'Verweigerung von Berechtigungen für :area',
    'inherit' => 'Berechtigungen für :area von Berechtigungsgruppen Vererben',
    'use_groups' => 'Wir empfehlen dringend, Berechtigungsgruppen zu verwenden, anstatt individuelle Berechtigungen für eine einfachere Verwaltung zuzuweisen.'

);
