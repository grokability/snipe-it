<?php

return array(

    'does_not_exist' => 'Категоријата не постои.',
    'assoc_models'	 => 'Оваа категорија моментално е поврзана со барем еден модел и не може да се избрише. Ве молиме да ги ажурирате вашите модели за да не ја користите оваа категорија и обидете се повторно. ',
    'assoc_items'	 => 'Оваа категорија е моментално поврзана со најмалку еден :asset_type и не може да се избрише. Ажурирајте го вашиот :asset_type да не ја користи оваа категорија и обидете се повторно. ',

    'create' => array(
        'error'   => 'Категоријата не е креирана, обидете се повторно.',
        'success' => 'Категоријата е успешно креирана.'
    ),

    'update' => array(
        'error'   => 'Категоријата не беше ажурирана, обидете се повторно',
        'success' => 'Категоријата е успешно ажурирана.',
        'cannot_change_category_type'   => 'Не можете да го смените типот на категорија откако е создадена',
    ),

    'delete' => array(
        'confirm'                => 'Дали сте сигурни дека сакате да ја избришете оваа категорија?',
        'error'                  => 'Имаше проблем со бришење на категоријата. Обидете се повторно.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
