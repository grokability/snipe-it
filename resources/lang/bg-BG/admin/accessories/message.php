<?php

return [

    'does_not_exist' => 'Аксесоарът [:id] не съществува.',
    'not_found' => 'Този аксесоар не е намерен.',
    'assoc_users' => 'От този аксесоар са предадени :count броя на потребителите. Моля впишете обратно нови или върнати и опитайте отново.',

    'create' => [
        'error' => 'Аксесоарът не беше създаден. Моля опитайте отново.',
        'success' => 'Аксесоарът създаден успешно.',
    ],

    'update' => [
        'error' => 'Аксесоарът не беше обновен. Моля опитайте отново.',
        'success' => 'Аксесоарът обновен успешно.',
    ],

    'delete' => [
        'confirm' => 'Сигурни ли сте, че искате да изтриете този аксесоар?',
        'error' => 'Възникна проблем при изтриването на този аксесоар. Моля опитайте отново.',
        'success' => 'Аксесоарът бе изтрит успешно.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Аксесоарът не беше изписан. Моля опитайте отново.',
        'success' => 'Аксесоарът изписан успешно.',
        'unavailable' => 'Аксесоарт не е наличен за изписване. Проверете наличното количество',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'checkout_qty' => [
            'lte' => 'В момента има само един наличен аксесоар от този вид и вие се опитвате да изпишете :checkout_qty. Моля коригирайте количеството или общата наличност и опитайте отново.|Има :number_currently_remaining броя налични аксесоари и вие се опитвате да изпишете :checkout_qty. Моля коригирайте количеството или общата наличност и опитайте отново.',
        ],

    ],

    'checkin' => [
        'error' => 'Аксесоарът не беше вписан. Моля опитайте отново.',
        'success' => 'Аксесоарът вписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Няма полета, който да са се променили, така че нищо не е осъвременено.',
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
