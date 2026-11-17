<?php

return [

    'does_not_exist' => 'Компонентът не съществува.',

    'create' => [
        'error' => 'Компонентът не беше създаден, моля опитайте отново.',
        'success' => 'Компонентът създаден успешно.',
    ],

    'update' => [
        'error' => 'Компонентът не беше обновен, моля опитайте отново.',
        'success' => 'Компонентът обновен успешно.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Сигурни ли сте, че желаете да изтриете този компонент?',
        'error' => 'Възникна проблем при изтриването на компонента. Моля опитайте отново.',
        'success' => 'Компонентът бе изтрит успешно.',
        'error_qty' => 'Някой компоненти от този тип са все още изписани. Моля проверете ги и пробвайте отново.',
    ],

    'checkout' => [
        'error' => 'Компонентът не беше отписан, моля опитайте отново.',
        'success' => 'Компонентът отписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'unavailable' => 'Няма достатъчно компоненти: :remaining налични, :requested заявени ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Няма полета, който да са се променили, така че нищо не е осъвременено.',
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
        'error' => 'Компонентът не беше вписан, моля опитайте отново.',
        'success' => 'Компонентът вписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
    ],

];
