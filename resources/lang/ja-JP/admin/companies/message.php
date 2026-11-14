<?php

return [
    'does_not_exist' => '所属は存在しません',
    'deleted' => '削除された企業',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => '所属は作成されませんでした、もう一度やり直してください。',
        'success' => '所属が正常に作成されました。',
    ],
    'update' => [
        'error' => '所属部署が更新されませんでした、もう一度やり直してください。',
        'success' => '所属が正常に更新されました。',
    ],
    'delete' => [
        'confirm' => 'この所属を削除してもよろしいですか？',
        'error' => '所属を削除する時に問題が発生しました。もう一度試して下さい。',
        'success' => '所属の削除に成功しました。',
        'bulk_success' => '会社を削除しました。|会社を:count件削除しました。',
        'partial_success' => '会社を削除しました。詳しくは以下をご確認ください。|会社を:count件削除しました。詳しくは以下をご確認ください。',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
