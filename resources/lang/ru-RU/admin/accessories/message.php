<?php

return [

    'does_not_exist' => 'Аксессуар [:id] не существует.',
    'not_found' => 'Этот аксессуар не найден.',
    'assoc_users' => 'Этот аксессуар выдан пользователям в количестве :count. Сделайте возврат аксессуара и попробуйте снова. ',

    'create' => [
        'error' => 'Аксессуар не был создан, попробуйте снова.',
        'success' => 'Аксессуар создан.',
    ],

    'update' => [
        'error' => 'Аксессуар не обновлён, попробуйте снова',
        'success' => 'Аксессуар обновлён.',
    ],

    'delete' => [
        'confirm' => 'Вы уверены, что хотите удалить этот аксессуар?',
        'error' => 'Возникла проблема при удалении аксессуара. Пожалуйста, попробуйте снова.',
        'success' => 'Аксессуар удалён.',
        'bulk_success' => 'Аксессуар успешно удален.|:count аксессуаров были успешно удалены.',
        'partial_success' => ':count аксессуар был успешно удален, но другие не могут быть удалены. Смотрите подробности ниже. |:count аксессуаров были успешно удалены, но другие не могут быть удалены. Смотрите подробности ниже.',
    ],

    'checkout' => [
        'error' => 'Ошибка при выдаче аксессуара, попробуйте снова',
        'success' => 'Аксессуар выдан.',
        'unavailable' => 'Нет доступных аксессуаров для выдачи. Проверьте их количество',
        'user_does_not_exist' => 'Неверный пользователь. Попробуйте снова.',
        'checkout_qty' => [
            'lte' => 'Вы пытаетесь выдать :checkout_qty аксессуаров, но в настоящее время доступен только один аксессуар данного типа. Пожалуйста, измените количество для выдачи или доступный остаток и попробуйте снова.|Вы пытаетесь выдать :checkout_qty аксессуаров, но доступно всего :number_currently_remaining. Пожалуйста, измените количество для выдачи или доступный остаток этого аксессуара и попробуйте снова.',
        ],

    ],

    'checkin' => [
        'error' => 'Ошибка при возврате аксессуара, попробуйте снова',
        'success' => 'Аксессуар возвращён.',
        'user_does_not_exist' => 'Неверный пользователь. Попробуйте снова.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Никаких изменений нет, поэтому ничего не обновлено.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
