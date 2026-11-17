<?php

return [

    'deleted' => '已删除资产型号',
    'does_not_exist' => '型号不存在。',
    'no_association' => '警告！此物品的资产型号无效或丢失！',
    'no_association_fix' => '这将以怪异且可怕的方式破坏一切。立即编辑此资产并为其指定一个型号。',
    'assoc_users' => '本型号下目前还有相关的资产，不能被删除，请删除资产以后，再重试。 ',
    'invalid_category_type' => '此类别必须是一个资产类别。',

    'create' => [
        'error' => '型号没有被创建，请重试。',
        'success' => '型号创建成功。',
        'duplicate_set' => '资产名称、制造商和编号都相同的资产型号已存在。',
    ],

    'update' => [
        'error' => '模板没有被更新，请重试。',
        'success' => '模板更新成功。',
    ],

    'delete' => [
        'confirm' => '你确定删除这个模板吗？',
        'error' => '删除模板的过程中出现了一点儿问题，请重试。',
        'success' => '模板已经成功被删除',
    ],

    'restore' => [
        'error' => '型号未被恢复，请重试。',
        'success' => '型号恢复成功。',
    ],

    'bulkedit' => [
        'error' => '没有字段被更改，因此没有更新任何内容。',
        'success' => '型号已成功更新|:model_count 个型号已成功更新。',
        'warn' => '您将要更新以下型号的属性：|您将要编辑 :model_count 个型号的属性：',

    ],

    'bulkdelete' => [
        'error' => '没有型号被选中，所以没有删除任何东西。',
        'nothing_deletable' => '所选型号中没有一个可以删除，因为它们仍然拥有与它们相关联的资产。',
        'success' => '型号已删除！|:succes_count 个型号已删除！',
        'success_partial' => ':success_count 个已删除, 但是 :fail_count 个因为还有关联资产所以没办法删除。',
    ],

    'merge' => [
        'min_two' => '选择至少两个型号进行合并。',
        'no_target' => '在合并之前选择要保留的型号。',
        'not_found' => '一个或多个选定的型号无法加载。刷新型号列表然后重试。',
        'information' => '您即将合并 :count 个型号。请选择您要保留的型号。关联到其他型号的所有资产都将重新分配给您选择的型号，源型号将被删除。',
        'warning' => '此操作无法撤销。重新分配的资产将继承未存型号的类别、字段和折旧设置。',
        'pick_target' => '您想要保留哪个型号？',
        'success' => '已将 :source_count 个型号合并至 ":target"。:asset_count 个资产已重新分配。',
    ],

];
