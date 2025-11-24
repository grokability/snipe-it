<?php

return array(

    'does_not_exist' => 'La categoría no existe.',
    'assoc_models'	 => 'Esta categoría está asociada actualmente con al menos un modelo y no puede ser eliminada. Actualice los modelos para que ya no hagan referencia a esta categoría e inténtelo de nuevo. ',
    'assoc_items'	 => 'Esta categoría está asociada actualmente con al menos un: asset_type y no puede ser eliminada. Actualice su :asset_type para que ya no haga referencia a esta categoría e inténtelo de nuevo. ',

    'create' => array(
        'error'   => 'La categoría no fue creada, por favor inténtelo de nuevo.',
        'success' => 'Categoría creada exitosamente.'
    ),

    'update' => array(
        'error'   => 'La categoría no se actualizó, por favor, inténtelo de nuevo',
        'success' => 'Categoría actualizada exitosamente.',
        'cannot_change_category_type'   => 'No puede cambiar el tipo de categoría una vez que se ha creado',
    ),

    'delete' => array(
        'confirm'                => '¿Está seguro de que desea eliminar esta categoría?',
        'error'                  => 'Hubo un problema eliminando la categoría. Inténtelo de nuevo.',
        'success'                => 'Categoría eliminada correctamente.',
        'bulk_success'           => 'Categorías eliminadas correctamente.',
        'partial_success'        => 'Categoría eliminada correctamente. Ver información adicional a continuación. | :count categorías eliminadas correctamente. Ver información adicional a continuación.',
    )

);
