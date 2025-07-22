<?php

return array(

    'deleted' => '已删除的供应商',
    'does_not_exist' => '供应商不存在。',


    'create' => array(
        'error'   => '供应商没有被创建，请重试。',
        'success' => '供应商创建成功。'
    ),

    'update' => array(
        'error'   => '供应商没有被更新，请重试。',
        'success' => '供应商更新成功。'
    ),

    'delete' => array(
        'confirm'   => '你确定要删除这个供应商吗？',
        'error'   => '删除供应商的过程中出现了一点儿问题，请重试。',
        'success' => '供应商成功被删除。',
        'assoc_assets'	 => '此供应商目前关联着 :asset_count 个资产，无法删除。请更新您的资产，取消关联此供应商后再试。 ',
        'assoc_licenses'	 => '此供应商目前关联着 :licenses_count 个许可证，不能删除。请更新您的许可证，取消关联此供应商，然后重试。 ',
        'assoc_maintenances'	 => '此供应商目前与 :asset_count 项资产维护记录关联，无法删除。请更新您的资产维护记录，移除对该供应商的引用，然后重试。 ',
    )

);
