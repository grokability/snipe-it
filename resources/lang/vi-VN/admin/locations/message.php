<?php

return array(

    'does_not_exist' => 'Vị trí không tồn tại.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one asset or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Vị trí này hiện tại đã được liên kết với ít nhất một tài sản và không thể xóa. Xin vui lòng cập nhật tài sản của bạn để không còn liên kết với Vị trí này nữa và thử lại. ',
    'assoc_child_loc'	 => 'Vị trí này hiện tại là cấp parent của ít nhật một Vị trí con và không thể xóa. Xin vui lòng cập nhật Vị trí của bạn để không liên kết đến Vị trí này và thử lại. ',
    'assigned_assets' => 'Tài sản được giao',
    'current_location' => 'Vị trí hiện tại',
    'open_map' => 'Open in :map_provider_icon Maps',


    'create' => array(
        'error'   => 'Vị trí chưa tạo, xin vui lòng thử lại.',
        'success' => 'Vị trí đã tạo thành công.'
    ),

    'update' => array(
        'error'   => 'Vị trí chưa cập nhật, xin vui lòng thử lại',
        'success' => 'Vị trí đã cập nhật thành công.'
    ),

    'restore' => array(
        'error'   => 'Vị trí không được khôi phục, hãy thử lại',
        'success' => 'Vịt trí đã được khôi phục.'
    ),

    'delete' => array(
        'confirm'   	=> 'Bạn có chắc muốn xóa Vị trí này?',
        'error'   => 'Có vấn đề xảy ra khi xóa Vị trí. Xin vui lòng thử lại.',
        'success' => 'Vị trí đã xóa thành công.'
    )

);
