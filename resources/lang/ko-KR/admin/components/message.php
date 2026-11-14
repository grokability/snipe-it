<?php

return [

    'does_not_exist' => '부품이 존재하지 않습니다.',

    'create' => [
        'error' => '부품이 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부품이 생성되었습니다.',
    ],

    'update' => [
        'error' => '부품이 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부품이 갱신 되었습니다.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => '이 부품을 삭제하시겠습니까?',
        'error' => '부품 삭제시 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '부품이 삭제되었습니다.',
        'error_qty' => '이 유형의 일부 부품이 여전히 반출되어 있습니다. 반입 후 다시 시도하세요.',
    ],

    'checkout' => [
        'error' => '부품이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부품이 반출 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'unavailable' => '남은 부품 부족: :remaining 개 남음, :requested 개 요청됨',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '변경된 항목이 없어서, 갱신되지 않습니다.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => '부품이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' => '부품이 반입되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
    ],

];
