<?php

return array(

    'accepted'                  => 'Bạn đã xác nhận tài sản này thành công.',
    'declined'                  => 'Bạn đã từ chối tài sản này thành công.',
    'bulk_manager_warn'	        => 'Người dùng của bạn đã được cập nhật thành công, tuy nhiên mục nhập của người quản lý của bạn không được lưu bởi vì người quản lý bạn đã chọn cũng nằm trong danh sách người dùng cần chỉnh sửa, và người dùng không phải là người quản lý của họ. Vui lòng chọn người dùng của bạn một lần nữa, ngoại trừ người quản lý.',
    'user_exists'               => 'Người dùng đã tồn tại!',
    'user_not_found'            => 'User does not exist or you do not have permission view them.',
    'user_login_required'       => 'Trường đăng nhập là bắt buộc',
    'user_has_no_assets_assigned' => 'Hiện không có tài sản nào được gán cho người dùng.',
    'user_password_required'    => 'Mật khẩu là bắt buộc.',
    'insufficient_permissions'  => 'Không có đủ quyền.',
    'user_deleted_warning'      => 'Người dùng này đã bị xóa. Bạn sẽ phải phục hồi người dùng này để chỉnh sửa nó hoặc khởi gán nó đến tài sản mới.',
    'ldap_not_configured'        => 'Tích hợp LDAP thì chưa được cấu hình cho cài đặt này.',
    'password_resets_sent'      => 'Những người dùng với trạng thái kích hoạt đã chọn và có địa chỉ email hợp lệ sẽ nhận được liên kết đặt lại mật khẩu.',
    'password_reset_sent'       => 'Đường đẫn khôi phục mật khẩu được gửi đến :email!',
    'user_has_no_email'         => 'Người dùng này không có địa chỉ email trong hồ sơ của họ.',
    'log_record_not_found'        => 'Không thể tìm thấy bản ghi nhật ký phù hợp cho người dùng này.',


    'success' => array(
        'create'    => 'Người dùng đã được tạo thành công.',
        'update'    => 'Người dùng đã được cập nhật thành công.',
        'update_bulk'    => 'Người dùng đã được cập nhật thành công!',
        'delete'    => 'Người dùng đã được xóa thành công.',
        'ban'       => 'Người dùng đã bị cấm thành công.',
        'unban'     => 'Phục hồi người dùng bị cấm thành công.',
        'suspend'   => 'Đã tạm ngưng người dùng thành công.',
        'unsuspend' => 'Đã phục hồi người dùng bị tạm ngưng thành công.',
        'restored'  => 'Người dùng đã được phục hồi thành công.',
        'import'    => 'Nhập danh sách người dùng thành công.',
    ),

    'error' => array(
        'create' => 'Có vấn đề xảy ra khi tạo người dùng. Xin thử lại lần nữa.',
        'update' => 'Có vấn đề xảy ra khi cập nhật người dùng. Xin thử lại lần nữa.',
        'delete' => 'Có vấn đề xảy ra khi xóa người dùng. Xin thử lại lần nữa.',
        'delete_has_assets' => 'Người dùng này đã gán các mục và không thể bị xóa.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Có vấn đề xảy ra khi phục hồi người dùng bị tạm ngưng. Xin thử lại.',
        'import'    => 'Có vấn đề xảy ra khi nhập danh sách người dùng. Xin thử lại.',
        'asset_already_accepted' => 'Tài sản này đã được chấp thuận.',
        'accept_or_decline' => 'Bạn phải chấp nhận hoặc từ chối tài sản này.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Nội dung bạn đã cố gắng chấp nhận không được kiểm tra cho bạn.',
        'ldap_could_not_connect' => 'Không thể kết nối đến máy chủ LDAP. Xin vui lòng kiểm tra lại cấu hình máy chủ LDAP của bạn ở trong tập tin cấu hình LDAP. <br>Lỗi từ máy chủ LDAP:',
        'ldap_could_not_bind' => 'Không thể liên kết đến máy chủ LDAP. Xin vui lòng kiểm tra lại cấu hình máy chủ LDAP của bạn ở trong tập tin cấu hình LDAP. <br>Lỗi từ máy chủ LDAP: ',
        'ldap_could_not_search' => 'Không thể tìm thấy máy chủ LDAP. Xin vui lòng kiểm tra cấu hình cài đặt máy chủ LDAP của bạn ở trong tập tin cấu hình LDAP. <br>Lỗi từ máy chủ LDAP:',
        'ldap_could_not_get_entries' => 'Không thể lấy các mục từ máy chủ LDAP. Xin vui lòng kiểm tra lại cấu hình máy chủ LDAP của bạn ở trong tập tin cấu hình LDAP. <br>Lỗi từ máy chủ LDAP:',
        'password_ldap' => 'Mật khẩu cho tài khoản này được quản lý bởi LDAP / Active Directory. Vui lòng liên hệ với bộ phận CNTT của bạn để thay đổi mật khẩu.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Tập tin không xóa được. Xin vui lòng thử lại.',
        'success' => 'Tập tin đã xóa thành công.',
    ),

    'upload' => array(
        'error'   => 'Tập tin không tải lên được. Xin vui lòng thử lại.',
        'success' => 'Tập tin đã tải lên thành công.',
        'nofiles' => 'Bạn đã không chọn tập tin nào để tải lên',
        'invalidfiles' => 'Một hoặc nhiều tập tin của bạn có dung lượng quá lớn hoặc loại tập tin không cho phép tải lên. Chỉ cho phép những loại tập tin png, gif, jpg, doc, docx, pdf, and txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Người dùng này chưa thiết lập email nào.',
        'success' => 'Người dùng đã được thông báo về tồn kho hiện tại của họ.'
    )
);