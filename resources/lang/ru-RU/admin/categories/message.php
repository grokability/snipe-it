<?php

return array(

    'does_not_exist' => 'Категория не существует.',
    'assoc_models'	 => 'Эта категория уже связана с одной или несколькими моделями и не может быть удалена. Измените модели, чтобы они не ссылались на эту категорию, и попробуйте снова. ',
    'assoc_items'	 => 'Эта категория связана с одним или несколькими :asset_type и не может быть удалена. Измените :asset_type, чтобы они не ссылались на эту категорию, и попробуйте снова. ',

    'create' => array(
        'error'   => 'Категория не создана, попробуйте снова.',
        'success' => 'Категория создана.'
    ),

    'update' => array(
        'error'   => 'Категория не изменена, попробуйте снова',
        'success' => 'Категория изменена.',
        'cannot_change_category_type'   => 'Вы не можете изменить тип категории после ее создания',
    ),

    'delete' => array(
        'confirm'                => 'Вы уверены, что хотите удалить категорию?',
        'error'                  => 'При удалении категории возникла проблема. Попробуйте снова.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
