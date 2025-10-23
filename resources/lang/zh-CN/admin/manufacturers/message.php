<?php

return array(

    'support_url_help' => '变量<code>{LOCALE}</code>、<code>{SERIAL}</code>、<code>{MODEL_NUMBER}</code>和<code>{MODEL_NAME}</code>可以在您的URL中使用，以便在查看资产时自动填充这些值——例如https://checkcoverage.apple.com/{LOCALE}{SERIAL}。',
    'does_not_exist' => '制造商不存在',
    'assoc_users'	 => '这个制造商下关联的还有其他资产，请确认后再重试。',

    'create' => array(
        'error'   => '制造商创建失败，请重试。',
        'success' => '制造商创建成功。'
    ),

    'update' => array(
        'error'   => '制造商没有被更新，请重试。',
        'success' => '制造商更新成功。'
    ),

    'restore' => array(
        'error'   => '制造商未恢复，请重试',
        'success' => '制造商恢复成功。'
    ),

    'delete' => array(
        'confirm' => '确定要删除这个制造商吗？',
        'error'   => '删除制造商的过程中出现了一点儿问题，请重试。',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
