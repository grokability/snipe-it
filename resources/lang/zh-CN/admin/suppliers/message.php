<?php

return [

    'deleted' => '已删除的供应商',
    'does_not_exist' => '供应商不存在。',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => '供应商没有被创建，请重试。',
        'success' => '供应商创建成功。',
    ],

    'update' => [
        'error' => '供应商没有被更新，请重试。',
        'success' => '供应商更新成功。',
    ],

    'delete' => [
        'confirm' => '你确定要删除这个供应商吗？',
        'error' => '删除供应商的过程中出现了一点儿问题，请重试。',
        'success' => '供应商成功被删除。',
        'not_found' => '找不到供应商。',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
