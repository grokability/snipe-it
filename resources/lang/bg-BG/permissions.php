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
        'name' => 'Супер Администратор',
        'note' => 'Определя дали потребителят има пълен достъп до всички аспекти на администраторския панел. Тази настройка отменя ВСИЧКИ по-специфични и ограничителни разрешения в системата. ',
    ],
    'admin' => [
        'name' => 'Администраторски достъп',
        'note' => 'Определя дали потребителят има достъп до повечето аспекти на системата, ОСВЕН настройките на системния администратор. Тези потребители ще могат да управляват потребители, местоположения, категории и др., но СА ограничени от пълната поддръжка за множество компании, ако е активирана.',
    ],

    'import' => [
        'name' => 'CVS Импорт',
        'note' => 'Това ще позволи импортиране на потребители, дори и ако достъпа до списък с потребители или активи и др. е забранен на друго място.',
    ],

    'reports' => [
        'name' => 'Достъп Спавки',
        'note' => 'Определя дали потребителя има достъп до справките в програмата.',
    ],

    'assets' => [
        'name' => 'Активи',
        'note' => 'Дава достъп до раздел активи в програмата.',
    ],

    'assetsview' => [
        'name' => 'Преглед на активи',
    ],

    'assetscreate' => [
        'name' => 'Създаване на нови активи',
    ],

    'assetsedit' => [
        'name' => 'Редакция на активи',
    ],

    'assetsdelete' => [
        'name' => 'Изтриване на активи',
    ],

    'assetscheckin' => [
        'name' => 'Вписване',
        'note' => 'Дава достъп за вписване на активи обраното в системата.',
    ],

    'assetscheckout' => [
        'name' => 'Изписване',
        'note' => 'Дава достъп за изписване на активи към потребители.',
    ],

    'assetsaudit' => [
        'name' => 'Инвентаризация на активи',
        'note' => 'Дава достъп на потребителя да прави инвентаризация.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Вижда активите за поискване',
        'note' => 'Дава достъп на потребителя да вижда активите, които са разрешени за поискване.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Вижда криптирани полета',
        'note' => 'Дава достъп на потребителя да вижда и да модифицира криптираните полета на активите.',
    ],

    'accessories' => [
        'name' => 'Аксесоари',
        'note' => 'Дава достъп до раздел аксесоари в програмата.',
    ],

    'accessoriesview' => [
        'name' => 'Вижда аксесоарите',
    ],
    'accessoriescreate' => [
        'name' => 'Създава нови аксесоари',
    ],
    'accessoriesedit' => [
        'name' => 'Редактира аксесоарите',
    ],
    'accessoriesdelete' => [
        'name' => 'Изтрива аксесоарите',
    ],
    'accessoriescheckout' => [
        'name' => 'Изписва аксесоарите',
        'note' => 'Дава достъп за изписване на аскесоарите към потребители.',
    ],
    'accessoriescheckin' => [
        'name' => 'Вписва аксесоари',
        'note' => 'Дава достъп за вписване на аксесоари обратно в системата.',
    ],
    'accessoriesfiles' => [
        'name' => 'Управление на файловете на аксесоарите',
        'note' => 'Allows the user to upload, download, and delete files associated with accessories. (This only makes sense with view privileges or higher.)',
    ],

    'assetsfiles' => [
        'name' => 'Manage Asset Files',
        'note' => 'Allows the user to upload, download, and delete files associated with assets. (This only makes sense with view privileges or higher.)',
    ],

    'usersfiles' => [
        'name' => 'Manage User Files',
        'note' => 'Allows the user to upload, download, and delete files associated with users. (This only makes sense with view privileges or higher.)',
    ],

    'modelsfiles' => [
        'name' => 'Manage Model Files',
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
        'name' => 'Управление на файловете на консумативите',
        'note' => 'Allows the user to upload, download, and delete files associated with consumables. (This only makes sense with view privileges or higher.)',
    ],

    'consumables' => [
        'name' => 'Консумативи',
        'note' => 'Дава достъп до раздел консумативи в програмата.',
    ],
    'consumablesview' => [
        'name' => 'Вижда консумативи',
    ],
    'consumablescreate' => [
        'name' => 'Създава консумативи',
    ],
    'consumablesedit' => [
        'name' => 'Редактира консумативи',
    ],
    'consumablesdelete' => [
        'name' => 'Изтрива консумативи',
    ],
    'consumablescheckout' => [
        'name' => 'Изписва консумативи',
        'note' => 'Дава достъп за изписване на консумативи към потребители.',
    ],

    'licenses' => [
        'name' => 'Лицензи',
        'note' => 'Дава достъп до раздел лицензи в програмата.',
    ],
    'licensesview' => [
        'name' => 'Вижда лицензи',
    ],
    'licensescreate' => [
        'name' => 'Създава лицензи',
    ],
    'licensesedit' => [
        'name' => 'Редактира лицензи',
    ],
    'licensesdelete' => [
        'name' => 'Изтрива лицензи',
    ],
    'licensescheckout' => [
        'name' => 'Изписва лицензи',
        'note' => 'Дава достъп за изписване на лицензи към потребители.',
    ],
    'licensescheckin' => [
        'name' => 'Вписва лицензи',
        'note' => 'Дава достъп за вписване на лицензи обратно в системата.',
    ],
    'licensesfiles' => [
        'name' => 'Управление на файловете на лицензите',
        'note' => 'Дава достъп за качване, сваляне и изтриване на файлове към лицензите.',
    ],
    'componentsfiles' => [
        'name' => 'Управлява файловете за компоненти',
        'note' => 'Дава достъп за качване, сваляне и изтриване на файлове към компонентите.',
    ],

    'licenseskeys' => [
        'name' => 'Управление на лицензионни ключове',
        'note' => 'Дава достъп на потребителя да вижда лицензионни ключове към лицензите.',
    ],
    'components' => [
        'name' => 'Компоненти',
        'note' => 'Дава достъп до раздел компоненти в програмата.',
    ],
    'componentsview' => [
        'name' => 'Вижда компоненти',
    ],
    'componentscreate' => [
        'name' => 'Създава компоненти',
    ],
    'componentsedit' => [
        'name' => 'Редактира компоненти',
    ],
    'componentsdelete' => [
        'name' => 'Изтрива компоненти',
    ],

    'componentscheckout' => [
        'name' => 'Изписване на компоненти',
        'note' => 'Дава достъп за изписване на компоненти към потребители.',
    ],
    'componentscheckin' => [
        'name' => 'Вписване на компоненти',
        'note' => 'Дава достъп за вписване на компоненти обратно в системата.',
    ],
    'kits' => [
        'name' => 'Комплекти',
        'note' => 'Дава достъп до раздел комплекти в програмата.',
    ],
    'kitsview' => [
        'name' => 'Вижда комплекти',
    ],
    'kitscreate' => [
        'name' => 'Създава комплекти',
    ],
    'kitsedit' => [
        'name' => 'Редактира комплекти',
    ],
    'kitsdelete' => [
        'name' => 'Изтрива комплекти',
    ],
    'users' => [
        'name' => 'Потребители',
        'note' => 'Дава достъп до раздел потребители в програмата.',
    ],
    'usersview' => [
        'name' => 'Преглед на потребителите',
    ],
    'userscreate' => [
        'name' => 'Създава нови потребители',
    ],
    'usersedit' => [
        'name' => 'Редактира потребители',
    ],
    'usersdelete' => [
        'name' => 'Изтрива потребители',
    ],
    'models' => [
        'name' => 'Модели',
        'note' => 'Дава достъп до раздел модели в програмата.',
    ],
    'modelsview' => [
        'name' => 'Преглед на моделите',
    ],

    'modelscreate' => [
        'name' => 'Създава нови модели',
    ],
    'modelsedit' => [
        'name' => 'Редактира модели',
    ],
    'modelsdelete' => [
        'name' => 'Изтрива модели',
    ],
    'categories' => [
        'name' => 'Категории',
        'note' => 'Дава достъп до раздел категорий в програмата.',
    ],
    'categoriesview' => [
        'name' => 'Вижда категорий',
    ],
    'categoriescreate' => [
        'name' => 'Създава нови категорий',
    ],
    'categoriesedit' => [
        'name' => 'Редактира категорий',
    ],
    'categoriesdelete' => [
        'name' => 'Изтрива категорий',
    ],
    'departments' => [
        'name' => 'Отдели',
        'note' => 'Дава достъп до раздел отдели в програмата.',
    ],
    'departmentsview' => [
        'name' => 'Вижда отдели',
    ],
    'departmentscreate' => [
        'name' => 'Създава нови отдели',
    ],
    'departmentsedit' => [
        'name' => 'Редактира отдели',
    ],
    'departmentsdelete' => [
        'name' => 'Изтрива отдели',
    ],
    'locations' => [
        'name' => 'Местоположения',
        'note' => 'Дава достъп до раздел местоположения в програмата.',
    ],
    'locationsview' => [
        'name' => 'Вижда местоположения',
    ],
    'locationscreate' => [
        'name' => 'Създава нови местоположения',
    ],
    'locationsedit' => [
        'name' => 'Редактира местоположения',
    ],
    'locationsdelete' => [
        'name' => 'Изтрива местоположения',
    ],
    'status-labels' => [
        'name' => 'Статус Етикети',
        'note' => 'Дава достъп до раздел статус етикети в програмата.',
    ],
    'statuslabelsview' => [
        'name' => 'Вижда статус етикети',
    ],
    'statuslabelscreate' => [
        'name' => 'Създава нови статус етикети',
    ],
    'statuslabelsedit' => [
        'name' => 'Редактира статус етикети',
    ],
    'statuslabelsdelete' => [
        'name' => 'Изтрива статус етикети',
    ],
    'custom-fields' => [
        'name' => 'Потребителски полета',
        'note' => 'Дава достъп до раздел потребителски полета в програмата.',
    ],
    'customfieldsview' => [
        'name' => 'Вижда потребителски полета',
    ],
    'customfieldscreate' => [
        'name' => 'Създава нови потребителски полета',
    ],
    'customfieldsedit' => [
        'name' => 'Редактира потребителски полета',
    ],
    'customfieldsdelete' => [
        'name' => 'Изтрива потребителски полета',
    ],
    'suppliers' => [
        'name' => 'Доставчици',
        'note' => 'Дава достъп до раздел доставчици в програмата.',
    ],
    'suppliersview' => [
        'name' => 'Вижда доставчици',
    ],
    'supplierscreate' => [
        'name' => 'Създава нови доставчици',
    ],
    'suppliersedit' => [
        'name' => 'Редактира доставчици',
    ],
    'suppliersdelete' => [
        'name' => 'Изтрива доставчици',
    ],
    'manufacturers' => [
        'name' => 'Производители',
        'note' => 'Дава достъп до раздел производители в програмата.',
    ],
    'manufacturersview' => [
        'name' => 'Вижда производители',
    ],
    'manufacturerscreate' => [
        'name' => 'Създава нови производители',
    ],
    'manufacturersedit' => [
        'name' => 'Редактира производители',
    ],
    'manufacturersdelete' => [
        'name' => 'Изтрива производители',
    ],
    'companies' => [
        'name' => 'Компании',
        'note' => 'Дава достъп до раздел компании в програмата.',
    ],
    'companiesview' => [
        'name' => 'Вижда компании',
    ],
    'companiescreate' => [
        'name' => 'Създава нови компании',
    ],
    'companiesedit' => [
        'name' => 'Редактира компании',
    ],
    'companiesdelete' => [
        'name' => 'Изтрива компании',
    ],
    'user-self-accounts' => [
        'name' => 'Собствен потребителски акаунт',
        'note' => 'Дава достъп на потребителя да редактира информация за техния собствен акаунт.',
    ],
    'selftwo-factor' => [
        'name' => 'Двуфакторно удостоверяване',
        'note' => 'Позволява на потребителите да включват, изключват и управляват двуфакторно удостоверяване на техните акаунти.',
    ],
    'selfapi' => [
        'name' => 'Управление на API ключове',
        'note' => 'Дава достъп на потребителите да създават, виждат и премахват техни лични API ключове. Ключовете ще имат същите права, като потребите от който са създадени.',
    ],
    'selfedit-location' => [
        'name' => 'Редактира местоположение',
        'note' => 'Дава достъп на потребителя да редактира местоположението на техния потребителски акаунт.',
    ],
    'selfcheckout-assets' => [
        'name' => 'Изписване на активи',
        'note' => 'Дава достъп за изписване на активи без намесата на админ.',
    ],
    'selfview-purchase-cost' => [
        'name' => 'Вижда цена на закупуване',
        'note' => 'Дава достъп на потребителя да вижда цената на която е закупен артикула.',
    ],

    'depreciations' => [
        'name' => 'Управление Амортизации',
        'note' => 'Дава достъп на потребителя да вижда информация за амортизации на активите.',
    ],
    'depreciationsview' => [
        'name' => 'Вижда Амортизации',
    ],
    'depreciationsedit' => [
        'name' => 'Редактира настройки на Амортизации',
    ],
    'depreciationsdelete' => [
        'name' => 'Изтрива записи на Амортизации',
    ],
    'depreciationscreate' => [
        'name' => 'Създава записи на Амортизации',
    ],

    'grant_all' => 'Всички права за :area',
    'deny_all' => 'Без права за :area',
    'inherit_all' => 'Наследяване на всички права за :area от група',
    'grant' => 'Права за :area',
    'deny' => 'Без права за :area',
    'inherit' => 'Наследяване на права за :area от група',
    'use_groups' => 'Силно се препоръчва да се използват групи за достъп вместо даване на индивидуални права за по лесно управление.',

];
