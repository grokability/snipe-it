<?php

return [

    'invalid_category_type' => '카테고리는 소모품 카테고리여야 합니다.',
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
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => '소모품이 반출되지 않았습니다. 다시 시도해 주세요.',
        'success' => '소모품이 반출 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
        'unavailable' => '이 반출에 필요한 소모품이 충분하지 않습니다. 남은 수량을 확인하세요.',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => '소모품이 반입되지 않았습니다. 다시 시도해 주세요.',
        'success' => '소모품이 반입 되었습니다.',
        'user_does_not_exist' => '잘못된 사용자 입니다. 다시 시도해 주세요.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => '변경된 항목이 없어서, 갱신되지 않습니다.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
