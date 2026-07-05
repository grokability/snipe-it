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
        'name' => '超級使用者',
        'note' => '決定使用者是否擁有管理員所有功能的完整存取權限。此設定將覆蓋系統中所有更具體和限制性的權限。',
    ],
    'admin' => [
        'name' => '管理員存取',
        'note' => '決定使用者是否可以存取系統大部分功能，但不包括系統管理員設定。這些使用者可以管理使用者、位置、類別等，但若啟用完整多公司支援，則受其限制。',
    ],

    'import' => [
        'name' => 'CSV 匯入',
        'note' => '即使其他地方拒絕存取使用者、資產等，也允許使用者進行匯入。',
    ],

    'reports' => [
        'name' => '報告存取',
        'note' => '決定使用者是否可以存取應用程式的報告區段。',
    ],

    'assets' => [
        'name' => '資產',
        'note' => '授予應用程式資產區段的存取權限。',
    ],

    'assetsview' => [
        'name' => '檢視資產',
    ],

    'assetscreate' => [
        'name' => '新增資產',
    ],

    'assetsedit' => [
        'name' => '編輯資產',
    ],

    'assetsdelete' => [
        'name' => '刪除資產',
    ],

    'assetscheckin' => [
        'name' => '繳回',
        'note' => '將目前已借出的資產繳回庫存。',
    ],

    'assetscheckout' => [
        'name' => '借出',
        'note' => '透過借出方式分配庫存中的資產。',
    ],

    'assetsaudit' => [
        'name' => '稽核資產',
        'note' => '允許使用者將資產標記為已實地盤點。',
    ],

    'assetsviewrequestable' => [
        'name' => '檢視可申請資產',
        'note' => '允許使用者查看標記為可申請的資產。',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => '檢視加密自訂欄位',
        'note' => '允許使用者查看和修改資產上的加密自訂欄位。',
    ],

    'accessories' => [
        'name' => '配件',
        'note' => '授予應用程式配件區段的存取權限。',
    ],

    'accessoriesview' => [
        'name' => '檢視配件',
    ],
    'accessoriescreate' => [
        'name' => '新增配件',
    ],
    'accessoriesedit' => [
        'name' => '編輯配件',
    ],
    'accessoriesdelete' => [
        'name' => '刪除配件',
    ],
    'accessoriescheckout' => [
        'name' => '借出配件',
        'note' => '透過借出方式分配庫存中的配件。',
    ],
    'accessoriescheckin' => [
        'name' => '繳回配件',
        'note' => '將目前已借出的配件繳回庫存。',
    ],
    'accessoriesfiles' => [
        'name' => '管理配件檔案',
        'note' => '允許使用者上傳、下載和刪除與配件相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'assetsfiles' => [
        'name' => '管理資產檔案',
        'note' => '允許使用者上傳、下載和刪除與資產相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'usersfiles' => [
        'name' => '管理使用者檔案',
        'note' => '允許使用者上傳、下載和刪除與使用者相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'modelsfiles' => [
        'name' => '管理型號檔案',
        'note' => '允許使用者在型號檢視和資產檢視畫面上傳、下載和刪除與資產型號相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'departmentsfiles' => [
        'name' => '管理部門檔案',
        'note' => '允許使用者上傳、下載和刪除與部門相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'suppliersfiles' => [
        'name' => '管理供應商檔案',
        'note' => '允許使用者上傳、下載和刪除與供應商相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'locationsfiles' => [
        'name' => '管理位置檔案',
        'note' => '允許使用者上傳、下載和刪除與位置相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'companiesfiles' => [
        'name' => '管理公司檔案',
        'note' => '允許使用者上傳、下載和刪除與公司相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'consumablesfiles' => [
        'name' => '管理耗材檔案',
        'note' => '允許使用者上傳、下載和刪除與耗材相關的檔案。（僅在具有檢視權限或更高權限時有意義。）',
    ],

    'consumables' => [
        'name' => '耗材',
        'note' => '授予應用程式耗材區段的存取權限。',
    ],
    'consumablesview' => [
        'name' => '檢視耗材',
    ],
    'consumablescreate' => [
        'name' => '新增耗材',
    ],
    'consumablesedit' => [
        'name' => '編輯耗材',
    ],
    'consumablesdelete' => [
        'name' => '刪除耗材',
    ],
    'consumablescheckout' => [
        'name' => '借出耗材',
        'note' => '透過借出方式分配庫存中的耗材。',
    ],

    'licenses' => [
        'name' => '授權',
        'note' => '授予應用程式授權區段的存取權限。',
    ],
    'licensesview' => [
        'name' => '檢視授權',
    ],
    'licensescreate' => [
        'name' => '新增授權',
    ],
    'licensesedit' => [
        'name' => '編輯授權',
    ],
    'licensesdelete' => [
        'name' => '刪除授權',
    ],
    'licensescheckout' => [
        'name' => '分配授權',
        'note' => '允許使用者將授權分配給資產或使用者。',
    ],
    'licensescheckin' => [
        'name' => '取消分配授權',
        'note' => '允許使用者從資產或使用者取消授權分配。',
    ],
    'licensesfiles' => [
        'name' => '管理授權檔案',
        'note' => '允許使用者上傳、下載和刪除與授權相關的檔案。',
    ],
    'componentsfiles' => [
        'name' => '管理組件檔案',
        'note' => '允許使用者上傳、下載和刪除與組件相關的檔案。',
    ],

    'licenseskeys' => [
        'name' => '管理授權金鑰',
        'note' => '允許使用者查看與授權相關的產品金鑰。',
    ],
    'components' => [
        'name' => '組件',
        'note' => '授予應用程式組件區段的存取權限。',
    ],
    'componentsview' => [
        'name' => '檢視組件',
    ],
    'componentscreate' => [
        'name' => '新增組件',
    ],
    'componentsedit' => [
        'name' => '編輯組件',
    ],
    'componentsdelete' => [
        'name' => '刪除組件',
    ],

    'componentscheckout' => [
        'name' => '借出組件',
        'note' => '透過借出方式分配庫存中的組件。',
    ],
    'componentscheckin' => [
        'name' => '繳回組件',
        'note' => '將目前已借出的組件繳回庫存。',
    ],
    'kits' => [
        'name' => '預設組',
        'note' => '授予應用程式預定義套件區段的存取權限。',
    ],
    'kitsview' => [
        'name' => '檢視預定義套件',
    ],
    'kitscreate' => [
        'name' => '新增預定義套件',
    ],
    'kitsedit' => [
        'name' => '編輯預定義套件',
    ],
    'kitsdelete' => [
        'name' => '刪除預定義套件',
    ],
    'users' => [
        'name' => '使用者',
        'note' => '授予應用程式使用者區段的存取權限。',
    ],
    'usersview' => [
        'name' => '檢視使用者',
    ],
    'userscreate' => [
        'name' => '新增使用者',
    ],
    'usersedit' => [
        'name' => '編輯使用者',
    ],
    'usersdelete' => [
        'name' => '刪除使用者',
    ],
    'models' => [
        'name' => '型號',
        'note' => '授予應用程式型號區段的存取權限。',
    ],
    'modelsview' => [
        'name' => '檢視型號',
    ],

    'modelscreate' => [
        'name' => '新增型號',
    ],
    'modelsedit' => [
        'name' => '編輯型號',
    ],
    'modelsdelete' => [
        'name' => '刪除型號',
    ],
    'categories' => [
        'name' => '類別',
        'note' => '授予應用程式類別區段的存取權限。',
    ],
    'categoriesview' => [
        'name' => '檢視類別',
    ],
    'categoriescreate' => [
        'name' => '新增類別',
    ],
    'categoriesedit' => [
        'name' => '編輯類別',
    ],
    'categoriesdelete' => [
        'name' => '刪除類別',
    ],
    'departments' => [
        'name' => '部門',
        'note' => '授予應用程式部門區段的存取權限。',
    ],
    'departmentsview' => [
        'name' => '檢視部門',
    ],
    'departmentscreate' => [
        'name' => '新增部門',
    ],
    'departmentsedit' => [
        'name' => '編輯部門',
    ],
    'departmentsdelete' => [
        'name' => '刪除部門',
    ],
    'locations' => [
        'name' => '位置',
        'note' => '授予應用程式位置區段的存取權限。',
    ],
    'locationsview' => [
        'name' => '檢視位置',
    ],
    'locationscreate' => [
        'name' => '新增位置',
    ],
    'locationsedit' => [
        'name' => '編輯位置',
    ],
    'locationsdelete' => [
        'name' => '刪除位置',
    ],
    'status-labels' => [
        'name' => '狀態標籤',
        'note' => '授予應用程式資產使用的狀態標籤區段的存取權限。',
    ],
    'statuslabelsview' => [
        'name' => '檢視狀態標籤',
    ],
    'statuslabelscreate' => [
        'name' => '新增狀態標籤',
    ],
    'statuslabelsedit' => [
        'name' => '編輯狀態標籤',
    ],
    'statuslabelsdelete' => [
        'name' => '刪除狀態標籤',
    ],
    'custom-fields' => [
        'name' => '自訂欄位',
        'note' => '授予應用程式資產使用的自訂欄位區段的存取權限。',
    ],
    'customfieldsview' => [
        'name' => '檢視自訂欄位',
    ],
    'customfieldscreate' => [
        'name' => '新增自訂欄位',
    ],
    'customfieldsedit' => [
        'name' => '編輯自訂欄位',
    ],
    'customfieldsdelete' => [
        'name' => '刪除自訂欄位',
    ],
    'suppliers' => [
        'name' => '供應商',
        'note' => '授予應用程式供應商區段的存取權限。',
    ],
    'suppliersview' => [
        'name' => '檢視供應商',
    ],
    'supplierscreate' => [
        'name' => '新增供應商',
    ],
    'suppliersedit' => [
        'name' => '編輯供應商',
    ],
    'suppliersdelete' => [
        'name' => '刪除供應商',
    ],
    'manufacturers' => [
        'name' => '製造商',
        'note' => '授予應用程式製造商區段的存取權限。',
    ],
    'manufacturersview' => [
        'name' => '檢視製造商',
    ],
    'manufacturerscreate' => [
        'name' => '新增製造商',
    ],
    'manufacturersedit' => [
        'name' => '編輯製造商',
    ],
    'manufacturersdelete' => [
        'name' => '刪除製造商',
    ],
    'companies' => [
        'name' => '公司',
        'note' => '授予應用程式公司區段的存取權限。',
    ],
    'companiesview' => [
        'name' => '檢視公司',
    ],
    'companiescreate' => [
        'name' => '新增公司',
    ],
    'companiesedit' => [
        'name' => '編輯公司',
    ],
    'companiesdelete' => [
        'name' => '刪除公司',
    ],
    'user-self-accounts' => [
        'name' => '使用者自我帳戶',
        'note' => '授予非管理員使用者管理自己帳戶某些方面的能力。',
    ],
    'selftwo-factor' => [
        'name' => '管理雙因素驗證',
        'note' => '允許使用者啟用、停用和管理自己帳戶的雙因素驗證。',
    ],
    'selfapi' => [
        'name' => '管理 API Tokens',
        'note' => '允許使用者建立、查看和撤銷自己的 API Tokens。使用者 Tokens 將擁有與建立者相同的權限。',
    ],
    'selfedit-location' => [
        'name' => '編輯位置',
        'note' => '允許使用者編輯與自己帳戶相關的位置。',
    ],
    'selfcheckout-assets' => [
        'name' => '自行借出資產',
        'note' => '允許使用者在不需要管理員介入的情況下，自行借出資產給自己。',
    ],
    'selfview-purchase-cost' => [
        'name' => '檢視採購成本',
        'note' => '允許使用者在其帳戶檢視中查看項目的採購成本。',
    ],

    'depreciations' => [
        'name' => '折舊管理',
        'note' => '允許使用者管理和查看資產折舊詳情。',
    ],
    'depreciationsview' => [
        'name' => '檢視折舊詳情',
    ],
    'depreciationsedit' => [
        'name' => '編輯折舊設定',
    ],
    'depreciationsdelete' => [
        'name' => '刪除折舊記錄',
    ],
    'depreciationscreate' => [
        'name' => '建立折舊記錄',
    ],

    'grant_all' => '授予 :area 的所有權限',
    'deny_all' => '拒絕 :area 的所有權限',
    'inherit_all' => '從權限群組繼承 :area 的所有權限',
    'grant' => '授予 :area 的權限',
    'deny' => '拒絕 :area 的權限',
    'inherit' => '從權限群組繼承 :area 的權限',
    'use_groups' => '我們強烈建議使用權限群組而非分配個別權限，以便更容易管理。',

];
