<?php

return array(

    'does_not_exist' => 'Hạng mục không tồn tại.',
    'assoc_models'	 => 'Danh mục này hiện đang liên kết với một kiểu nào đó nên không thể xoá. Vui lòng cập nhật kiểu có liên quan để ngừng liên kết với danh mục này và thử xoá lại.',
    'assoc_items'	 => 'Danh mục này hiện đang liên kết với :asset_type nào đó nên không thể xoá. Vui lòng cập nhật  :asset_type có liên quan để ngừng liên kết với danh mục này và thử xoá lại.',

    'create' => array(
        'error'   => 'Hạng mục chưa được tạo. Bạn hãy thử lại.',
        'success' => 'Hạng mục đã được khởi tạo thành công.'
    ),

    'update' => array(
        'error'   => 'Hạng mục chưa được cập nhật. Bạn hãy thử lại',
        'success' => 'Hạng mục được cập nhật thành công.',
        'cannot_change_category_type'   => 'Bạn không thể thay đổi loại danh mục một khi nó đã được tạo',
    ),

    'delete' => array(
        'confirm'                => 'Bạn có chắc chắn muốn xoá hạng mục này?',
        'error'                  => 'Có vấn đề xảy ra khi xoá hạng mục này. Bạn hãy thử lại.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
