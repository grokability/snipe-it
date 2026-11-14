<?php

return [
    'does_not_exist' => 'Công ty này không tồn tại.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Công ty chưa được tạo, hãy thử lại.',
        'success' => 'Công ty thành công.',
    ],
    'update' => [
        'error' => 'Công ty không được cập nhật, vui lòng thử lại',
        'success' => 'Công ty đã được cập nhật thành công.',
    ],
    'delete' => [
        'confirm' => 'Bạn có chắc chắn muốn xóa công ty này?',
        'error' => 'Đã xảy ra sự cố khi xóa công ty. Vui lòng thử lại.',
        'success' => 'Công ty đã bị xóa thành công.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
