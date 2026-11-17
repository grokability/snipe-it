<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Vật tư phụ không tồn tại.',

    'create' => [
        'error' => 'Vật tư phụ chưa tạo, xin vui lòng thử lại.',
        'success' => 'Vật tư phụ đã tạo thành công.',
    ],

    'update' => [
        'error' => 'Vật tư phụ chưa cập nhật, xin vui lòng thử lại',
        'success' => 'Vật tư phụ đã cập nhật thành công.',
    ],

    'delete' => [
        'confirm' => 'Bạn có chắc chắn muốn xóa tài liệu này không?',
        'error' => 'Có vấn đề khi xóa vật tư phụ. Xin vui lòng thử lại.',
        'success' => 'Tài liệu tiêu thụ đã bị xoá thành công.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Vật tư phụ chưa checkout, xin thử lại',
        'success' => 'Vật tư phụ đã checkout thành công.',
        'user_does_not_exist' => 'Người dùng này không tồn tại. Xin vui lòng thử lại.',
        'unavailable' => 'Không đủ vật tư tiêu hao cho checkout. Vui lòng kiểm tra số lượng còn lại. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Vật tư phụ chưa checkin, xin thử lại',
        'success' => 'Vật tư phụ đã checkin thành công.',
        'user_does_not_exist' => 'Người dùng này không tồn tại. Xin vui lòng thử lại.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Không có trường nào được thay đổi, vì vậy không có gì được cập nhật.',
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
