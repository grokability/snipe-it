<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => '소모품이 존재하지 않습니다.',

    'create' => [
        'error' => '소모품이 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '소모품이 생성되었습니다.',
    ],

    'update' => [
        'error' => '소모품이 수정되지 않았습니다. 다시 시도해 주세요.',
        'success' => '소모품이 수정되었습니다.',
    ],

    'delete' => [
        'confirm' => '이 소모품을 삭제하시겠습니까?',
        'error' => '소모품 삭제시 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '소모품이 삭제 되었습니다.',
    ],

    'checkout' => [
        'error' => '소모품이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' => '소모품이 반출 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'checkin' => [
        'error' => '소모품이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' => '소모품이 반입 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
    ],

];
