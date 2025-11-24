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
        'name' => 'Super User',
        'note'       => 'Determina se l\'utente ha accesso completo a tutti gli aspetti dell\'amministrazione. Questa impostazione sostituisce TUTTE le autorizzazioni più specifiche e restrittive nel sistema ',
    ],
    'admin' => [
        'name' => 'Accesso Amministratore',
        'note'       => 'Determina se l\'utente ha accesso alla maggior parte degli aspetti del sistema, TRANNE le Impostazioni di Amministrazione di Sistema. Questi utenti potranno gestire utenti, sedi, categorie, ecc., ma SONO vincolati dal Supporto Completo per Più Aziende, se abilitato.',
    ],

    'import' => [
        'name' => 'Importazione CSV',
        'note'       => 'Questo consentirà agli utenti di importare anche se l\'accesso a utenti, asset, ecc è negato altrove.',
    ],

    'reports' => [
        'name' => 'Rapporti di accesso',
        'note'       => 'Determina se l\'utente ha accesso alla sezione Report dell\'applicazione.',
    ],

    'assets' =>
        [
            'name' => 'Beni',
            'note' => 'Concede l\'accesso alla sezione Assets dell\'applicazione.',
    ],

    'assetsview' => [
        'name' => 'Visualizza Asset',
    ],

    'assetscreate' => [
        'name' => 'Crea Nuovi Asset',
    ],

    'assetsedit' => [
        'name' => 'Modifica Asset',
    ],

    'assetsdelete' => [
        'name' => 'Elimina Asset',
    ],

    'assetscheckin' => [
        'name' => 'Check In',
        'note' => 'Controlla gli asset di nuovo nell\'inventario che sono attualmente in fase di verifica.',
    ],

    'assetscheckout' => [
        'name' => 'Check Out',
        'note' => 'Assegnare asset nell\'inventario verificandole.',
    ],

    'assetsaudit' => [
        'name' => 'Revisione contabile Asset',
        'note' => 'Consente all\'utente di contrassegnare un asset come fisicamente inventariato.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Visualizza Asset Richiedibili',
        'note' => 'Consente all\'utente di visualizzare gli asset contrassegnati come richiedibili.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Visualizza Campi Personalizzati Criptati',
        'note' => 'Consente all\'utente di visualizzare e modificare i campi personalizzati crittografati negli asset.',
    ],

    'accessories'   => [
        'name' => 'Accessori',
        'note'       => 'Consente l\'accesso alla sezione Accessori dell\'applicazione.',
    ],

    'accessoriesview' => [
        'name' => 'Visualizza Accessori',
    ],
    'accessoriescreate' => [
        'name' => 'Crea Nuovi Accessori',
    ],
    'accessoriesedit' => [
        'name' => 'Modifica Accessori',
    ],
    'accessoriesdelete' => [
        'name' => 'Elimina Accessori',
    ],
    'accessoriescheckout' => [
        'name' => 'Check Out Accessori',
        'note' => 'Assegnare gli accessori nell\'inventario controllandoli.',
    ],
    'accessoriescheckin' => [
        'name' => 'Check In Accessori',
        'note' => 'Controlla gli accessori di nuovo nell\'inventario che sono attualmente in check out.',
    ],
    'accessoriesfiles' => [
        'name' => 'Gestisce File Accessori',
        'note' => 'Consente all\'utente di caricare, scaricare ed eliminare i file associati agli accessori.',
    ],
    'consumables'   => [
        'name' => 'Consumabili',
        'note'       => 'Concede l\'accesso alla sezione Consumabili dell\'applicazione.',
    ],
    'consumablesview' => [
        'name' => 'Visualizza Consumabili',
    ],
    'consumablescreate' => [
        'name' => 'Crea Nuovi Consumabili',
    ],
    'consumablesedit' => [
        'name' => 'Modifica Consumabili',
    ],
    'consumablesdelete' => [
        'name' => 'Elimina Consumabili',
    ],
    'consumablescheckout' => [
        'name' => 'Check Out Consumabili',
        'note' => 'Assegnare i materiali di consumo nell\'inventario verificandoli.',
    ],
    'consumablesfiles' => [
        'name' => 'Gestisci file consumabili',
        'note' => 'Consente all\'utente di caricare, scaricare ed eliminare i file associati ai materiali di consumo.',
    ],
    'licenses'   => [
        'name' => 'Licenze',
        'note'       => 'Concede l\'accesso alla sezione Licenze dell\'applicazione.',
    ],
    'licensesview' => [
        'name' => 'Visualizza Licenze',
    ],
    'licensescreate' => [
        'name' => 'Crea Nuove Licenze',
    ],
    'licensesedit' => [
        'name' => 'Modifica Licenze',
    ],
    'licensesdelete' => [
        'name' => 'Elimina Licenze',
    ],
    'licensescheckout' => [
        'name' => 'Assegna Licenze',
        'note' => 'Consente all\'utente di assegnare licenze ad asset o utenti.',
        ],
    'licensescheckin' => [
        'name' => 'Annulla assegnazione licenze',
        'note' => 'Consente all\'utente di annullare l\'assegnazione di licenze da asset o utenti.',
    ],
    'licensesfiles' => [
        'name' => 'Gestisci File Licenza',
        'note' => 'Consente all\'utente di caricare, scaricare ed eliminare i file associati alle licenze.',
    ],
    'licenseskeys' => [
        'name' => 'Gestisci Chiavi Licenza',
        'note' => 'Consente all\'utente di visualizzare le chiavi del prodotto associate alle licenze.',
    ],
    'components'   => [
        'name' => 'Componenti',
        'note'       => 'Consente l\'accesso alla sezione Componenti dell\'applicazione.',
    ],
    'componentsview' => [
        'name' => 'Visualizza Componenti',
    ],
    'componentscreate' => [
        'name' => 'Crea Nuovi Componenti',
    ],
    'componentsedit' => [
        'name' => 'Modifica Componenti',
    ],
    'componentsdelete' => [
        'name' => 'Elimina Componenti',
    ],
    'componentsfiles' => [
        'name' => 'Gestisci File Componenti',
        'note' => 'Consente all\'utente di caricare, scaricare ed eliminare i file associati ai componenti.',
    ],
    'componentscheckout' => [
        'name' => 'Check Out Componenti',
        'note' => 'Assegna componenti nell\'inventario controllandoli.',
    ],
    'componentscheckin' => [
        'name' => 'Check In Componenti',
        'note' => 'Controlla i componenti di nuovo nell\'inventario che sono attualmente controllati.',
    ],
    'kits'   => [
        'name' => 'Kit Predefiniti',
        'note'       => 'Consente l\'accesso alla sezione Kit Predefiniti dell\'applicazione.',
    ],
    'kitsview' => [
        'name' => 'Visualizza Kit Predefiniti',
    ],
    'kitscreate' => [
        'name' => 'Crea Nuovi Kit Predefiniti',
    ],
    'kitsedit' => [
        'name' => 'Modifica Kit Predefiniti',
    ],
    'kitsdelete' => [
        'name' => 'Elimina Kit Predefiniti',
    ],
    'users'   => [
        'name' => 'Utenti',
        'note'       => 'Consente l\'accesso alla sezione Utenti dell\'applicazione.',
    ],
    'usersview' => [
        'name' => 'Visualizza utenti',
    ],
    'userscreate' => [
        'name' => 'Crea Nuovi Utenti',
    ],
    'usersedit' => [
        'name' => 'Modifica Utenti',
    ],
    'usersdelete' => [
        'name' => 'Elimina Utenti',
    ],
    'models'   => [
        'name' => 'Modelli',
        'note'       => 'Consente l\'accesso alla sezione Modelli dell\'applicazione.',
    ],
    'modelsview' => [
        'name' => 'Visualizza i modelli',
    ],

    'modelscreate' => [
        'name' => 'Crea Nuovi Modelli',
    ],
    'modelsedit' => [
        'name' => 'Modifica Modelli',
    ],
    'modelsdelete' => [
        'name' => 'Elimina Modelli',
    ],
    'categories'   => [
        'name' => 'Categorie',
        'note'       => 'Concede l\'accesso alla sezione Categorie dell\'applicazione.',
    ],
    'categoriesview' => [
        'name' => 'Visualizza Categorie',
    ],
    'categoriescreate' => [
        'name' => 'Crea Nuove Categorie',
    ],
    'categoriesedit' => [
        'name' => 'Modifica Categorie',
    ],
    'categoriesdelete' => [
        'name' => 'Elimina Categorie',
    ],
    'departments'   => [
        'name' => 'Reparti',
        'note'       => 'Concede l\'accesso alla sezione Dipartimenti dell\'applicazione.',
    ],
    'departmentsview' => [
        'name' => 'Visualizza Dipartimenti',
    ],
    'departmentscreate' => [
        'name' => 'Crea nuovi dipartimenti',
    ],
    'departmentsedit' => [
        'name' => 'Modifica Dipartimenti',
    ],
    'departmentsdelete' => [
        'name' => 'Elimina Dipartimenti',
    ],
    'locations'   => [
        'name' => 'Sedi',
        'note'       => 'Concede l\'accesso alla sezione Posizioni dell\'applicazione.',
    ],
    'locationsview' => [
        'name' => 'Visualizza Posizioni',
    ],
    'locationscreate' => [
        'name' => 'Crea Nuove Posizioni',
    ],
    'locationsedit' => [
        'name' => 'Modifica Posizioni',
    ],
    'locationsdelete' => [
        'name' => 'Elimina Posizioni',
    ],
    'status-labels'   => [
        'name' => 'Etichette di Stato',
        'note'       => 'Concede l\'accesso alla sezione Etichette di stato dell\'applicazione utilizzata da Assets.',
    ],
    'statuslabelsview' => [
        'name' => 'Visualizza Etichette Di Stato',
    ],
    'statuslabelscreate' => [
        'name' => 'Crea nuove etichette di stato',
    ],
    'statuslabelsedit' => [
        'name' => 'Modifica Etichette Di Stato',
    ],
    'statuslabelsdelete' => [
        'name' => 'Elimina Etichette Di Stato',
    ],
    'custom-fields'   => [
        'name' => 'Campi Personalizzati',
        'note'       => 'Consente l\'accesso alla sezione Campi Personalizzati dell\'applicazione utilizzata da Assets.',
    ],
    'customfieldsview' => [
        'name' => 'Visualizza Campi Personalizzati',
    ],
    'customfieldscreate' => [
        'name' => 'Crea Nuovi Campi Personalizzati',
    ],
    'customfieldsedit' => [
        'name' => 'Modifica Campi Personalizzati',
    ],
    'customfieldsdelete' => [
        'name' => 'Elimina Campi Personalizzati',
    ],
    'suppliers'   => [
        'name' => 'Fornitori',
        'note'       => 'Consente l\'accesso alla sezione Fornitori dell\'applicazione.',
    ],
    'suppliersview' => [
        'name' => 'Visualizza Fornitori',
    ],
    'supplierscreate' => [
        'name' => 'Crea Nuovi Fornitori',
    ],
    'suppliersedit' => [
        'name' => 'Modifica Fornitori',
    ],
    'suppliersdelete' => [
        'name' => 'Elimina Fornitori',
    ],
    'manufacturers'   => [
        'name' => 'Produttori',
        'note'       => 'Concede l\'accesso alla sezione Produttori dell\'applicazione.',
    ],
    'manufacturersview' => [
        'name' => 'Visualizza Produttori',
    ],
    'manufacturerscreate' => [
        'name' => 'Crea Nuovi Produttori',
    ],
    'manufacturersedit' => [
        'name' => 'Modifica Produttori',
    ],
    'manufacturersdelete' => [
        'name' => 'Elimina Produttori',
    ],
    'companies'   => [
        'name' => 'Aziende',
        'note'       => 'Concede l\'accesso alla sezione Aziende dell\'applicazione.',
    ],
    'companiesview' => [
        'name' => 'Visualizza Aziende',
    ],
    'companiescreate' => [
        'name' => 'Crea Nuove Aziende',
    ],
    'companiesedit' => [
        'name' => 'Modifica Aziende',
    ],
    'companiesdelete' => [
        'name' => 'Elimina Aziende',
    ],
    'user-self-accounts' => [
        'name' => 'Account utente personali',
        'note'       => 'Permette agli utenti non-admin di gestire alcuni aspetti dei propri account utente.',
    ],
    'selftwo-factor' => [
        'name' => 'Gestisci Autenticazione A Due Fattori',
        'note'       => 'Consente agli utenti di abilitare, disabilitare e gestire l\'autenticazione a due fattori per i propri account.',
    ],
    'selfapi' => [
        'name' => 'Gestisci i token API',
        'note'       => 'Consente agli utenti di creare, visualizzare e revocare i propri token API. I token utente avranno gli stessi permessi dell\'utente che li ha creati.',
    ],
    'selfedit-location' => [
        'name' => 'Modifica Posizione',
        'note'       => 'Consente agli utenti di modificare la posizione associata al proprio account utente.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Asset per il self-check-out',
        'note'       => 'Consente agli utenti di controllare gli asset a se stessi senza intervento amministratore.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Visualizza Costo Acquisto',
        'note'       => 'Consente agli utenti di visualizzare il costo di acquisto degli articoli nella vista del proprio account.',
    ],

    'depreciations' => [
        'name' => 'Gestione degli ammortamenti',
        'note'       => 'Consente agli utenti di gestire e visualizzare i dettagli dell\'ammortamento degli asset.',
    ],
    'depreciationsview' => [
        'name' => 'Visualizza Dettagli Di Ammortamento',
    ],
    'depreciationsedit' => [
        'name' => 'Modifica Impostazioni Di Ammortamento',
    ],
    'depreciationsdelete' => [
        'name' => 'Elimina Record Di Ammortamento',
    ],
    'depreciationscreate' => [
        'name' => 'Crea Record Di Ammortamento',
    ],

    'grant_all' => 'Concedi tutti i permessi per :area',
    'deny_all' => 'Nega tutti i permessi per :area',
    'inherit_all' => 'Eredita tutti i permessi per :area dai gruppi di permessi',
    'grant' => 'Concedi il permesso per :area',
    'deny' => 'Nega il permesso per :area',
    'inherit' => 'Eredita il permesso per :area da gruppi di permessi',
    'use_groups' => 'Si consiglia vivamente di utilizzare i gruppi di permessi invece di assegnare i permessi individuali per una gestione più semplice.'

);
