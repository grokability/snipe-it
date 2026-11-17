<?php

return [

    'invalid_category_type' => 'Категоряита трябва да бъде от консумативи.',
    'does_not_exist' => 'Консуматива не съществува.',

    'create' => [
        'error' => 'Консумативът не беше създаден. Моля опитайте отново.',
        'success' => 'Консумативът създаден успешно.',
    ],

    'update' => [
        'error' => 'Консумативът не беше обновен. Моля опитайте отново.',
        'success' => 'Консумативът обновен успешно.',
    ],

    'delete' => [
        'confirm' => 'Желаете ли изтриване на консуматива?',
        'error' => 'Проблем при изтриването на консуматива. Моля опитайте отново.',
        'success' => 'Консумативът изтрит успешно.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Консумативът не беше изписан. Моля опитайте отново.',
        'success' => 'Консумативът изписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'unavailable' => 'Няма достатъчно консумативи. Моля проверете количеството налични. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Консумативът не беше вписан. Моля опитайте отново.',
        'success' => 'Консумативът вписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Няма полета, който да са се променили, така че нищо не е осъвременено.',
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
