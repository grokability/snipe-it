<?php

return array(

    'does_not_exist' => 'Phụ kiện [:id] Không tồn tại.',
    'not_found' => 'Phụ kiện không được tìm thấy.',
    'assoc_users'	 => 'Phụ kiện này hiện có :count cái đã giao cho người dùng. Bạn hãy nhập lại vào trong phần phụ kiện và thử lại lần nữa. ',

    'create' => array(
        'error'   => 'Phụ kiện chưa tạo, hãy thử một lần nữa.',
        'success' => 'Phụ kiện đã được tạo thành công.'
    ),

    'update' => array(
        'error'   => 'Phụ kiện chưa được cập nhật, vui lòng thử lại',
        'success' => 'Phụ kiện đã được cập nhật thành công.'
    ),

    'delete' => array(
        'confirm'   => 'Bạn có chắc muốn xoá phụ kiện này không?',
        'error'   => 'Có lỗi xảy ra khi xoá phụ kiện. Vui lòng thử lại.',
        'success' => 'Phụ kiện đã được xoá thành công.'
    ),

     'checkout' => array(
        'error'   		=> 'Phụ kiện chưa được xuất kho. Bạn hãy thử lại',
        'success' 		=> 'Phụ kiện được xuất kho thành công.',
        'unavailable'   => 'Không có sẵn phụ kiện để xuất. Hãy kiểm tra số lượng có sẵn',
        'user_does_not_exist' => 'Người dùng này không tồn tại. Bạn hãy thử lại.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Phụ kiện chưa được kho. Bạn hãy thử lại',
        'success' 		=> 'Phuk kiện được nhập kho thành công.',
        'user_does_not_exist' => 'Người dùng này không tồn tại. Bạn hãy thử lại.'
    )


);
