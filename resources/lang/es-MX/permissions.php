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
        'name' => 'Súper Usuario',
        'note'       => 'Determines whether the user has full access to all aspects of the admin. This setting overrides ALL more specific and restrictive permissions throughout the system. ',
    ],
    'admin' => [
        'name' => 'Admin Access',
        'note'       => 'Determines whether the user has access to most aspects of the system EXCEPT the System Admin Settings. These users will be able to manage users, locations, categories, etc, but ARE constrained by Full Multiple Company Support if it is enabled.',
    ],

    'import' => [
        'name' => 'CSV Import',
        'note'       => 'This will allow users to import even if access to users, assets, etc is denied elsewhere.',
    ],

    'reports' => [
        'name' => 'Reports Access',
        'note'       => 'Determines whether the user has access to the Reports section of the application.',
    ],

    'assets' =>
        [
            'name' => 'Activos',
            'note' => 'Otorga acceso a la sección Activos de la aplicación.',
    ],

    'assetsview' => [
        'name' => 'Ver activos',
    ],

    'assetscreate' => [
        'name' => 'Crear nuevos activos',
    ],

    'assetsedit' => [
        'name' => 'Editar activos',
    ],

    'assetsdelete' => [
        'name' => 'Eliminar activos',
    ],

    'assetscheckin' => [
        'name' => 'Devolver',
        'note' => 'Devolver activos entregados a inventario.',
    ],

    'assetscheckout' => [
        'name' => 'Entregar',
        'note' => 'Assign assets in inventory by checking them out.',
    ],

    'assetsaudit' => [
        'name' => 'Auditar activos',
        'note' => 'Permite al usuario marcar un activo como inventoriado físicamente.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Ver activos solicitables',
        'note' => 'Permite al usuario ver activos marcados como solicitables.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'View Encrypted Custom Fields',
        'note' => 'Allows the user to view and modify encrypted custom fields on assets.',
    ],

    'accessories'   => [
        'name' => 'Accesorios',
        'note'       => 'Otorga acceso a la sección Accesorios de la aplicación.',
    ],

    'accessoriesview' => [
        'name' => 'Ver Accesorios',
    ],
    'accessoriescreate' => [
        'name' => 'Crear nuevo Accesorio',
    ],
    'accessoriesedit' => [
        'name' => 'Editar Accesorios',
    ],
    'accessoriesdelete' => [
        'name' => 'Eliminar Accesorios',
    ],
    'accessoriescheckout' => [
        'name' => 'Entregar Accesorios',
        'note' => 'Asignar accesiorios en inventario al entregarlos.',
    ],
    'accessoriescheckin' => [
        'name' => 'Devolver accesorios.',
        'note' => 'Devolver accesorios que están entregados al inventario.',
    ],
    'accessoriesfiles' => [
        'name' => 'Administrar archivos de accesorios',
        'note' => 'Permite al usuario subir, descargar, y eliminar archivos asociados a accesorios.',
    ],
    'consumables'   => [
        'name' => 'Consumibles',
        'note'       => 'Otorga acceso a la sección Consumibles de la aplicación.',
    ],
    'consumablesview' => [
        'name' => 'Ver consumibles',
    ],
    'consumablescreate' => [
        'name' => 'Crear nuevos consumibles',
    ],
    'consumablesedit' => [
        'name' => 'Editar consumibles',
    ],
    'consumablesdelete' => [
        'name' => 'Eliminar consumibles',
    ],
    'consumablescheckout' => [
        'name' => 'Entregar Consumibles',
        'note' => 'Asignar consumibles en inventario al entregarlos.',
    ],
    'consumablesfiles' => [
        'name' => 'Administrar archivos de Consumibles.',
        'note' => 'Permite al usuario subir, descargar, y eliminar archivos asociados a consumibles.',
    ],
    'licenses'   => [
        'name' => 'Licencias',
        'note'       => 'Otorga acceso a la sección Licencias de la aplicación.',
    ],
    'licensesview' => [
        'name' => 'Ver Licencias',
    ],
    'licensescreate' => [
        'name' => 'Crear nueva Licencia',
    ],
    'licensesedit' => [
        'name' => 'Editar Licencias',
    ],
    'licensesdelete' => [
        'name' => 'Eliminar Licencias',
    ],
    'licensescheckout' => [
        'name' => 'Asignar Licencias',
        'note' => 'Permite al usuario asignar licencias a activos o usuarios.',
        ],
    'licensescheckin' => [
        'name' => 'Desasignar Licencias',
        'note' => 'Permite al usuario desasignar licencias a activos o usuarios.',
    ],
    'licensesfiles' => [
        'name' => 'Administrar archivos de Licencias',
        'note' => 'Permite al usuario subir, descargar, y eliminar archivos asociados a licencias.',
    ],
    'licenseskeys' => [
        'name' => 'Administrar claves de licencia',
        'note' => 'Permite al usuario ver las claves de producto asociadas a licencias.',
    ],
    'components'   => [
        'name' => 'Componentes',
        'note'       => 'Otorga acceso a la sección Componentes de la aplicación.',
    ],
    'componentsview' => [
        'name' => 'Ver Componentes',
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
        'name' => '',
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
        'name' => 'Kits predefinidos',
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
        'name' => 'Usuarios',
        'note'       => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'Ver usuarios',
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
        'name' => 'Ver modelos',
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
        'name' => 'Categorías',
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
        'name' => 'Departamentos',
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
        'name' => 'Ubicaciones',
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
        'name' => 'Etiquetas de estado',
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
        'name' => 'Campos personalizados',
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
        'name' => 'Proveedores',
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
        'name' => 'Fabricantes',
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
        'name' => 'Compañías',
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
