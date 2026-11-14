<?php

return [

    'does_not_exist' => '해당 악세사리 [:id] 가 존재하지 않습니다.',
    'not_found' => '액세서리를 찾을 수 없습니다.',
    'assoc_users' => '이 부속품은 현재 사용자에게 :count 개가 반출 되었습니다. 이 부속품을 반입하고 다시 시도해 주세요. ',

    'create' => [
        'error' => '부속품을 만들 수 없었습니다. 재시도해 주세요.',
        'success' => '부속품을 만들었습니다.',
    ],

    'update' => [
        'error' => '부속품을 갱신하지 못했습니다. 재시도해 주세요.',
        'success' => '부속품을 갱신했습니다.',
    ],

    'delete' => [
        'confirm' => '이 부속품을 삭제하시겠습니까?',
        'error' => '분류 삭제 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '해당 부속품이 삭제 완료 되었습니다.',
        'bulk_success' => '액세서리가 성공적으로 삭제되었습니다.|:count개의 액세서리가 성공적으로 삭제되었습니다.',
        'partial_success' => ':count개의 액세서리가 성공적으로 삭제되었으나 일부는 삭제할 수 없습니다. 자세한 내용은 아래를 참조하세요.|:count개의 액세서리가 성공적으로 삭제되었으나 일부는 삭제할 수 없습니다. 자세한 내용은 아래를 참조하세요.',
    ],

    'checkout' => [
        'error' => '부속품이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부속품이 반출 되었습니다.',
        'unavailable' => '주변기기를 반출할 수 없습니다. 수량을 확인해주세요.⠀',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'checkout_qty' => [
            'lte' => '마지막 하나 남은 주변기기 품목에서 :checkout_qty 개 만큼 반출 요청 하셨습니다. 반출하시려는 수량 또는 주변기기의 전체 수량을 조정 하신 다음 다시 시도해주세요.⠀| :number_currently_remaining 개 남은 주변기기 품목에서 :checkout_qty 개 만큼 반출 요청 하셨습니다. 반출하시려는 수량 또는 주변기기의 전체 수량을 조정 하신 다음 다시 시도해주세요.',
        ],

    ],

    'checkin' => [
        'error' => '부속품이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부속품이 반입 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '변경된 항목이 없어서, 갱신되지 않습니다.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
