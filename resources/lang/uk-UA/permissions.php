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
        'name' => 'Cуперкористувач',
        'note'       => 'Визначає, чи має користувач повний доступ до всіх розділів адміністрування. Це налаштування ПЕРЕКРИВАЄ всі інші специфічні та обмежувальні дозволи в системі. ',
    ],
    'admin' => [
        'name' => 'Доступ адміністратора',
        'note'       => 'Визначає, чи має користувач доступ до більшості розділів системи, ЗА ВИНЯТКОМ налаштувань системного адміністратора. Ці користувачі зможуть керувати користувачами, локаціями, категоріями тощо, але ПІДПОРЯДКОВУЮТЬСЯ обмеженням повної підтримки декількох компаній, якщо вона увімкнена.',
    ],

    'import' => [
        'name' => 'Імпорт із CSV',
        'note'       => 'Це дозволить користувачам виконувати імпорт, навіть якщо доступ до користувачів, активів тощо заборонено в інших розділах.',
    ],

    'reports' => [
        'name' => 'Доступ до звітів',
        'note'       => 'Визначає, чи має користувач доступ до розділу «Звіти» в додатку.',
    ],

    'assets' =>
        [
            'name' => 'Активи',
            'note' => 'Надає доступ до розділу «Активи» в додатку.',
    ],

    'assetsview' => [
        'name' => 'Переглянути активи',
    ],

    'assetscreate' => [
        'name' => 'Створити нові активи',
    ],

    'assetsedit' => [
        'name' => 'Редагувати активи',
    ],

    'assetsdelete' => [
        'name' => 'Видалити активи',
    ],

    'assetscheckin' => [
        'name' => 'Повернути на склад',
        'note' => 'Повернення на склад активів, які наразі видані користувачам або на локації.',
    ],

    'assetscheckout' => [
        'name' => 'Видати',
        'note' => 'Призначення активів зі складу шляхом їх видачі.',
    ],

    'assetsaudit' => [
        'name' => 'Аудит активів',
        'note' => 'Дозволяє користувачеві позначати актив як такий, що пройшов фізичну інвентаризацію.',
    ],

    'assetsviewrequestable' => [
        'name' => 'Перегляд активів, доступних для запиту',
        'note' => 'Дозволяє користувачеві переглядати активи, позначені як доступні для запиту.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => 'Перегляд зашифрованих користувацьких полів',
        'note' => 'Дозволяє користувачеві переглядати та змінювати зашифровані користувацькі поля активів.',
    ],

    'accessories'   => [
        'name' => 'Аксесуари',
        'note'       => 'Надає доступ до розділу «Аксесуари» в додатку.',
    ],

    'accessoriesview' => [
        'name' => 'Перегляд аксесуарів',
    ],
    'accessoriescreate' => [
        'name' => 'Створення нових аксесуарів',
    ],
    'accessoriesedit' => [
        'name' => 'Редагування аксесуарів',
    ],
    'accessoriesdelete' => [
        'name' => 'Видалення аксесуарів',
    ],
    'accessoriescheckout' => [
        'name' => 'Видавати аксесуари',
        'note' => 'Призначення аксесуарів зі складу шляхом їх видачі.',
    ],
    'accessoriescheckin' => [
        'name' => 'Повернути аксесуари',
        'note' => 'Повернення на склад аксесуарів, які зараз видані.',
    ],
    'accessoriesfiles' => [
        'name' => 'Керування файлами аксесуарів',
        'note' => 'Дозволяє користувачеві завантажувати, викачувати та видаляти файли, пов’язані з аксесуарами.',
    ],
    'consumables'   => [
        'name' => 'Витратні матеріали',
        'note'       => 'Надає доступ до розділу «Витратні матеріали» в додатку.',
    ],
    'consumablesview' => [
        'name' => 'Перегляд витратних матеріалів',
    ],
    'consumablescreate' => [
        'name' => 'Створення нових витратних матеріалів',
    ],
    'consumablesedit' => [
        'name' => 'Редагування витратних матеріалів',
    ],
    'consumablesdelete' => [
        'name' => 'Видалення витратних матеріалів',
    ],
    'consumablescheckout' => [
        'name' => 'Видавати витратні матеріали',
        'note' => 'Призначення витратних матеріалів зі складу шляхом їх видачі.',
    ],
    'consumablesfiles' => [
        'name' => 'Керування файлами витратних матеріалів',
        'note' => 'Дозволяє користувачеві завантажувати, викачувати та видаляти файли, пов’язані з витратними матеріалами.',
    ],
    'licenses'   => [
        'name' => 'Ліцензії',
        'note'       => 'Надає доступ до розділу «Ліцензії» в додатку.',
    ],
    'licensesview' => [
        'name' => 'Перегляд ліцензій',
    ],
    'licensescreate' => [
        'name' => 'Створення нових ліцензій',
    ],
    'licensesedit' => [
        'name' => 'Редагування ліцензій',
    ],
    'licensesdelete' => [
        'name' => 'Видалення ліцензій',
    ],
    'licensescheckout' => [
        'name' => 'Призначення ліцензій',
        'note' => 'Дозволяє користувачеві призначати ліцензії активам або користувачам.',
        ],
    'licensescheckin' => [
        'name' => 'Скасування призначення ліцензій',
        'note' => 'Дозволяє користувачеві скасовувати призначення ліцензій активам або користувачам.',
    ],
    'licensesfiles' => [
        'name' => 'Керування файлами ліцензій',
        'note' => 'Дозволяє користувачеві завантажувати, викачувати та видаляти файли, пов’язані з ліцензіями.',
    ],
    'licenseskeys' => [
        'name' => 'Керування ліцензійними ключами',
        'note' => 'Дозволяє користувачеві переглядати ключі продуктів, пов’язані з ліцензіями.',
    ],
    'components'   => [
        'name' => 'Компоненти',
        'note'       => 'Надає доступ до розділу «Компоненти» в додатку.',
    ],
    'componentsview' => [
        'name' => 'Перегляд компонентів',
    ],
    'componentscreate' => [
        'name' => 'Створення нових компонентів',
    ],
    'componentsedit' => [
        'name' => 'Редагування компонентів',
    ],
    'componentsdelete' => [
        'name' => 'Видалення компонентів',
    ],
    'componentsfiles' => [
        'name' => 'Керування файлами компонентів',
        'note' => 'Дозволяє користувачеві завантажувати, викачувати та видаляти файли, пов’язані з компонентами.',
    ],
    'componentscheckout' => [
        'name' => 'Видача компонентів',
        'note' => 'Призначайте компоненти в інвентарі, видаючи їх.',
    ],
    'componentscheckin' => [
        'name' => 'Повернення компонентів',
        'note' => 'Повертати компоненти, які наразі видані, назад до інвентарю.',
    ],
    'kits'   => [
        'name' => 'Попередньо визначені набори',
        'note'       => 'Надає доступ до розділу «Попередньо визначені набори» в додатку.',
    ],
    'kitsview' => [
        'name' => 'Перегляд попередньо визначених наборів',
    ],
    'kitscreate' => [
        'name' => 'Створення нових попередньо визначених наборів',
    ],
    'kitsedit' => [
        'name' => 'Редагування попередньо визначених наборів',
    ],
    'kitsdelete' => [
        'name' => 'Видалення попередньо визначених наборів',
    ],
    'users'   => [
        'name' => 'Користувачі',
        'note'       => 'Надає доступ до розділу «Користувачі» в додатку.',
    ],
    'usersview' => [
        'name' => 'Переглянути користувачів',
    ],
    'userscreate' => [
        'name' => 'Створення нових користувачів',
    ],
    'usersedit' => [
        'name' => 'Редагування користувачів',
    ],
    'usersdelete' => [
        'name' => 'Видалення користувачів',
    ],
    'models'   => [
        'name' => 'Моделі',
        'note'       => 'Надає доступ до розділу «Моделі» в додатку.',
    ],
    'modelsview' => [
        'name' => 'Переглянути моделі',
    ],

    'modelscreate' => [
        'name' => 'Створення нових моделей',
    ],
    'modelsedit' => [
        'name' => 'Редагування моделей',
    ],
    'modelsdelete' => [
        'name' => 'Видалення моделей',
    ],
    'categories'   => [
        'name' => 'Категорії',
        'note'       => 'Надає доступ до розділу «Категорії» в додатку.',
    ],
    'categoriesview' => [
        'name' => 'Перегляд категорій',
    ],
    'categoriescreate' => [
        'name' => 'Створення нових категорій',
    ],
    'categoriesedit' => [
        'name' => 'Редагування категорій',
    ],
    'categoriesdelete' => [
        'name' => 'Видалення категорій',
    ],
    'departments'   => [
        'name' => 'Відділи',
        'note'       => 'Надає доступ до розділу «Відділи» в додатку.',
    ],
    'departmentsview' => [
        'name' => 'Перегляд відділів',
    ],
    'departmentscreate' => [
        'name' => 'Створення нових відділів',
    ],
    'departmentsedit' => [
        'name' => 'Редагування відділів',
    ],
    'departmentsdelete' => [
        'name' => 'Видалення відділів',
    ],
    'locations'   => [
        'name' => 'Розташування',
        'note'       => 'Надає доступ до розділу «Локації» в додатку.',
    ],
    'locationsview' => [
        'name' => 'Перегляд локацій',
    ],
    'locationscreate' => [
        'name' => 'Створення нових локацій',
    ],
    'locationsedit' => [
        'name' => 'Редагування локацій',
    ],
    'locationsdelete' => [
        'name' => 'Видалення локацій',
    ],
    'status-labels'   => [
        'name' => 'Статуси активів',
        'note'       => 'Надає доступ до розділу «Мітки статусів», що використовуються для активів.',
    ],
    'statuslabelsview' => [
        'name' => 'Перегляд міток статусів',
    ],
    'statuslabelscreate' => [
        'name' => 'Створення нових міток статусів',
    ],
    'statuslabelsedit' => [
        'name' => 'Редагування міток статусів',
    ],
    'statuslabelsdelete' => [
        'name' => 'Видалення міток статусів',
    ],
    'custom-fields'   => [
        'name' => 'Користувацькі поля',
        'note'       => 'Надає доступ до розділу «Користувацькі поля», що використовуються для активів.',
    ],
    'customfieldsview' => [
        'name' => 'Перегляд користувацьких полів',
    ],
    'customfieldscreate' => [
        'name' => 'Створення нових користувацьких полів',
    ],
    'customfieldsedit' => [
        'name' => 'Редагування користувацьких полів',
    ],
    'customfieldsdelete' => [
        'name' => 'Видалення користувацьких полів',
    ],
    'suppliers'   => [
        'name' => 'Постачальники',
        'note'       => 'Надає доступ до розділу «Постачальники» в додатку.',
    ],
    'suppliersview' => [
        'name' => 'Перегляд постачальників',
    ],
    'supplierscreate' => [
        'name' => 'Створення нових постачальників',
    ],
    'suppliersedit' => [
        'name' => 'Редагування постачальників',
    ],
    'suppliersdelete' => [
        'name' => 'Видалення постачальників',
    ],
    'manufacturers'   => [
        'name' => 'Виробники',
        'note'       => 'Надає доступ до розділу «Виробники» в додатку.',
    ],
    'manufacturersview' => [
        'name' => 'Перегляд виробників',
    ],
    'manufacturerscreate' => [
        'name' => 'Створення нових виробників',
    ],
    'manufacturersedit' => [
        'name' => 'Редагування виробників',
    ],
    'manufacturersdelete' => [
        'name' => 'Видалення виробників',
    ],
    'companies'   => [
        'name' => 'Компанії',
        'note'       => 'Надає доступ до розділу «Компанії» в додатку.',
    ],
    'companiesview' => [
        'name' => 'Перегляд компаній',
    ],
    'companiescreate' => [
        'name' => 'Створення нових компаній',
    ],
    'companiesedit' => [
        'name' => 'Редагування компаній',
    ],
    'companiesdelete' => [
        'name' => 'Видалення компаній',
    ],
    'user-self-accounts' => [
        'name' => 'Власні облікові записи',
        'note'       => 'Надає користувачам без прав адміністратора можливість керувати певними аспектами власних облікових записів.',
    ],
    'selftwo-factor' => [
        'name' => 'Керування двофакторною автентифікацією',
        'note'       => 'Дозволяє користувачам вмикати, вимикати та керувати двофакторною автентифікацією для своїх облікових записів.',
    ],
    'selfapi' => [
        'name' => 'Керування токенами API',
        'note'       => 'Дозволяє користувачам створювати, переглядати та відкликати власні токени API. Токени матимуть ті самі дозволи, що й користувач, який їх створив.',
    ],
    'selfedit-location' => [
        'name' => 'Редагування локації',
        'note'       => 'Дозволяє користувачам редагувати локацію, пов’язану з їхнім власним обліковим записом.',
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
