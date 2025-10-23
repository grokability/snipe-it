<?php

return array(

    'does_not_exist' => 'Категорията не съществува.',
    'assoc_models'	 => 'Тази категория е свързана с поне един модел или не може да бъде изтрита Моля проверете за модели, които все още се препращат към тази категория и опитайте отново.',
    'assoc_items'	 => 'Тази категория е свързана с поне един :asset_type или не може да бъде изтрита Моля проверете за :asset_type, които все още се препращат към тази категория и опитайте отново.',

    'create' => array(
        'error'   => 'Категорията не беше създадена. Моля опитайте отново.',
        'success' => 'Категорията е създадена.'
    ),

    'update' => array(
        'error'   => 'Категорията не беше обновена. Моля опитайте отново',
        'success' => 'Категорията е обновена успешно.',
        'cannot_change_category_type'   => 'Не може да смените вида на категорията след като е създадена',
    ),

    'delete' => array(
        'confirm'                => 'Желаете ли да изтриете тази категория?',
        'error'                  => 'Проблем при изтриване на категорията. Моля опитайте отново.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
