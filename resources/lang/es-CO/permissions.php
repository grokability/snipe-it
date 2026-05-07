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
        'name' => 'Super Usuario',
        'note' => 'Determines whether the user has full access to all aspects of the admin. This setting overrides ALL more specific and restrictive permissions throughout the system. ',
    ],
    'admin' => [
        'name' => 'Acceso de administrador',
        'note' => 'Determines whether the user has access to most aspects of the system EXCEPT the System Admin Settings. These users will be able to manage users, locations, categories, etc, but ARE constrained by Full Multiple Company Support if it is enabled.',
    ],

    'import' => [
        'name' => 'Importar CSV',
        'note' => 'This will allow users to import even if access to users, assets, etc is denied elsewhere.',
    ],

    'reports' => [
        'name' => 'Acceso a Informes',
        'note' => 'Determines whether the user has access to the Reports section of the application.',
    ],

    'assets' => [
        'name' => 'Activos',
        'note' => 'Grants access to the Assets section of the application.',
    ],

    'assetsview' => [
        'name' => 'Ver activos',
    ],

    'assetscreate' => [
        'name' => 'Crear nuevo activo',
    ],

    'assetsedit' => [
        'name' => 'Editar Activo',
    ],

    'assetsdelete' => [
        'name' => 'Eliminar Activos',
    ],

    'assetscheckin' => [
        'name' => 'Check In',
        'note' => 'Check assets back into inventory that are currently checked out.',
    ],

    'assetscheckout' => [
        'name' => 'Check Out',
        'note' => 'Assign assets in inventory by checking them out.',
    ],

    'assetsaudit' => [
        'name' => 'Auditar Activos',
        'note' => 'Allows the user to mark an asset as physically inventoried.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Ver activos solicitados',
        'note' => 'Allows the user to view assets that are marked as requestable.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Ver Campos Personalizados Encriptados',
        'note' => 'Allows the user to view and modify encrypted custom fields on assets.',
    ],

    'accessories' => [
        'name' => 'Accesorios',
        'note' => 'Grants access to the Accessories section of the application.',
    ],

    'accessoriesview' => [
        'name' => 'Ver Accesorios',
    ],
    'accessoriescreate' => [
        'name' => 'Crear nuevos accesorios',
    ],
    'accessoriesedit' => [
        'name' => 'Editar Accesorios',
    ],
    'accessoriesdelete' => [
        'name' => 'Eliminar Accesorios',
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
        'name' => 'Ges',
        'note' => 'Allows the user to upload, download, and delete files associated with accessories. (This only makes sense with view privileges or higher.)',
    ],

    'assetsfiles' => [
        'name' => 'Gestionar Archivos de Activos',
        'note' => 'Allows the user to upload, download, and delete files associated with assets. (This only makes sense with view privileges or higher.)',
    ],

    'usersfiles' => [
        'name' => 'Administrar archivos de usuario',
        'note' => 'Allows the user to upload, download, and delete files associated with users. (This only makes sense with view privileges or higher.)',
    ],

    'modelsfiles' => [
        'name' => 'Gestionar Archivos de Modelos',
        'note' => 'Allows the user to upload, download, and delete files associated with asset models on both the model view and the asset view screens. (This only makes sense with view privileges or higher.)',
    ],

    'departmentsfiles' => [
        'name' => 'Gestionar Ficheros del Departamento',
        'note' => 'Allows the user to upload, download, and delete files associated with departments. (This only makes sense with view privileges or higher.)',
    ],

    'suppliersfiles' => [
        'name' => 'Gestionar Ficheros del Proveedor',
        'note' => 'Allows the user to upload, download, and delete files associated with suppliers. (This only makes sense with view privileges or higher.)',
    ],

    'locationsfiles' => [
        'name' => 'Gestionar Archivos de Ubicaciones',
        'note' => 'Allows the user to upload, download, and delete files associated with locations.(This only makes sense with view privileges or higher.)',
    ],

    'companiesfiles' => [
        'name' => 'Gestionar Archivos de Empresa',
        'note' => 'Permite al usuario subir, descargar y eliminar archivos asociados con empresas. (Esto sólo tiene sentido con privilegios de visualización o superiores)',
    ],

    'consumablesfiles' => [
        'name' => 'Gestionar Ficheros de los Consumibles',
        'note' => 'Allows the user to upload, download, and delete files associated with consumables. (This only makes sense with view privileges or higher.)',
    ],

    'consumables' => [
        'name' => 'Consumibles',
        'note' => 'Grants access to the Consumables section of the application.',
    ],
    'consumablesview' => [
        'name' => 'View Consumables',
    ],
    'consumablescreate' => [
        'name' => 'Crear nuevos consumibles',
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

    'licenses' => [
        'name' => 'Licencias',
        'note' => 'Grants access to the Licenses section of the application.',
    ],
    'licensesview' => [
        'name' => 'Ver licencias',
    ],
    'licensescreate' => [
        'name' => 'Crear Nuevas Licencias',
    ],
    'licensesedit' => [
        'name' => 'Editar Licencias',
    ],
    'licensesdelete' => [
        'name' => 'Eliminar Licencias',
    ],
    'licensescheckout' => [
        'name' => 'Asignar Licencias',
        'note' => 'Permitir al usuario asignar licencias a activos o a usuarios.',
    ],
    'licensescheckin' => [
        'name' => 'Desvinculación Licencias',
        'note' => 'Permite al usuario desvincular licencias de activos o de usuarios.',
    ],
    'licensesfiles' => [
        'name' => 'Gestionar Ficheros de Licencias',
        'note' => 'Permitir al usuario subir, descargar, y eliminar ficheros asociados a licencias. ',
    ],
    'componentsfiles' => [
        'name' => 'Gestionar Ficheros de Componentes',
        'note' => 'Allows the user to upload, download, and delete files associated with components.',
    ],

    'licenseskeys' => [
        'name' => 'Administrar claves de licencia',
        'note' => 'Allows the user to view product keys associated with licenses.',
    ],
    'components' => [
        'name' => 'Componentes',
        'note' => 'Grants access to the Components section of the application.',
    ],
    'componentsview' => [
        'name' => 'Ver componentes',
    ],
    'componentscreate' => [
        'name' => 'Crear nuevos componentes',
    ],
    'componentsedit' => [
        'name' => 'Editar Componentes',
    ],
    'componentsdelete' => [
        'name' => 'Eliminar Componentes',
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
        'name' => 'Kits predefinidos',
        'note' => 'Grants access to the Predefined Kits section of the application.',
    ],
    'kitsview' => [
        'name' => 'Ver Kits Predefinidos',
    ],
    'kitscreate' => [
        'name' => 'Crear Nuevos Kit Predefinidos',
    ],
    'kitsedit' => [
        'name' => 'Editar Kits Predefinidos',
    ],
    'kitsdelete' => [
        'name' => 'Eliminar Kits Predefinidos',
    ],
    'users' => [
        'name' => 'Usuarios',
        'note' => 'Grants access to the Users section of the application.',
    ],
    'usersview' => [
        'name' => 'Ver usuarios',
    ],
    'userscreate' => [
        'name' => 'Crear Nuevos Usuarios',
    ],
    'usersedit' => [
        'name' => 'Editar Usuarios',
    ],
    'usersdelete' => [
        'name' => 'Eliminar Usuarios',
    ],
    'models' => [
        'name' => 'Modelos',
        'note' => 'Dar acceso a la sección de Modelos de la aplicación.',
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
    'categories' => [
        'name' => 'Categorías',
        'note' => 'Grants access to the Categories section of the application.',
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
    'departments' => [
        'name' => 'Departamentos',
        'note' => 'Grants access to the Departments section of the application.',
    ],
    'departmentsview' => [
        'name' => 'Ver Departamentos',
    ],
    'departmentscreate' => [
        'name' => 'Create New Departments',
    ],
    'departmentsedit' => [
        'name' => 'Editar Departamentos',
    ],
    'departmentsdelete' => [
        'name' => 'Eliminar Departamentos',
    ],
    'locations' => [
        'name' => 'Ubicaciones',
        'note' => 'Grants access to the Locations section of the application.',
    ],
    'locationsview' => [
        'name' => 'Ver Ubicaciones',
    ],
    'locationscreate' => [
        'name' => 'Crear Nuevas Ubicaciones',
    ],
    'locationsedit' => [
        'name' => 'Editar Ubicaciones',
    ],
    'locationsdelete' => [
        'name' => 'Eliminar Ubicaciones',
    ],
    'status-labels' => [
        'name' => 'Etiquetas de estado',
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
        'name' => 'Campos personalizados',
        'note' => 'Grants access to the Custom Fields section of the application used by Assets.',
    ],
    'customfieldsview' => [
        'name' => 'Ver Campos Personalizados',
    ],
    'customfieldscreate' => [
        'name' => 'Crear Nuevos Campos Personalizados',
    ],
    'customfieldsedit' => [
        'name' => 'Editar Campos Personalizados',
    ],
    'customfieldsdelete' => [
        'name' => 'Eliminar Campos Personalizados',
    ],
    'suppliers' => [
        'name' => 'Proveedores',
        'note' => 'Dar acceso a la sección de Proveedores  de la aplicación. ',
    ],
    'suppliersview' => [
        'name' => 'Ver proveedor',
    ],
    'supplierscreate' => [
        'name' => 'Crear nuevos proveedores',
    ],
    'suppliersedit' => [
        'name' => 'Editar Proveedores',
    ],
    'suppliersdelete' => [
        'name' => 'Eliminar Proveedores',
    ],
    'manufacturers' => [
        'name' => 'Fabricantes',
        'note' => 'Grants access to the Manufacturers section of the application.',
    ],
    'manufacturersview' => [
        'name' => 'Ver Fabricantes',
    ],
    'manufacturerscreate' => [
        'name' => 'Crear Nuevos Fabricantes',
    ],
    'manufacturersedit' => [
        'name' => 'Editar Fabricantes',
    ],
    'manufacturersdelete' => [
        'name' => 'Eliminar Fabricantes',
    ],
    'companies' => [
        'name' => 'Compañías',
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
        'name' => 'Administrar Autenticación de dos Factores',
        'note' => 'Permite a los usuarios habilitar, desactivar y administrar la autenticación de dos factores para sus propias cuentas.',
    ],
    'selfapi' => [
        'name' => 'Administrar las claves del API',
        'note' => 'Allows users to create, view, and revoke their own API tokens. User tokens will have the same permissions as the user who created them.',
    ],
    'selfedit-location' => [
        'name' => 'Editar Ubicación',
        'note' => 'Allows users to edit the location associated with their own user account.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Self Check Out Assets',
        'note' => 'Allows users to check out assets to themselves without admin intervention.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Ver Coste de Compra',
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

    'grant_all' => 'Permitir todos los permisos para :area',
    'deny_all' => 'Deny all permissions for :area',
    'inherit_all' => 'Heredar todos los permisos del :area de grupos de permisos',
    'grant' => 'Otorgar permiso para :area',
    'deny' => 'Denegar permiso para :area',
    'inherit' => 'Heredar permiso para :area de los grupos de permisos',
    'use_groups' => 'We strongly suggest using Permission Groups instead of assigning individual permissions for easier management.',

];
