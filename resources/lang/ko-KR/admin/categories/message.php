<?php

return array(

    'does_not_exist' => '분류가 존재하지 않습니다.',
    'assoc_models'	 => '이 분류는 적어도 한개의 모델과 연결되어 있기에 삭제할 수 없습니다. 이 분류를 참조하지 않게 모델을 수정하고 다시 시도해 주세요. ',
    'assoc_items'	 => '이 분류는 적어도 하나의 :asset_type 과 연결되어 있기에 삭제할 수 없습니다. 이 분류를 참조하지 않게 :asset_type 을 수정하고 다시 시도해 주세요. ',

    'create' => array(
        'error'   => '분류가 생성되지 않았습니다. 다시 시도해 주세요',
        'success' => '분류가 생성되었습니다.'
    ),

    'update' => array(
        'error'   => '분류가 갱신되지 않았습니다. 다시 시도해 주세요',
        'success' => '분류가 갱신되었습니다.',
        'cannot_change_category_type'   => '',
    ),

    'delete' => array(
        'confirm'                => '이 분류를 삭제하시겠습니까?',
        'error'                  => '분류 삭제 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
