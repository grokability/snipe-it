<?php

return [

    'invalid_category_type' => 'Категорія має бути категорією витратних матеріалів.',
    'does_not_exist' => 'Витратний матеріал не існує.',

    'create' => [
        'error' => 'Витратний матеріал не було створено, спробуйте ще раз.',
        'success' => 'Витратний матеріал успішно створено.',
    ],

    'update' => [
        'error' => 'Витратний матеріал не було оновлено, спробуйте ще раз',
        'success' => 'Витратний матеріал успішно оновлено.',
    ],

    'delete' => [
        'confirm' => 'Ви впевнені що хочете видалити цей витратний матеріал?',
        'error' => 'Виникла проблема при видаленні витратного файлу. Будь ласка, спробуйте ще раз.',
        'success' => 'Витяг успішно видалено.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Витратний матеріал не було перевірено, спробуйте ще раз',
        'success' => 'Витратний матеріал успішно видано.',
        'user_does_not_exist' => 'Невірний користувач. Спробуйте ще раз.',
        'unavailable' => 'Немає достатньо витратних елементів для цього замовлення. Будь ласка, перевірте кількість залишилось. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Витратний матеріал не було перевірено, спробуйте ще раз',
        'success' => 'Витратний матеріал успішно перевірено.',
        'user_does_not_exist' => 'Вказаного користувача не існує. Спробуйте ще раз.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Будь ласка, виберіть дію зі списку.',
        'no_fields_updated' => 'Немає змінених полів, тому нічого не було оновлено.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
