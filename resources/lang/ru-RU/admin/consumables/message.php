<?php

return [

    'invalid_category_type' => 'Категория должна быть категорией расходных материалов.',
    'does_not_exist' => 'Расходный материал не существует.',

    'create' => [
        'error' => 'Расходный материал не создан, попробуйте снова.',
        'success' => 'Расходный материал создан.',
    ],

    'update' => [
        'error' => 'Расходный материал не был обновлён, попробуйте снова',
        'success' => 'Расходный материал обновлён.',
    ],

    'delete' => [
        'confirm' => 'Вы уверены, что хотите удалить расходный материал?',
        'error' => 'В процессе удаления расходного материала произошла ошибка. Попробуйте снова.',
        'success' => 'Расходный материал удалён.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Расходный материал не был выдан, попробуйте снова',
        'success' => 'Расходный материал выдан.',
        'user_does_not_exist' => 'Неверный пользователь. Попробуйте снова.',
        'unavailable' => 'Недостаточно расходных материалов для выдачи. Проверьте их количество. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Расходный материал не был возвращён, попробуйте снова',
        'success' => 'Расходный материал возвращён.',
        'user_does_not_exist' => 'Неверный пользователь. Попробуйте снова.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Никаких изменений нет, поэтому ничего не обновлено.',
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
