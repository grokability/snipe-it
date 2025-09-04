<?php

return array(

    'does_not_exist' => '부품이 존재하지 않습니다.',

    'create' => array(
        'error'   => '부품이 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부품이 생성되었습니다.'
    ),

    'update' => array(
        'error'   => '부품이 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부품이 갱신 되었습니다.'
    ),

    'delete' => array(
        'confirm'   => '이 부품을 삭제하시겠습니까?',
        'error'   => '부품 삭제시 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '부품이 삭제되었습니다.',
        'error_qty'   => 'Some components of this type are still checked out. Please check them in and try again.',
    ),

     'checkout' => array(
        'error'   		=> '부품이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' 		=> '부품이 반출 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'unavailable'      => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ),

    'checkin' => array(
        'error'   		=> '부품이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' 		=> '부품이 반입되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.'
    )


);
