<?php

return [

    'does_not_exist' => 'El componente no existe.',

    'create' => [
        'error' => 'El componente no fue creado, por favor inténtelo de nuevo.',
        'success' => 'El componente se creó satisfactoriamente.',
    ],

    'update' => [
        'error' => 'El componente no se actualizó, por favor inténtelo de nuevo',
        'success' => 'Componente actualizado correctamente.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => '¿Está seguro de que desea eliminar este componente?',
        'error' => 'Hubo un problema eliminando el componente. Por favor, inténtelo de nuevo.',
        'success' => 'El componente fue borrado correctamente.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'El componente no fue asignado, por favor, inténtelo de nuevo',
        'success' => 'El componente se ha asignado correctamente.',
        'user_does_not_exist' => 'Ese usuario no es válido. Por favor, inténtelo de nuevo.',
        'unavailable' => 'No quedan suficientes componentes: :remaining disponibles, :requested solicitados ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ningún cambio fue cambiado, así que nada se actualizó.',
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
        'error' => 'No se realizó el ingreso del componente, por favor inténtelo de nuevo',
        'success' => 'Componente ingresado con éxito.',
        'user_does_not_exist' => 'Ese usuario no es válido. Por favor, inténtelo de nuevo.',
    ],

];
