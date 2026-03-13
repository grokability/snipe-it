<?php

return array(

    'invalid_category_type' => '该类别必须是一个耗材类别。',
    'does_not_exist' => '耗材不存在。',

    'create' => array(
        'error'   => '耗材未被创建，请重试。',
        'success' => '耗材创建成功。'
    ),

    'update' => array(
        'error'   => '耗材未被更新，请重试。',
        'success' => '耗材更新成功。'
    ),

    'delete' => array(
        'confirm'   => '您确定希望删除此耗材？',
        'error'   => '删除耗材失败，请重试',
        'success' => '成功删除此耗材。'
    ),

     'checkout' => array(
        'error'   		=> '耗材领取失败，请重试',
        'success' 		=> '耗材领取成功',
        'user_does_not_exist' => '无效用户，请重试。',
         'unavailable'      => '没有足够的耗材可被签出。请检查剩余数量。 ',
    ),

    'checkin' => array(
        'error'   		=> '耗材归还失败，请重试',
        'success' 		=> '耗材归还成功。',
        'user_does_not_exist' => '无效用户，请重试。'
    )


);
