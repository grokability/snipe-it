<?php

return array(

    'does_not_exist' => '組件不存在',

    'create' => array(
        'error'   => '新增組件失敗，請重試。',
        'success' => '新增組件成功。'
    ),

    'update' => array(
        'error'   => '更新組件失敗，請重試。',
        'success' => '更新組件成功。'
    ),

    'delete' => array(
        'confirm'   => '您確定要刪除此組件嗎？',
        'error'   => '刪除組件時發生問題。請再試一次。',
        'success' => '刪除組件成功。',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> '組件借出失敗。請再試一次。',
        'success' 		=> '借出組件成功。',
        'user_does_not_exist' => '使用者不正確。請再試一次。',
        'unavailable'      => '組件剩餘不足：剩餘 :remaining ，申請 :requested',
    ),

    'checkin' => array(
        'error'   		=> '組件繳回失敗。請再試一次。',
        'success' 		=> '繳回組件成功。',
        'user_does_not_exist' => '使用者不正確。請再試一次。'
    )


);
