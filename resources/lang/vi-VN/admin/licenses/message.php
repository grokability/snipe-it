<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Tài sản bản đang cố gắng liên kết với bản quyền này không tồn tại.',
    'owner_doesnt_match_asset' => 'Tài sản bạn đang cố gắng liên kết với bản quyền đã được sở hữu hởi một người nào đó khác với người đang được lựa chọn để gán trong danh sách xổ xuống.',
    'assoc_users'	 => 'Bản quyền này hiện tại đã được checkout đến một người dùng và có thể đã bị xóa. Xin vui lòng kiểm tra lại bản quyền đầu tiên, và kế đến có gắng xóa thêm lần nữa. ',
    'select_asset_or_person' => 'Bạn phải chọn một nội dung hoặc người dùng, nhưng không phải cả hai.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Bản quyền chưa được tạo, xin vui lòng thử lại.',
        'success' => 'Bản quyền đã được tạo thành công.'
    ),

    'deletefile' => array(
        'error'   => 'Tập tin không xóa được. Xin vui lòng thử lại.',
        'success' => 'Tập tin đã xóa thành công.',
    ),

    'upload' => array(
        'error'   => 'Tập tin không tải lên được. Xin vui lòng thử lại.',
        'success' => 'Tập tin đã tải lên thành công.',
        'nofiles' => 'Bạn chưa chọn bất kỳ tập tin nào để tải lên, hoặc tập tin bạn đang cố gắng tải lên có dung lượng quá lớn',
        'invalidfiles' => 'Một hoặc nhiều tệp của bạn quá lớn hoặc là loại tập tin không được phép. Các loại tệp được cho phép là png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml và lic.',
    ),

    'update' => array(
        'error'   => 'Bản quyền chưa được cập nhật, xin vui lòng thử lại',
        'success' => 'Bản quyền đã cập nhật thành công.'
    ),

    'delete' => array(
        'confirm'   => 'Bạn có chắc muốn xóa bản quyền này?',
        'error'   => 'Có vấn đề xảy ra khi xóa bản quyền này. Xin vui lòng thử lại.',
        'success' => 'Bản quyền đã xóa thành công.'
    ),

    'checkout' => array(
        'error'   => 'Có vấn đề xảy ra khi checkout bản quyền. Xin vui lòng thử lại.',
        'success' => 'Bản quyền đã được checkout thành công',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Có vấn đề xảy ra khi checkin bản quyền. Xin vui lòng thử lại.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Bản quyền đã được checkin thành công'
    ),

);
