<?php

return array(

    'does_not_exist' => '分类不存在。',
    'assoc_models'	 => '此分类下至少还有一个相关资产型号，目前不能被删除，请你确定您的所有资产型号不在此分类下，然后重试。',
    'assoc_items'	 => '此分类下至少还有一个 :asset_type ，目前不能被删除，请确定您的 :asset_type 不在此分类下，然后重试。 ',

    'create' => array(
        'error'   => '分类创建失败，请重试。',
        'success' => '分类创建成功'
    ),

    'update' => array(
        'error'   => '分类更新失败，请重试',
        'success' => '分类更新成功',
        'cannot_change_category_type'   => '分类类型一旦创建就无法更改',
    ),

    'delete' => array(
        'confirm'                => '你确定要删除这个分类吗？',
        'error'                  => '删除分类出现异常，请重试。',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
