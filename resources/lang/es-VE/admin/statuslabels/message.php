<?php

return [

    'does_not_exist' => 'Etiqueta de estado no existe.',
    'deleted_label' => 'Etiqueta de estado borrado',
    'assoc_assets'	 => 'Esta etiqueta de estado está actualmente asociada con al menos un activo y no se puede eliminar. Por favor actualice sus activos para que ya no hagan referencia a este estado e inténtelo de nuevo. ',

    'create' => [
        'error'   => 'La etiqueta de estado no pudo ser creada, por favor inténtelo de nuevo.',
        'success' => 'La etiqueta de estado fue creada con éxito.',
    ],

    'update' => [
        'error'   => 'La etiqueta de estado no se actualizó, por favor inténtelo de nuevo',
        'success' => 'La etiqueta de estado fue actualizada exitosamente.',
    ],

    'delete' => [
        'confirm'   => '¿Está seguro de que desea eliminar esta etiqueta de estado?',
        'error'   => 'Hubo un problema borrando la etiqueta de estado. Por favor, inténtelo de nuevo.',
        'success' => 'La etiqueta de estado se ha eliminado con éxito.',
    ],

    'help' => [
        'undeployable'   => 'Estos activos no pueden ser asignados.',
        'deployable'   => 'Estos activos pueden ser asignados. Una vez estén asignados, asumirán el meta estado de <i class="fas fa-circle text-blue"></i> <strong>Asignado</strong>.',
        'archived'   => 'Estos activos no pueden ser asignados y solo se mostrarán en la vista de Archivados. Esto es útil para mantener información de activos por razones de presupuesto o de revisión histórica y al mismo tiempo se excluyen de los activos que se pueden usar en el día a día.',
        'pending'   => 'Estos activos aún no pueden asignarse, y suelen utilizarse para elementos que están en reparación, pero que se espera que regresen a circulación.',
    ],

];
