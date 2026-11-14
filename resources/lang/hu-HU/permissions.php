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
        'name' => 'Super user',
        'note' => 'Meghatározza, hogy a felhasználó teljes hozzáféréssel rendelkezik-e az adminisztráció minden területéhez. Ez a beállítás felülír minden egyéb, a rendszerben megadott specifikusabb és korlátozóbb jogosultságot ',
    ],
    'admin' => [
        'name' => 'Adminisztrátori hozzáférés',
        'note' => 'Meghatározza, hogy a felhasználó hozzáfér-e a rendszer legtöbb területéhez, KIVÉVE a Rendszeradminisztrátori beállításokat. Ezek a felhasználók kezelhetik a felhasználókat, helyszíneket, kategóriákat stb., de a Teljes többvállalatos támogatás beállításai korlátozzák őket, amennyiben az engedélyezve van.',
    ],

    'import' => [
        'name' => 'CSV betöltés',
        'note' => 'Ez lehetővé teszi a felhasználók számára az importálást akkor is, ha máshol a rendszerben nincs hozzáférésük a felhasználókhoz, eszközökhöz stb.',
    ],

    'reports' => [
        'name' => 'Hozzáférés a jelentésekhez',
        'note' => 'Meghatározza, hogy a felhasználó hozzáfér-e az alkalmazás Jelentések menüpontjához.',
    ],

    'assets' => [
        'name' => 'Eszközök',
        'note' => 'Hozzáférést biztosít az alkalmazás Eszközök menüpontjához. ',
    ],

    'assetsview' => [
        'name' => 'Eszközök megtekintése',
        'note' => 'Note that users with this permission will also be able to see (not modify or delete) files uploaded to the asset model as well. This is to make it easier to share common documents like user manuals across assets without having to upload them to every asset, and to avoid having to grant the user permission to modify asset files. Users with this permission will also be able to view edit and checkin history.',
    ],

    'assetscreate' => [
        'name' => 'Új eszközök létrehozása',
    ],

    'assetsedit' => [
        'name' => 'Eszközök szerkesztése',
    ],

    'assetsdelete' => [
        'name' => 'Eszközök törlése',
    ],

    'assetscheckin' => [
        'name' => 'Visszavételezés',
        'note' => 'A jelenleg kiadott eszközök visszavételezése a készletbe.',
    ],

    'assetscheckout' => [
        'name' => 'Kiadás',
        'note' => 'Eszközök hozzárendelése a készletből kiadással.',
    ],

    'assetsaudit' => [
        'name' => 'Eszközök auditálása',
        'note' => 'Lehetővé teszi a felhasználó számára, hogy egy eszközt fizikailag leltározottként jelöljön meg.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Igényelhető eszközök megtekintése',
        'note' => 'Allows the user to view items that are marked as requestable.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Titkosított mezők megtekintése',
        'note' => 'Allows the user to view and modify encrypted custom fields on assets.',
    ],

    'accessories' => [
        'name' => 'Tartozékok',
        'note' => 'Grants access to the Accessories section of the application.',
    ],

    'accessoriesview' => [
        'name' => 'Kiegészítők megtekintése',
    ],
    'accessoriescreate' => [
        'name' => 'Új kiegészítők létrehozása',
    ],
    'accessoriesedit' => [
        'name' => 'Kiegészítők szerkesztése',
    ],
    'accessoriesdelete' => [
        'name' => 'Kiegészítők törlése',
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
        'name' => 'Kiegészítő fileok kezelése',
        'note' => 'Allows the user to upload, download, and delete files associated with accessories. (This only makes sense with view privileges or higher.)',
    ],

    'assetsfiles' => [
        'name' => 'Eszköz fileok kezelése',
        'note' => 'Allows the user to upload, download, and delete files associated with assets. (This only makes sense with view privileges or higher.)',
    ],

    'usersfiles' => [
        'name' => 'Felhasználói fileok kezelése',
        'note' => 'Allows the user to upload, download, and delete files associated with users. (This only makes sense with view privileges or higher.)',
    ],

    'modelsfiles' => [
        'name' => 'Modell fileok kezelése',
        'note' => 'Allows the user to upload, download, and delete files associated with asset models on both the model view and the asset view screens. (This only makes sense with view privileges or higher.)',
    ],

    'departmentsfiles' => [
        'name' => 'Manage Department Files',
        'note' => 'Allows the user to upload, download, and delete files associated with departments. (This only makes sense with view privileges or higher.)',
    ],

    'suppliersfiles' => [
        'name' => 'Manage Supplier Files',
        'note' => 'Allows the user to upload, download, and delete files associated with suppliers. (This only makes sense with view privileges or higher.)',
    ],

    'locationsfiles' => [
        'name' => 'Manage Location Files',
        'note' => 'Allows the user to upload, download, and delete files associated with locations.(This only makes sense with view privileges or higher.)',
    ],

    'companiesfiles' => [
        'name' => 'Manage Company Files',
        'note' => 'Allows the user to upload, download, and delete files associated with companies. (This only makes sense with view privileges or higher.)',
    ],

    'consumablesfiles' => [
        'name' => 'Manage Consumable Files',
        'note' => 'Allows the user to upload, download, and delete files associated with consumables. (This only makes sense with view privileges or higher.)',
    ],

    'consumables' => [
        'name' => 'Fogyóeszközök',
        'note' => 'Grants access to the Consumables section of the application.',
    ],
    'consumablesview' => [
        'name' => 'Fogyóeszközök megtekintése',
    ],
    'consumablescreate' => [
        'name' => 'Új fogyóeszközök létrehozása',
    ],
    'consumablesedit' => [
        'name' => 'Fogyóeszközök szerkesztése',
    ],
    'consumablesdelete' => [
        'name' => 'Fogyóeszközök törlése',
    ],
    'consumablescheckout' => [
        'name' => 'Check Out Consumables',
        'note' => 'Assign consumables in inventory by checking them out.',
    ],

    'licenses' => [
        'name' => 'Licencek',
        'note' => 'Grants access to the Licenses section of the application.',
    ],
    'licensesview' => [
        'name' => 'Licenszek megtekintése',
    ],
    'licensescreate' => [
        'name' => 'Új licenszek létrehozása',
    ],
    'licensesedit' => [
        'name' => 'Licenszek szerkesztése',
    ],
    'licensesdelete' => [
        'name' => 'Licenszek törlése',
    ],
    'licensescheckout' => [
        'name' => 'Licenszek hozzárendelése',
        'note' => 'Allows the user to assign licenses to assets or users.',
    ],
    'licensescheckin' => [
        'name' => 'Licensz hozzárendelések eltávolítása',
        'note' => 'Allows the user to unassign licenses from assets or users.',
    ],
    'licensesfiles' => [
        'name' => 'Licensz fileok kezelése',
        'note' => 'Allows the user to upload, download, and delete files associated with licenses.',
    ],
    'componentsfiles' => [
        'name' => 'Manage Component Files',
        'note' => 'Allows the user to upload, download, and delete files associated with components.',
    ],

    'licenseskeys' => [
        'name' => 'Manage License Keys',
        'note' => 'Allows the user to view product keys associated with licenses.',
    ],
    'components' => [
        'name' => 'Alkatrészek',
        'note' => 'Grants access to the Components section of the application.',
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

    'componentscheckout' => [
        'name' => 'Check Out Components',
        'note' => 'Assign components in inventory by checking them out.',
    ],
    'componentscheckin' => [
        'name' => 'Check In Components',
        'note' => 'Check components back into inventory that are currently checked out.',
    ],
    'kits' => [
        'name' => 'Előre definiált csomagok',
        'note' => 'Grants access to the Predefined Kits section of the application.',
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
    'users' => [
        'name' => 'Felhasználók',
        'note' => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'Felhasználók megtekintése',
        'note' => 'Users with this permission will also be able to view edit and checkin history. Access to files uploaded to a user is gated separately by the Manage User Files permission.',
    ],
    'userscreate' => [
        'name' => 'Create New Users',
    ],
    'usersedit' => [
        'name' => 'Edit Users',
    ],
    'usersdelete' => [
        'name' => 'Felhasználók törlése',
    ],
    'models' => [
        'name' => 'Modellek',
        'note' => 'Grants access to the Models section of the application.',
    ],
    'modelsview' => [
        'name' => 'Modellek megtekintése',
    ],

    'modelscreate' => [
        'name' => 'Új modellek létrehozása',
    ],
    'modelsedit' => [
        'name' => 'Modellek szerkesztése',
    ],
    'modelsdelete' => [
        'name' => 'Modellek törlése',
    ],
    'categories' => [
        'name' => 'Kategóriák',
        'note' => 'Hozzáférése biztosítása az alkalmazás "Kategóriák" részéhez.',
    ],
    'categoriesview' => [
        'name' => 'Kategóriák megtekintése',
    ],
    'categoriescreate' => [
        'name' => 'Új kategóriák létrehozása',
    ],
    'categoriesedit' => [
        'name' => 'Kategóriák szerkesztése',
    ],
    'categoriesdelete' => [
        'name' => 'Kategóriák törlése',
    ],
    'departments' => [
        'name' => 'Osztályok',
        'note' => 'Grants access to the Departments section of the application.',
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
    'locations' => [
        'name' => 'Helyek',
        'note' => 'Grants access to the Locations section of the application.',
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
    'status-labels' => [
        'name' => 'Státusz címkék',
        'note' => 'Grants access to the Status Labels section of the application used by Assets.',
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
    'custom-fields' => [
        'name' => 'Egyéni mezők',
        'note' => 'Hozzáférése biztosítása az alkalmazásben az Eszközök által használt "Egyéni mezők" részhez.',
    ],
    'customfieldsview' => [
        'name' => 'Egyéni mezők megtekintése',
    ],
    'customfieldscreate' => [
        'name' => 'Új egyéni mezők létrehozása',
    ],
    'customfieldsedit' => [
        'name' => 'Egyéni mezők szerkesztése',
    ],
    'customfieldsdelete' => [
        'name' => 'Egyéni mezők törlése',
    ],
    'suppliers' => [
        'name' => 'Beszállítók',
        'note' => 'Hozzáférése biztosítása az alkalmazás "Beszállítók" részéhez.',
    ],
    'suppliersview' => [
        'name' => 'Beszállítók megtekintése',
    ],
    'supplierscreate' => [
        'name' => 'Új beszállítók létrehozása',
    ],
    'suppliersedit' => [
        'name' => 'Beszállítók szerkesztése',
    ],
    'suppliersdelete' => [
        'name' => 'Beszállítók törlése',
    ],
    'manufacturers' => [
        'name' => 'Gyártók',
        'note' => 'Hozzáférése biztosítása az alkalmazás "Gyártók" részéhez.',
    ],
    'manufacturersview' => [
        'name' => 'Gyártók megtekintése',
    ],
    'manufacturerscreate' => [
        'name' => 'Új gyártók létrehozása',
    ],
    'manufacturersedit' => [
        'name' => 'Gyártók szerkesztése',
    ],
    'manufacturersdelete' => [
        'name' => 'Gyártók törlése',
    ],
    'companies' => [
        'name' => 'Cégek',
        'note' => 'Grants access to the Companies section of the application.',
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
        'note' => 'Grants non-admin users the ability to manage certain aspects of their own user accounts.',
    ],
    'selftwo-factor' => [
        'name' => 'Kétfaktoros azonosítás kezelése',
        'note' => 'Allows users to enable, disable, and manage two-factor authentication for their own accounts.',
    ],
    'selfapi' => [
        'name' => 'API Tokenek kezelése',
        'note' => 'Allows users to create, view, and revoke their own API tokens. User tokens will have the same permissions as the user who created them.',
    ],
    'selfedit-location' => [
        'name' => 'Edit Location',
        'note' => 'Allows users to edit the location associated with their own user account.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Self Check Out Assets',
        'note' => 'Allows users to check out assets to themselves without admin intervention.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'View Purchase Cost',
        'note' => 'Allows users to view the purchase cost of items in their account view.',
    ],

    'depreciations' => [
        'name' => 'Depreciation Management',
        'note' => 'Allows users to manage and view asset depreciation details.',
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
    'use_groups' => 'We strongly suggest using Permission Groups instead of assigning individual permissions for easier management.',

];
