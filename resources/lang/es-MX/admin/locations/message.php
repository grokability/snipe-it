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

    'bulkedit' => [
        'error' => 'Ningún cambio fue cambiado, así que nada se actualizó.',
        'success' => 'Location successfully updated.|:count locations successfully updated.',
        'warn' => 'Edit the fields below to update this location. Fields you leave blank will not change on the location.|Edit the fields below to update all :count selected locations. Fields you leave blank will not change on any of them.',
        'show_selected' => '1 ubicación seleccionada|:count ubicaciones seleccionadas',
        'company_scope_mismatch_partial' => 'The company was not changed on 1 location because items or users at that location belong to different companies. Update or move those first.|The company was not changed on :count locations because items or users at those locations belong to different companies. Update or move those first.',
        'company_scope_mismatch_all' => 'No locations were reassigned. The requested company does not match items or users at the selected location.|No locations were reassigned. The requested company does not match items or users at any of the :count selected locations.',
        'parent_company_mismatch_partial' => 'The parent or company was not changed on 1 location because it would leave the location in a different company than its parent.|The parent or company was not changed on :count locations because it would leave those locations in a different company than their parent.',
        'parent_company_mismatch_all' => 'No changes were saved. The requested parent or company would leave the location in a different company than its parent.|No changes were saved. The requested parent or company would leave every one of the :count selected locations in a different company than their parent.',
    ],

];
