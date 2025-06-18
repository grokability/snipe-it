<?php

return array(

    'does_not_exist' => '配件[:id] 不存在。',
    'not_found' => '找不到该配件。',
    'assoc_users'	 => '配件目前状态：可用数量不足，请检查改配件再重试。',

    'create' => array(
        'error'   => '配件添加失败，请重试。',
        'success' => '配件添加成功。'
    ),

    'update' => array(
        'error'   => '配件更新失败，请重试。',
        'success' => '配件更新成功。'
    ),

    'delete' => array(
        'confirm'   => '你确定要删除此配件？',
        'error'   => '删除配件出错，请重试。',
        'success' => '删除配件成功'
    ),

     'checkout' => array(
        'error'   		=> '配件不能被预订，请重试。',
        'success' 		=> '配件成功签出。',
        'unavailable'   => '配件不可被签出。检查可用数量',
        'user_does_not_exist' => '无效用户，请重试。',
         'checkout_qty' => array(
            'lte'  => '目前只有一个可用的此类型的配件，您正在试图签出 :checkout_qty 个。 请调整签出数量或该配件的总库存，然后重试。|有 :num_currently_restotal 个可用配件，您正在尝试签出 :checkout_qty 个。 请调整借出数量或该配件的总库存，然后重试。',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> '配件没有归还，请重试。',
        'success' 		=> '配件归还成功。',
        'user_does_not_exist' => '无效用户，请重试。'
    )


);
