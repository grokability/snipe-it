<?php

return array(

    'does_not_exist' => '组件不存在。',

    'create' => array(
        'error'   => '新增组件失败，请重试。',
        'success' => '新增组件成功。'
    ),

    'update' => array(
        'error'   => '更新组件失败，请重试。',
        'success' => '更新成功。'
    ),

    'delete' => array(
        'confirm'   => '你确定要删除这个组件吗？',
        'error'   => '删除组件出错，请重试。',
        'success' => '删除组件成功。',
        'error_qty'   => '此类型的某些组件仍然被签出。请归还它们，然后重试。',
    ),

     'checkout' => array(
        'error'   		=> '签出组件失败，请重试。',
        'success' 		=> '签出组件成功。',
        'user_does_not_exist' => '无效用户，请重试。',
        'unavailable'      => '还没有足够的组件: :remaining 件剩余, :requested 件需求 ',
    ),

    'checkin' => array(
        'error'   		=> '归还组件失败，请重试。',
        'success' 		=> '归还组件成功。',
        'user_does_not_exist' => '无效用户，请重试。'
    )


);
