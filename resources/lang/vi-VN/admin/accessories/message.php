<?php

return [

    'does_not_exist' => 'Phụ kiện [:id] Không tồn tại.',
    'not_found' => 'Phụ kiện không được tìm thấy.',
    'assoc_users' => 'Phụ kiện này hiện có :count cái đã giao cho người dùng. Bạn hãy nhập lại vào trong phần phụ kiện và thử lại lần nữa. ',

    'create' => [
        'error' => 'Phụ kiện chưa tạo, hãy thử một lần nữa.',
        'success' => 'Phụ kiện đã được tạo thành công.',
    ],

    'update' => [
        'error' => 'Phụ kiện chưa được cập nhật, vui lòng thử lại',
        'success' => 'Phụ kiện đã được cập nhật thành công.',
    ],

    'delete' => [
        'confirm' => 'Bạn có chắc muốn xoá phụ kiện này không?',
        'error' => 'Có lỗi xảy ra khi xoá phụ kiện. Vui lòng thử lại.',
        'success' => 'Phụ kiện đã được xoá thành công.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Phụ kiện chưa được xuất kho. Bạn hãy thử lại',
        'success' => 'Phụ kiện được xuất kho thành công.',
        'unavailable' => 'Không có sẵn phụ kiện để xuất. Hãy kiểm tra số lượng có sẵn',
        'user_does_not_exist' => 'Người dùng này không tồn tại. Bạn hãy thử lại.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Phụ kiện chưa được kho. Bạn hãy thử lại',
        'success' => 'Phuk kiện được nhập kho thành công.',
        'user_does_not_exist' => 'Người dùng này không tồn tại. Bạn hãy thử lại.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Không có trường nào được thay đổi, vì vậy không có gì được cập nhật.',
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
