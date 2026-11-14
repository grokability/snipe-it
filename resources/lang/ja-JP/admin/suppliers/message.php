<?php

return [

    'deleted' => '削除されたサプライヤー',
    'does_not_exist' => '仕入先が存在していません。',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => '仕入先が作成できませんでした。もう一度試して下さい。',
        'success' => '仕入先が作成されました。',
    ],

    'update' => [
        'error' => '仕入先は更新できませんでした。もう一度試して下さい。',
        'success' => '仕入先が更新されました。',
    ],

    'delete' => [
        'confirm' => '本当にこの仕入先を削除してよいですか？',
        'error' => '仕入先を削除する際に問題が発生しました。もう一度試して下さい。',
        'success' => '仕入先が削除されました。',
        'not_found' => '仕入先が見つかりません。',
        'bulk_success' => '仕入先を削除しました。|仕入先を:count件削除しました。',
        'partial_success' => '一部の仕入先を削除しました。詳しくは以下をご確認ください。',
    ],

];
