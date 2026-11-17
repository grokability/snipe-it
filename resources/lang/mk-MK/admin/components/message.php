<?php

return [

    'does_not_exist' => 'Компонентата не постои.',

    'create' => [
        'error' => 'Компонентата не е креирана, обидете се повторно.',
        'success' => 'Компонентата е успешно креирана.',
    ],

    'update' => [
        'error' => 'Компонентата не беше ажурирана, обидете се повторно',
        'success' => 'Компонентата е успешно ажурирана.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Дали сте сигурни дека сакате да ја избришете оваа компонента?',
        'error' => 'Имаше проблем со бришење на компонентата. Обидете се повторно.',
        'success' => 'Компонентата беше успешно избришана.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Компонентата не беше задолжена, обидете се повторно',
        'success' => 'Компонентата е задолжена.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
        'unavailable' => 'Нема доволно компоненти: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Не беа сменети полиња, затоа ништо не беше ажурирано.',
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
        'error' => 'Компонентата не беше раздолжена, обидете се повторно',
        'success' => 'Компонентата е раздолжена.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
    ],

];
