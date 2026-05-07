<?php

return [

    'does_not_exist' => 'La ubicación no existe.',
    'assoc_users' => 'Esta ubicación no se puede eliminar actualmente porque es la ubicación de al menos un activo o usuario, tiene activos asignados o es la ubicación padre de otra ubicación. Por favor, actualice sus registros para que ya no hagan referencia a esta ubicación e inténtalo de nuevo ',
    'assoc_assets' => 'Esta ubicación está actualmente asociada con al menos un activo y no puede ser eliminada. Por favor actualice sus activos para que ya no hagan referencia a esta ubicación e inténtelo de nuevo. ',
    'assoc_child_loc' => 'Esta ubicación es actualmente el padre de al menos una ubicación hija y no puede ser eliminada.   Por favor actualice sus ubicaciones para que ya no hagan referencia a esta ubicación e inténtelo de nuevo. ',
    'assigned_assets' => 'Activos asignados',
    'current_location' => 'Ubicación actual',
    'deleted_warning' => 'Esta ubicación ha sido eliminada. Por favor, restaurala antes de intentar realizar cualquier cambio.',

    'create' => [
        'error' => 'La ubicación no pudo ser creada, por favor, inténtelo de nuevo.',
        'success' => 'La ubicación fue creada exitosamente.',
    ],

    'update' => [
        'error' => 'La ubicación no pudo ser actualizada, por favor inténtelo de nuevo',
        'success' => 'La ubicación fue actualizada exitosamente.',
    ],

    'restore' => [
        'error' => 'No se ha restaurado la ubicación, inténtelo de nuevo',
        'success' => 'La ubicación fue restaurada exitosamente.',
    ],

    'delete' => [
        'confirm' => '¿Está seguro de que desea eliminar esta ubicación?',
        'error' => 'Hubo un problema eliminando la ubicación. Por favor, inténtelo de nuevo.',
        'success' => 'La ubicación fue eliminada exitosamente.',
    ],

];
