<?php

return [
    'not_found' => '未找到你查询的资产维护信息！',
    'delete' => [
        'confirm' => ' 你确定要删除这条资产维护信息吗？',
        'error' => '资产维护信息删除失败，请稍后重试',
        'success' => '资产维护信息已删除',
    ],
    'create' => [
        'error' => '资产维护信息创建失败，请重试',
        'success' => '资产维护信息已创建。',
    ],
    'edit' => [
        'error' => '资产维护信息创建失败，请重试',
        'success' => '资产维护信息已成功编辑。',
    ],
    'asset_maintenance_incomplete' => '尚未完成',
    'warranty' => '质保',
    'not_warranty' => '无质保',
    'complete' => [
        'confirm' => '您确定要将此维护标记为完成吗？此操作无法撤销。',
        'success' => '维护已标记为完成。',
        'error' => '将此维护标记为完成时出现问题。请重试。',
    ],
    'bulk_delete' => '未删除任何维护记录（跳过 :skipped 条）。|已删除 :count 条维护记录。（跳过 :skipped 条）|已删除 :count 条维护记录。（跳过 :skipped 条）',
    'bulk_complete' => '未将任何维护记录标记为完成（跳过或已完成 :skipped 条）。|已将 :count 条维护记录标记为完成。（跳过或已完成 :skipped 条）|已将 :count 条维护记录标记为完成。（跳过或已完成 :skipped 条）',
];
