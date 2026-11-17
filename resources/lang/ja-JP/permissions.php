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
        'name' => 'スーパーユーザー',
        'note' => 'システム全体への完全な管理権限を付与します。この設定は、個別に設定されたすべての制限付き権限より優先されます。',
    ],
    'admin' => [
        'name' => '管理者アクセス',
        'note' => 'システム管理設定を除く、ほとんどの機能へのアクセスを許可します。ユーザー、場所、カテゴリなどを管理できます。完全な複数会社対応が有効な場合は、その会社範囲の制限が適用されます。',
    ],

    'import' => [
        'name' => 'CSVインポート',
        'note' => 'ユーザーや資産などへのアクセスが個別に拒否されていても、CSVからのインポートを許可します。',
    ],

    'reports' => [
        'name' => 'レポートへのアクセス',
        'note' => 'レポート画面へのアクセスを許可します。',
    ],

    'assets' => [
        'name' => '資産',
        'note' => '資産画面へのアクセスを許可します。',
    ],

    'assetsview' => [
        'name' => '資産を表示',
        'note' => 'この権限を持つユーザーは、資産モデルにアップロードされたファイルも閲覧できます（変更や削除はできません）。取扱説明書などを資産ごとにアップロードせず共有するための仕様です。',
    ],

    'assetscreate' => [
        'name' => '資産を作成',
    ],

    'assetsedit' => [
        'name' => '資産を編集',
    ],

    'assetsdelete' => [
        'name' => '資産を削除',
    ],

    'assetscheckin' => [
        'name' => 'チェックイン',
        'note' => 'チェックアウト中の資産を在庫へ戻す操作を許可します。',
    ],

    'assetscheckout' => [
        'name' => 'チェックアウト',
        'note' => '在庫内の資産をチェックアウトして割り当てる操作を許可します。',
    ],

    'assetsaudit' => [
        'name' => '資産を監査',
        'note' => '資産を現物確認済みとして記録する操作を許可します。',
    ],

    'assetsviewrequestable' => [
        'name' => '申請可能な資産を表示',
        'note' => '申請可能に設定された資産の表示を許可します。',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => '暗号化されたカスタムフィールドを表示',
        'note' => '資産の暗号化されたカスタムフィールドの表示と変更を許可します。',
    ],

    'accessories' => [
        'name' => '付属品',
        'note' => '付属品画面へのアクセスを許可します。',
    ],

    'accessoriesview' => [
        'name' => '付属品を表示',
    ],
    'accessoriescreate' => [
        'name' => '付属品を作成',
    ],
    'accessoriesedit' => [
        'name' => '付属品を編集',
    ],
    'accessoriesdelete' => [
        'name' => '付属品を削除',
    ],
    'accessoriescheckout' => [
        'name' => '付属品をチェックアウト',
        'note' => '在庫内の付属品をチェックアウトして割り当てる操作を許可します。',
    ],
    'accessoriescheckin' => [
        'name' => '付属品をチェックイン',
        'note' => 'チェックアウト中の付属品を在庫へ戻す操作を許可します。',
    ],
    'accessoriesfiles' => [
        'name' => '付属品のファイルを管理',
        'note' => '付属品に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'assetsfiles' => [
        'name' => '資産のファイルを管理',
        'note' => '資産に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'usersfiles' => [
        'name' => 'ユーザーのファイルを管理',
        'note' => 'ユーザーに関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'modelsfiles' => [
        'name' => '資産モデルのファイルを管理',
        'note' => '資産モデル画面と資産画面で、資産モデルに関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'departmentsfiles' => [
        'name' => '部署のファイルを管理',
        'note' => '部署に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'suppliersfiles' => [
        'name' => '仕入先のファイルを管理',
        'note' => '仕入先に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'locationsfiles' => [
        'name' => '場所のファイルを管理',
        'note' => '場所に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'companiesfiles' => [
        'name' => '会社のファイルを管理',
        'note' => '会社に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'consumablesfiles' => [
        'name' => '消耗品のファイルを管理',
        'note' => '消耗品に関連付けられたファイルのアップロード、ダウンロード、削除を許可します（表示以上の権限も必要です）。',
    ],

    'consumables' => [
        'name' => '消耗品',
        'note' => '消耗品画面へのアクセスを許可します。',
    ],
    'consumablesview' => [
        'name' => '消耗品を表示',
    ],
    'consumablescreate' => [
        'name' => '消耗品を作成',
    ],
    'consumablesedit' => [
        'name' => '消耗品を編集',
    ],
    'consumablesdelete' => [
        'name' => '消耗品を削除',
    ],
    'consumablescheckout' => [
        'name' => '消耗品をチェックアウト',
        'note' => '在庫内の消耗品をチェックアウトして割り当てる操作を許可します。',
    ],

    'licenses' => [
        'name' => 'ライセンス',
        'note' => 'ライセンス画面へのアクセスを許可します。',
    ],
    'licensesview' => [
        'name' => 'ライセンスを表示',
    ],
    'licensescreate' => [
        'name' => 'ライセンスを作成',
    ],
    'licensesedit' => [
        'name' => 'ライセンスを編集',
    ],
    'licensesdelete' => [
        'name' => 'ライセンスを削除',
    ],
    'licensescheckout' => [
        'name' => 'ライセンスを割り当て',
        'note' => '資産またはユーザーへのライセンス割り当てを許可します。',
    ],
    'licensescheckin' => [
        'name' => 'ライセンスの割り当てを解除',
        'note' => '資産またはユーザーからのライセンス割り当て解除を許可します。',
    ],
    'licensesfiles' => [
        'name' => 'ライセンスのファイルを管理',
        'note' => 'ライセンスに関連付けられたファイルのアップロード、ダウンロード、削除を許可します。',
    ],
    'componentsfiles' => [
        'name' => 'コンポーネントのファイルを管理',
        'note' => 'コンポーネントに関連付けられたファイルのアップロード、ダウンロード、削除を許可します。',
    ],

    'licenseskeys' => [
        'name' => 'ライセンスキーを管理',
        'note' => 'ライセンスに関連付けられたプロダクトキーの表示を許可します。',
    ],
    'components' => [
        'name' => 'コンポーネント',
        'note' => 'コンポーネント画面へのアクセスを許可します。',
    ],
    'componentsview' => [
        'name' => 'コンポーネントを表示',
    ],
    'componentscreate' => [
        'name' => 'コンポーネントを作成',
    ],
    'componentsedit' => [
        'name' => 'コンポーネントを編集',
    ],
    'componentsdelete' => [
        'name' => 'コンポーネントを削除',
    ],

    'componentscheckout' => [
        'name' => 'コンポーネントをチェックアウト',
        'note' => '在庫内のコンポーネントをチェックアウトして割り当てる操作を許可します。',
    ],
    'componentscheckin' => [
        'name' => 'コンポーネントをチェックイン',
        'note' => 'チェックアウト中のコンポーネントを在庫へ戻す操作を許可します。',
    ],
    'kits' => [
        'name' => '製品セット',
        'note' => '定義済みキット画面へのアクセスを許可します。',
    ],
    'kitsview' => [
        'name' => '定義済みキットを表示',
    ],
    'kitscreate' => [
        'name' => '定義済みキットを作成',
    ],
    'kitsedit' => [
        'name' => '定義済みキットを編集',
    ],
    'kitsdelete' => [
        'name' => '定義済みキットを削除',
    ],
    'users' => [
        'name' => 'ユーザー',
        'note' => 'ユーザー画面へのアクセスを許可します。',
    ],
    'usersview' => [
        'name' => 'ユーザーを表示',
        'note' => 'Users with this permission will also be able to view edit and checkin history. Access to files uploaded to a user is gated separately by the Manage User Files permission.',
    ],
    'userscreate' => [
        'name' => 'ユーザーを作成',
    ],
    'usersedit' => [
        'name' => 'ユーザーを編集',
    ],
    'usersdelete' => [
        'name' => 'ユーザーを削除',
    ],
    'models' => [
        'name' => '資産モデル',
        'note' => '資産モデル画面へのアクセスを許可します。',
    ],
    'modelsview' => [
        'name' => 'モデルを表示',
    ],

    'modelscreate' => [
        'name' => '資産モデルを作成',
    ],
    'modelsedit' => [
        'name' => '資産モデルを編集',
    ],
    'modelsdelete' => [
        'name' => '資産モデルを削除',
    ],
    'categories' => [
        'name' => 'カテゴリ',
        'note' => 'カテゴリ画面へのアクセスを許可します。',
    ],
    'categoriesview' => [
        'name' => 'カテゴリを表示',
    ],
    'categoriescreate' => [
        'name' => 'カテゴリを作成',
    ],
    'categoriesedit' => [
        'name' => 'カテゴリを編集',
    ],
    'categoriesdelete' => [
        'name' => 'カテゴリを削除',
    ],
    'departments' => [
        'name' => '部署',
        'note' => '部署画面へのアクセスを許可します。',
    ],
    'departmentsview' => [
        'name' => '部署を表示',
    ],
    'departmentscreate' => [
        'name' => '部署を作成',
    ],
    'departmentsedit' => [
        'name' => '部署を編集',
    ],
    'departmentsdelete' => [
        'name' => '部署を削除',
    ],
    'locations' => [
        'name' => '場所',
        'note' => '場所画面へのアクセスを許可します。',
    ],
    'locationsview' => [
        'name' => '場所を表示',
    ],
    'locationscreate' => [
        'name' => '場所を作成',
    ],
    'locationsedit' => [
        'name' => '場所を編集',
    ],
    'locationsdelete' => [
        'name' => '場所を削除',
    ],
    'status-labels' => [
        'name' => 'ステータスラベル',
        'note' => '資産で使用するステータスラベル画面へのアクセスを許可します。',
    ],
    'statuslabelsview' => [
        'name' => 'ステータスラベルを表示',
    ],
    'statuslabelscreate' => [
        'name' => 'ステータスラベルを作成',
    ],
    'statuslabelsedit' => [
        'name' => 'ステータスラベルを編集',
    ],
    'statuslabelsdelete' => [
        'name' => 'ステータスラベルを削除',
    ],
    'custom-fields' => [
        'name' => 'カスタムフィールド',
        'note' => '資産で使用するカスタムフィールド画面へのアクセスを許可します。',
    ],
    'customfieldsview' => [
        'name' => 'カスタムフィールドを表示',
    ],
    'customfieldscreate' => [
        'name' => 'カスタムフィールドを作成',
    ],
    'customfieldsedit' => [
        'name' => 'カスタムフィールドを編集',
    ],
    'customfieldsdelete' => [
        'name' => 'カスタムフィールドを削除',
    ],
    'suppliers' => [
        'name' => '仕入先',
        'note' => '仕入先画面へのアクセスを許可します。',
    ],
    'suppliersview' => [
        'name' => '仕入先を表示',
    ],
    'supplierscreate' => [
        'name' => '仕入先を作成',
    ],
    'suppliersedit' => [
        'name' => '仕入先を編集',
    ],
    'suppliersdelete' => [
        'name' => '仕入先を削除',
    ],
    'manufacturers' => [
        'name' => '製造元',
        'note' => '製造元画面へのアクセスを許可します。',
    ],
    'manufacturersview' => [
        'name' => '製造元を表示',
    ],
    'manufacturerscreate' => [
        'name' => '製造元を作成',
    ],
    'manufacturersedit' => [
        'name' => '製造元を編集',
    ],
    'manufacturersdelete' => [
        'name' => '製造元を削除',
    ],
    'companies' => [
        'name' => '会社',
        'note' => '会社画面へのアクセスを許可します。',
    ],
    'companiesview' => [
        'name' => '会社を表示',
    ],
    'companiescreate' => [
        'name' => '会社を作成',
    ],
    'companiesedit' => [
        'name' => '会社を編集',
    ],
    'companiesdelete' => [
        'name' => '会社を削除',
    ],
    'user-self-accounts' => [
        'name' => 'ユーザー自身のアカウント',
        'note' => '管理者でないユーザーに、自分のアカウントの一部を管理する権限を付与します。',
    ],
    'selftwo-factor' => [
        'name' => '二要素認証を管理',
        'note' => 'ユーザー自身のアカウントで、二要素認証の有効化、無効化、管理を許可します。',
    ],
    'selfapi' => [
        'name' => 'APIトークンを管理',
        'note' => 'ユーザー自身のAPIトークンの作成、表示、失効を許可します。トークンには、作成したユーザーと同じ権限が適用されます。',
    ],
    'selfedit-location' => [
        'name' => '自分の場所を編集',
        'note' => 'ユーザー自身のアカウントに関連付けられた場所の編集を許可します。',
    ],
    'selfcheckout-assets' => [
        'name' => '資産を自分にチェックアウト',
        'note' => '管理者の操作なしで、ユーザーが資産を自分自身へチェックアウトすることを許可します。',
    ],
    'selfview-purchase-cost' => [
        'name' => '購入費用を表示',
        'note' => 'アカウント画面で、割り当てられたアイテムの購入費用を表示することを許可します。',
    ],

    'depreciations' => [
        'name' => '減価償却の管理',
        'note' => '資産の減価償却情報の管理と表示を許可します。',
    ],
    'depreciationsview' => [
        'name' => '減価償却の詳細を表示',
    ],
    'depreciationsedit' => [
        'name' => '減価償却設定を編集',
    ],
    'depreciationsdelete' => [
        'name' => '減価償却レコードを削除',
    ],
    'depreciationscreate' => [
        'name' => '減価償却レコードを作成',
    ],

    'grant_all' => ':areaの全権限を許可',
    'deny_all' => ':areaの全権限を拒否',
    'inherit_all' => ':areaの全権限を権限グループから継承',
    'grant' => ':areaの権限を許可',
    'deny' => ':areaの権限を拒否',
    'inherit' => ':areaの権限を権限グループから継承',
    'use_groups' => '管理しやすくするため、個別に権限を割り当てる代わりに権限グループの使用を強く推奨します。',

];
