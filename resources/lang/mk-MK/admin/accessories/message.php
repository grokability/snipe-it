<?php

return [

    'does_not_exist' => 'Додатокот [:id] не постои.',
    'not_found' => 'Додатокот не е пронајден.',
    'assoc_users' => 'Овој додаток во моментов има :count ставки задолжени на корисници. Ве молиме проверете во додатоците и обидете се повторно. ',

    'create' => [
        'error' => 'Додатокот не е креиран, обидете се повторно.',
        'success' => 'Додатокот беше успешно креиран.',
    ],

    'update' => [
        'error' => 'Додатокот не беше ажуриран, обидете се повторно',
        'success' => 'Додатокот беше успешно ажуриран.',
    ],

    'delete' => [
        'confirm' => 'Дали сте сигурни дека сакате да го избришете овој додаток?',
        'error' => 'Имаше проблем со бришење на додатокот. Обидете се повторно.',
        'success' => 'Додатокот беше успешно избришан.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Додатокот не беше задолжен, обидете се повторно',
        'success' => 'Додатокот е задолжен.',
        'unavailable' => 'Додатокот не е достапен за позајмување. Проверете ја достапната количина',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
        'checkout_qty' => [
            'lte' => 'Во моментов е достапен само еден додаток од овој тип, а вие се обидувате да позајмите :checkout_qty. Ве молиме, прилагодете ја количината за позајмување или вкупната залиха на овој додаток и обидете се повторно. Има вкупно :number_currently_remaining достапни додатоци, а вие се обидувате да позајмите :checkout_qty. Ве молиме, прилагодете ја количината за позајмување или вкупната залиха на овој додаток и обидете се повторно.',
        ],

    ],

    'checkin' => [
        'error' => 'Додатокот не беше раздолжен, обидете се повторно',
        'success' => 'Додатокот е раздолжен.',
        'user_does_not_exist' => 'Тој корисник е неважечки. Обидете се повторно.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Не беа сменети полиња, затоа ништо не беше ажурирано.',
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
