<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => '製造元が存在しません。',
    'assoc_users'	 => 'この製造元は一つ以上の型番に関連付けられているため、削除できません。この製造元の関連付けを削除後、もう一度試して下さい。 ',

    'create' => array(
        'error'   => '製造元が作成できませんでした。もう一度試して下さい。',
        'success' => '製造元が作成されました。'
    ),

    'update' => array(
        'error'   => '製造元が更新できませんでした。もう一度試して下さい。',
        'success' => '製造元が更新できませんでした。もう一度試して下さい。'
    ),

    'restore' => array(
        'error'   => '製造元が復元できませんでした。もう一度試して下さい。',
        'success' => '製造元が復元されました。'
    ),

    'delete' => array(
        'confirm' => '本当にこの製造元を削除しますか？',
        'error'   => '製造元を削除する際に問題が発生しました。もう一度試して下さい。',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
