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
        'name' => 'Superpoužívateľ',
        'note'       => 'Určuje, či má používateľ plný prístup ku všetkým aspektom administrácie. Toto nastavenie prepíše VŠETKY špecifickejšie a reštriktívnejšie povolenia v celom systéme. ',
    ],
    'admin' => [
        'name' => 'Prístup pre správcu',
        'note'       => 'Určuje, či má používateľ prístup k väčšine aspektov systému OKREM nastavení správcu systému. Títo používatelia budú môcť spravovať používateľov, lokality, kategórie atď., ale SÚ obmedzení plnou podporou viacerých spoločností, ak je povolená.',
    ],

    'import' => [
        'name' => 'CSV import',
        'note'       => 'To umožní používateľom importovať aj keď je prístup k používateľom, majetkom atď. inde zamietnutý.',
    ],

    'reports' => [
        'name' => 'Prístup k reportom',
        'note'       => 'Určuje, či má používateľ prístup k sekcii Reporty v aplikácii.',
    ],

    'assets' =>
        [
            'name' => 'Položky majetku',
            'note' => 'Udeľuje prístup k sekcii Majetky v aplikácii.',
    ],

    'assetsview' => [
        'name' => 'Zobraziť Majetky',
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
        'name' => 'Zobraziť požadované majetky',
        'note' => 'Umožňuje používateľovi zobraziť položky majetku, ktoré sú označené ako vyžiadateľné.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Zobraziť šifrované vlastné polia',
        'note' => 'Umožňuje používateľovi zobraziť a upraviť zašifrované vlastné polia v prvkoch.',
    ],

    'accessories'   => [
        'name' => 'Príslušenstvo',
        'note'       => 'Umožňuje prístup do sekcie Príslušenstvo v aplikácii.',
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
        'note' => 'Check accessories back into inventory that are currently checked out.',
    ],
    'accessoriesfiles' => [
        'name' => 'Manage Accessory Files',
        'note' => 'Allows the user to upload, download, and delete files associated with accessories.',
    ],
    'consumables'   => [
        'name' => 'Spotrebný materiál',
        'note'       => 'Grants access to the Consumables section of the application.',
    ],
    'consumablesview' => [
        'name' => 'View Consumables',
    ],
    'consumablescreate' => [
        'name' => 'Create New Consumables',
    ],
    'consumablesedit' => [
        'name' => 'Edit Consumables',
    ],
    'consumablesdelete' => [
        'name' => 'Delete Consumables',
    ],
    'consumablescheckout' => [
        'name' => 'Check Out Consumables',
        'note' => 'Assign consumables in inventory by checking them out.',
    ],
    'consumablesfiles' => [
        'name' => 'Manage Consumable Files',
        'note' => 'Allows the user to upload, download, and delete files associated with consumables.',
    ],
    'licenses'   => [
        'name' => 'Licencie',
        'note'       => 'Grants access to the Licenses section of the application.',
    ],
    'licensesview' => [
        'name' => 'View Licenses',
    ],
    'licensescreate' => [
        'name' => 'Create New Licenses',
    ],
    'licensesedit' => [
        'name' => 'Edit Licenses',
    ],
    'licensesdelete' => [
        'name' => 'Delete Licenses',
    ],
    'licensescheckout' => [
        'name' => 'Assign Licenses',
        'note' => 'Allows the user to assign licenses to assets or users.',
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
        'name' => 'Komponenty',
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
        'name' => 'Predvolené sady',
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
        'name' => 'Používatelia',
        'note'       => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'Zobraziť používateľa',
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
        'name' => 'Zobraziť modely',
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
        'name' => 'Kategórie',
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
        'name' => 'Oddelenia',
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
        'name' => 'Lokality',
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
        'name' => 'Stavy',
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
        'name' => 'Delete Status Labels',
    ],
    'custom-fields'   => [
        'name' => 'Vlastné polia',
        'note'       => 'Grants access to the Custom Fields section of the application used by Assets.',
    ],
    'customfieldsview' => [
        'name' => 'View Custom Fields',
    ],
    'customfieldscreate' => [
        'name' => 'Create New Custom Fields',
    ],
    'customfieldsedit' => [
        'name' => 'Edit Custom Fields',
    ],
    'customfieldsdelete' => [
        'name' => 'Delete Custom Fields',
    ],
    'suppliers'   => [
        'name' => 'Dodávatelia',
        'note'       => 'Grants access to the Suppliers section of the application.',
    ],
    'suppliersview' => [
        'name' => 'View Suppliers',
    ],
    'supplierscreate' => [
        'name' => 'Create New Suppliers',
    ],
    'suppliersedit' => [
        'name' => 'Edit Suppliers',
    ],
    'suppliersdelete' => [
        'name' => 'Delete Suppliers',
    ],
    'manufacturers'   => [
        'name' => 'Výrobcovia',
        'note'       => 'Grants access to the Manufacturers section of the application.',
    ],
    'manufacturersview' => [
        'name' => 'View Manufacturers',
    ],
    'manufacturerscreate' => [
        'name' => 'Create New Manufacturers',
    ],
    'manufacturersedit' => [
        'name' => 'Edit Manufacturers',
    ],
    'manufacturersdelete' => [
        'name' => 'Delete Manufacturers',
    ],
    'companies'   => [
        'name' => 'Spoločnosti',
        'note'       => 'Grants access to the Companies section of the application.',
    ],
    'companiesview' => [
        'name' => 'View Companies',
    ],
    'companiescreate' => [
        'name' => 'Create New Companies',
    ],
    'companiesedit' => [
        'name' => 'Edit Companies',
    ],
    'companiesdelete' => [
        'name' => 'Delete Companies',
    ],
    'user-self-accounts' => [
        'name' => 'User Self Accounts',
        'note'       => 'Grants non-admin users the ability to manage certain aspects of their own user accounts.',
    ],
    'selftwo-factor' => [
        'name' => 'Manage Two-Factor Authentication',
        'note'       => 'Allows users to enable, disable, and manage two-factor authentication for their own accounts.',
    ],
    'selfapi' => [
        'name' => 'Manage API Tokens',
        'note'       => 'Allows users to create, view, and revoke their own API tokens. User tokens will have the same permissions as the user who created them.',
    ],
    'selfedit-location' => [
        'name' => 'Edit Location',
        'note'       => 'Allows users to edit the location associated with their own user account.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Self Check Out Assets',
        'note'       => 'Allows users to check out assets to themselves without admin intervention.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'View Purchase Cost',
        'note'       => 'Allows users to view the purchase cost of items in their account view.',
    ],

    'depreciations' => [
        'name' => 'Depreciation Management',
        'note'       => 'Allows users to manage and view asset depreciation details.',
    ],
    'depreciationsview' => [
        'name' => 'View Depreciation Details',
    ],
    'depreciationsedit' => [
        'name' => 'Edit Depreciation Settings',
    ],
    'depreciationsdelete' => [
        'name' => 'Delete Depreciation Records',
    ],
    'depreciationscreate' => [
        'name' => 'Create Depreciation Records',
    ],

    'grant_all' => 'Grant all permissions for :area',
    'deny_all' => 'Deny all permissions for :area',
    'inherit_all' => 'Inherit all permissions for :area from permission groups',
    'grant' => 'Grant Permission for :area',
    'deny' => 'Deny Permission for :area',
    'inherit' => 'Inherit Permission for :area from permission groups',
    'use_groups' => 'We strongly suggest using Permission Groups instead of assigning individual permissions for easier management.'

);
