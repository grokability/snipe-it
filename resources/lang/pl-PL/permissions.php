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
        'name' => 'Super użytkownik',
        'note'       => 'Determines whether the user has full access to all aspects of the admin. This setting overrides ALL more specific and restrictive permissions throughout the system. ',
    ],
    'admin' => [
        'name' => 'Dostęp administratora',
        'note'       => 'Determines whether the user has access to most aspects of the system EXCEPT the System Admin Settings. These users will be able to manage users, locations, categories, etc, but ARE constrained by Full Multiple Company Support if it is enabled.',
    ],

    'import' => [
        'name' => 'Importuj CSV',
        'note'       => 'Pozwoli to użytkownikom importować, nawet jeśli dostęp do użytkowników, zasobów itd. został odebrany w innym miejscu.',
    ],

    'reports' => [
        'name' => 'Reports Access',
        'note'       => 'Określa czy użytkownik ma dostęp do sekcji "Raporty".',
    ],

    'assets' =>
        [
            'name' => 'Środki',
            'note' => 'Udziela dostęp do sekcji "Zasoby".',
    ],

    'assetsview' => [
        'name' => 'Pokaż zasoby',
    ],

    'assetscreate' => [
        'name' => 'Stwórz nowe zasoby',
    ],

    'assetsedit' => [
        'name' => 'Edytuj zasoby',
    ],

    'assetsdelete' => [
        'name' => 'Usuń zasoby',
    ],

    'assetscheckin' => [
        'name' => 'Zarejestruj',
        'note' => 'Zwróć obecnie zajęte zasoby, do puli dostępnych.',
    ],

    'assetscheckout' => [
        'name' => '',
        'note' => 'Assign assets in inventory by checking them out.',
    ],

    'assetsaudit' => [
        'name' => 'Audyt zasobów',
        'note' => 'Allows the user to mark an asset as physically inventoried.',
    ],

    'assetsviewrequestable' => [
        'name' => 'View Requestable Assets',
        'note' => 'Allows the user to view assets that are marked as requestable.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'View Encrypted Custom Fields',
        'note' => 'Allows the user to view and modify encrypted custom fields on assets.',
    ],

    'accessories'   => [
        'name' => 'Akcesoria',
        'note'       => 'Grants access to the Accessories section of the application.',
    ],

    'accessoriesview' => [
        'name' => 'View Accessories',
    ],
    'accessoriescreate' => [
        'name' => 'Create New Accessories',
    ],
    'accessoriesedit' => [
        'name' => 'Edit Accessories',
    ],
    'accessoriesdelete' => [
        'name' => 'Delete Accessories',
    ],
    'accessoriescheckout' => [
        'name' => 'Check Out Accessories',
        'note' => 'Assign accessories in inventory by checking them out.',
    ],
    'accessoriescheckin' => [
        'name' => 'Check In Accessories',
        'note' => 'Check accessories back into inventory that are currently checked out.',
    ],
    'accessoriesfiles' => [
        'name' => 'Manage Accessory Files',
        'note' => 'Allows the user to upload, download, and delete files associated with accessories.',
    ],
    'consumables'   => [
        'name' => 'Materiały eksploatacyjne',
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
        'name' => 'Licencje',
        'note'       => 'Udziela dostępu do części licencyjnej aplikacji.',
    ],
    'licensesview' => [
        'name' => 'Pokaż licencje',
    ],
    'licensescreate' => [
        'name' => 'Stwórz nowe licencje',
    ],
    'licensesedit' => [
        'name' => 'Edytuj licencje',
    ],
    'licensesdelete' => [
        'name' => 'Usuń licencje',
    ],
    'licensescheckout' => [
        'name' => 'Przypisz licencje',
        'note' => 'Pozwala użytkownikowi przypisać licencję do zasobu bądź użytkownika.',
        ],
    'licensescheckin' => [
        'name' => 'Odepnij licencje',
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
        'name' => 'Składniki',
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
        'name' => 'Predefiniowane zestawy',
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
        'name' => 'Użytkownicy',
        'note'       => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'Przeglądaj użytkowników',
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
        'name' => 'Pokaż Modele',
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
        'name' => 'Kategorie',
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
        'name' => 'Działy',
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
        'name' => 'Lokalizacje',
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
        'name' => 'Status',
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
        'name' => 'Pola niestandardowe',
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
        'name' => 'Dostawcy',
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
        'name' => 'Producenci',
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
        'name' => 'Firmy',
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
