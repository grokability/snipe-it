<?php

return [

    'invalid_category_type' => 'Категоријата мора да биде категорија на потрошни материјали.',
    'does_not_exist' => 'Потрошниот материјал не постои.',

    'create' => [
        'error' => 'Потрошниот материјал не е креиран, обидете се повторно.',
        'success' => 'Потрошниот материјал е успешно креиран.',
    ],

    'update' => [
        'error' => 'Потрошниот материјал не е ажуриран, обидете се повторно',
        'success' => 'Потрошниот материјал е успешно ажуриран.',
    ],

    'delete' => [
        'confirm' => 'Дали сте сигурни дека сакате да го избришете овој потрошен материјал?',
        'error' => 'Имаше проблем со бришење на потрошниот материјал. Обидете се повторно.',
        'success' => 'Потрошниот материјал беше успешно избришан.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Потрошниот материјал не е задолжен, обидете се повторно',
        'success' => 'Потрошниот материјал е успешно задолжен.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
        'unavailable' => 'Нема доволно потрошни материјали за ова позајмување. Ве молиме проверете ја преостанатата количина. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Потрошниот материјал не е раздолжен, обидете се повторно',
        'success' => 'Потрошниот материјал е успешно раздолжен.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Не беа сменети полиња, затоа ништо не беше ажурирано.',
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
