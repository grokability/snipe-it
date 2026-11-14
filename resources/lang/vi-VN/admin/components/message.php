<?php

return [

    'does_not_exist' => 'Hợp phần không tồn tại.',

    'create' => [
        'error' => 'Hợp phần đã không được tạo, vui lòng thử lại.',
        'success' => 'Thành phần thành công.',
    ],

    'update' => [
        'error' => 'Hợp phần đã không được cập nhật, vui lòng thử lại',
        'success' => 'Thành phần được cập nhật thành công.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Bạn có chắc chắn muốn xoá thành phần này?',
        'error' => 'Đã xảy ra sự cố khi xóa thành phần này. Vui lòng thử lại.',
        'success' => 'Thành phần đã được xóa thành công.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Hợp phần đã không được kiểm tra, hãy thử lại',
        'success' => 'Thành phần được kiểm tra thành công.',
        'user_does_not_exist' => 'Người dùng đó không hợp lệ. Vui lòng thử lại.',
        'unavailable' => 'Không đủ thành phần còn lại: :remaining, Yêu cầu :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Không có trường nào được thay đổi, vì vậy không có gì được cập nhật.',
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
        'error' => 'Không kiểm tra thành phần, vui lòng thử lại',
        'success' => 'Đã kiểm tra thành công thành phần.',
        'user_does_not_exist' => 'Người dùng đó không hợp lệ. Vui lòng thử lại.',
    ],

];
