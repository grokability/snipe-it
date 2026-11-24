<?php

return [

    'deleted' => '已删除的供应商',
    'does_not_exist' => '供应商不存在。',
    'assoc_items' => '该供应商当前至少与一个资产、配件、消耗品、组件、许可证或维护记录相关联，无法删除。请更新这些项目，使其不再引用该供应商，然后重试。 ',

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
        'bulk_success' => '供应商删除成功。|:count 个供应商已成功删除。',
        'partial_success' => '.',
    ],

];
