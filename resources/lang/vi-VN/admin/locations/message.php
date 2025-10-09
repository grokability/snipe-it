<?php

return array(

    'does_not_exist' => 'Địa phương không tồn tại.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Địa phương này hiện tại đã được liên kết với ít nhất một tài sản và không thể xóa. Xin vui lòng cập nhật tài sản của bạn để không còn liên kết với địa phương này nữa và thử lại. ',
    'assoc_child_loc'	 => 'Địa phương này hiện tại là cấp parent của ít nhật một địa phương con và không thể xóa. Xin vui lòng cập nhật địa phương của bạn để không liên kết đến địa phương này và thử lại. ',
    'assigned_assets' => 'Tài sản được giao',
    'current_location' => 'Vị trí hiện tại',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Địa phương chưa tạo, xin vui lòng thử lại.',
        'success' => 'Địa phương đã tạo thành công.'
    ),

    'update' => array(
        'error'   => 'Địa phương chưa cập nhật, xin vui lòng thử lại',
        'success' => 'Địa phương đã cập nhật thành công.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Bạn có chắc muốn xóa địa phương này?',
        'error'   => 'Có vấn đề xảy ra khi xóa địa phương. Xin vui lòng thử lại.',
        'success' => 'Địa phương đã xóa thành công.'
    )

);
