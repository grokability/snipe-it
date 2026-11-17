<?php

return [

    'does_not_exist' => 'Компонент не существует.',

    'create' => [
        'error' => 'Не удалось создать компонент, попробуйте снова.',
        'success' => 'Компонент создан.',
    ],

    'update' => [
        'error' => 'Не удалось обновить компонент, попробуйте снова',
        'success' => 'Компонент обновлён.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Вы уверены, что хотите удалить этот компонент?',
        'error' => 'Возникла проблема при удалении компонента. Попробуйте снова.',
        'success' => 'Компонент успешно удалён.',
        'error_qty' => 'Некоторые компоненты этого типа всё ещё выданы. Проверьте их и попробуйте снова.',
    ],

    'checkout' => [
        'error' => 'Не удалось выдать компонент, попробуйте снова',
        'success' => 'Компонент выдан.',
        'user_does_not_exist' => 'Неверный пользователь. Попробуйте снова.',
        'unavailable' => 'Недостаточно компонентов: :remaining - осталось, :requested - запрошено',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Никаких изменений нет, поэтому ничего не обновлено.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Не удалось вернуть компонент, попробуйте снова',
        'success' => 'Компонент возвращён.',
        'user_does_not_exist' => 'Неверный пользователь. Попробуйте снова.',
    ],

];
