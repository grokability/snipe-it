<?php

return array(

    'deleted' => 'Xóa nhà cung cấp',
    'does_not_exist' => 'Nhà cung cấp không tồn tại.',


    'create' => array(
        'error'   => 'Nhà cung cấp chưa được tạo, xin thử lại.',
        'success' => 'Nhà cung cấp đã tạo thành công.'
    ),

    'update' => array(
        'error'   => 'Nhà cung cấp chưa được cập nhật, xin thử lại',
        'success' => 'Nhà cung cấp đã cập nhật thành công.'
    ),

    'delete' => array(
        'confirm'   => 'Bạn có chắc muốn xóa nhà cung cấp này?',
        'error'   => 'Có vấn đề xảy ra khi xóa nhà cung cấp. Xin thử lại.',
        'success' => 'Nhà cung cấp đã xóa thành công.',
        'assoc_assets'	 => 'Nhà cung cấp này hiện tại đang có liên kết với: asset_count tài sản và không thể xóa. Xin vui lòng cập nhật các tài sản để không còn liên kết với nhà cung cấp này và thử lại. ',
        'assoc_licenses'	 => 'Nhà cung cấp này hiện tại đang có liên kết với: licenses_count license(s) và không thể xóa. Xin vui lòng cập nhật các licence để không còn liên kết với nhà cung cấp này và thử lại. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
