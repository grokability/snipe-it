<?php

return [

    'does_not_exist' => 'El accesorio [:id] no existe.',
    'not_found' => 'Ese accesorio no fue encontrado.',
    'assoc_users' => 'Este accesorio actualmente tiene :count elemento(s) asignado(s) a usuarios. Por favor realice el ingreso de los accesorios y vuelva a intentar. ',

    'create' => [
        'error' => 'El accesorio no fue creado, por favor inténtelo de nuevo.',
        'success' => 'Accesorio creado correctamente.',
    ],

    'update' => [
        'error' => 'El accesorio no fue actualizado, por favor, inténtelo de nuevo',
        'success' => 'El accesorio fue actualizado con éxito.',
    ],

    'delete' => [
        'confirm' => '¿Está seguro de que desea eliminar este accesorio?',
        'error' => 'Hubo un problema eliminando el accesorio. Por favor, inténtelo de nuevo.',
        'success' => 'El accesorio ha sido borrado con éxito.',
        'bulk_success' => 'Accesorio eliminado con éxito.|:count accesorios eliminados correctamente.',
        'partial_success' => ':count accesorio se ha eliminado con éxito, pero otros no se han podido eliminar. Ver abajo para más detalles.|:count accesorios fueron eliminados con éxito, pero otros no pudieron ser eliminados. Ver abajo para más detalles.',
    ],

    'checkout' => [
        'error' => 'El accesorio no fue asignado, por favor vuelva a intentarlo',
        'success' => 'Accesorio asignado correctamente.',
        'unavailable' => 'El accesorio no está disponible para ser asignado. Compruebe la cantidad disponible',
        'user_does_not_exist' => 'Este usuario no es válido. Por favor, inténtelo de nuevo.',
        'checkout_qty' => [
            'lte' => 'En este momento solo existe un accesorio disponible de este tipo y está tratando de asignar :checkout_qty. Por favor, ajuste la cantidad asignada o el total de existencias de este accesorio e intente nuevamente.|Existen en total :number_currently_remaining accesorios disponibles y está tratando de asignar :checkout_qty. Por favor, ajuste la cantidad asignada o el total de existencias de este accesorio e intente nuevamente.',
        ],

    ],

    'checkin' => [
        'error' => 'El accesorio no fue recibido, por favor vuelva a intentarlo',
        'success' => 'El accesorio ha sido ingresado correctamente.',
        'user_does_not_exist' => 'Ese usuario no es válido. Por favor, inténtelo de nuevo.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Ningún cambio fue cambiado, así que nada se actualizó.',
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
