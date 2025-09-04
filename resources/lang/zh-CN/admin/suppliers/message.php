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
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
