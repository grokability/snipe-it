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
        'success' => 'El accesorio fue borrado con éxito.',
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

];
