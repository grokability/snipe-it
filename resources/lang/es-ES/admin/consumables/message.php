<?php

return [

    'invalid_category_type' => 'La categoría debe ser una categoría para consumibles.',
    'does_not_exist' => 'El consumible no existe.',

    'create' => [
        'error' => 'El consumible no fue creado, por favor inténtelo nuevamente.',
        'success' => 'Consumible creado con éxito.',
    ],

    'update' => [
        'error' => 'El consumible no fue actualizado, por favor, inténtelo de nuevo',
        'success' => 'Consumible actualizado correctamente.',
    ],

    'delete' => [
        'confirm' => '¿Está seguro de que desea eliminar este consumible?',
        'error' => 'Hubo un problema al eliminar el consumible. Por favor inténtelo de nuevo.',
        'success' => 'El consumible ha sido eliminado correctamente.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'El consumible no fue asignado, por favor inténtelo de nuevo',
        'success' => 'Consumible asignado correctamente.',
        'user_does_not_exist' => 'Ese usuario no es válido. Por favor, inténtelo de nuevo.',
        'unavailable' => 'No hay suficientes consumibles para esta asignación. Por favor, compruebe la cantidad disponible. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'El consumible no fue ingresado, por favor inténtelo de nuevo',
        'success' => 'El consumible fue ingresado correctamente.',
        'user_does_not_exist' => 'Ese usuario no es válido. Por favor, inténtelo de nuevo.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ningún cambio fue cambiado, así que nada se actualizó.',
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
