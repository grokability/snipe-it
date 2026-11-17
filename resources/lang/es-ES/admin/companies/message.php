<?php

return [
    'does_not_exist' => 'La compañía no existe.',
    'deleted' => 'Compañía eliminada',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'La compañía no fue creada, por favor, inténtelo de nuevo.',
        'success' => 'Compañía creada satisfactoriamente.',
    ],
    'update' => [
        'error' => 'La compañía no fue actualizada, por favor, inténtelo de nuevo',
        'success' => 'Compañía actualizada correctamente.',
    ],
    'delete' => [
        'confirm' => '¿Está seguro de que quiere eliminar esta compañía?',
        'error' => 'Hubo un problema eliminando la compañía. Por favor, inténtelo de nuevo.',
        'success' => 'La compañía fue eliminada correctamente.',
        'bulk_success' => 'Compañía eliminada exitosamente.|:count empresas se eliminaron correctamente.',
        'partial_success' => 'Compañía eliminada con éxito. Ver información adicional a continuación. | :count empresas fueron eliminadas correctamente. Ver información adicional a continuación.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
