<?php

return array(

    'does_not_exist' => 'Категорія не існує.',
    'assoc_models'	 => 'Наразі ця категорія пов\'язана хоча б з однією моделлю і не може бути видалена. Будь ласка, оновіть вашу модель, щоб більше не посилатись на цю категорію та повторіть спробу. ',
    'assoc_items'	 => 'Ця категорія пов\'язана хоча б з одним :asset_type і не може бути видалена. Будь ласка, оновіть ваш :asset_type щоб більше не посилатися на цю категорію і спробуйте ще раз. ',

    'create' => array(
        'error'   => 'Категорія не була створена, будь ласка, спробуйте ще раз.',
        'success' => 'Розділ успішно створено.'
    ),

    'update' => array(
        'error'   => 'Категорія не була оновлена, будь ласка, спробуйте ще раз',
        'success' => 'Категорія успішно оновлена.',
        'cannot_change_category_type'   => 'Ви не можете змінити тип категорії після створення',
    ),

    'delete' => array(
        'confirm'                => 'Ви впевнені що бажаєте видалити цю категорію?',
        'error'                  => 'Виникла проблема з видаленням категорії. Будь ласка, спробуйте ще раз.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
