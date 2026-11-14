<?php

return [

    'undeployable' => 'Los siguientes activos no pueden ser asignados y han sido removidos de la lista: :asset_tags',
    'does_not_exist' => 'Equipo inexistente.',
    'does_not_exist_var' => 'Activo con placa :asset_tag no encontrado.',
    'no_tag' => 'No se ha proporcionado ninguna placa de activo.',
    'does_not_exist_or_not_requestable' => 'Ese activo no existe o no puede ser solicitado.',
    'assoc_users' => 'Actualmente este activo está asignado a un usuario y no puede ser eliminado. Por favor, primero ingrese el activo y vuelva a intentarlo. ',
    'warning_audit_date_mismatch' => 'La próxima fecha de auditoría de este activo (:next_audit_date) es anterior a la última fecha de auditoría (:last_audit_date). Por favor, actualice la próxima fecha de auditoría.',
    'labels_generated' => 'Las etiquetas fueron generadas exitosamente.',
    'error_generating_labels' => 'Error en la generación de etiquetas.',
    'no_assets_selected' => 'No se han seleccionado activos.',

    'create' => [
        'error' => 'El activo no fue creado, por favor, inténtelo de nuevo. :(',
        'success' => 'Activo creado con éxito. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Activo con placa :tag creado con éxito. <strong><a href=":link" style="color: white;">Haga clic aquí para ver</a></strong>.',
        'multi_success_linked' => 'Activo con etiqueta :links fue creado exitosamente.|:count activos fueron creados correctamente. :links.',
        'partial_failure' => 'No se ha podido crear un activo: Motivo: :failures|No se pudieron crear :count activos. Motivos: :failures',
        'target_not_found' => [
            'user' => 'El usuario asignado no pudo ser encontrado.',
            'asset' => 'El activo asignado no pudo ser encontrado.',
            'location' => 'La ubicación asignada no pudo ser encontrada.',
        ],
    ],

    'update' => [
        'error' => 'El activo no pudo ser actualizado, por favor inténtelo de nuevo',
        'success' => 'Equipo actualizado correctamente.',
        'encrypted_warning' => 'El activo se actualizó correctamente, pero los campos personalizados cifrados no lo hicieron debido a los permisos',
        'nothing_updated' => 'Ningún campo fue seleccionado, por lo que no se actualizó nada.',
        'no_assets_selected' => 'Ningún activo fue seleccionado, por lo que no se actualizó nada.',
        'assets_do_not_exist_or_are_invalid' => 'Los activos seleccionados no se pueden actualizar.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'El activo no fue restaurado, por favor inténtelo nuevamente',
        'success' => 'Activo restaurado exitosamente.',
        'bulk_success' => 'Activo restaurado exitosamente.',
        'nothing_updated' => 'No se seleccionaron activos, por lo que no se restauró nada.',
    ],

    'audit' => [
        'error' => 'Auditoría de activos fallida: :error ',
        'success' => 'Auditoría de activos registrada correctamente.',
    ],

    'deletefile' => [
        'error' => 'Archivo no eliminado. Por favor inténtelo nuevamente.',
        'success' => 'Archivo eliminado correctamente.',
    ],

    'upload' => [
        'error' => 'Archivo(s) no cargado(s). Por favor, inténtelo nuevamente.',
        'success' => 'Archivo(s) cargado(s) exitosamente.',
        'nofiles' => 'No seleccionó ningún archivo para ser cargado, o el archivo que está tratando de cargar es demasiado grande',
        'invalidfiles' => 'Uno o más de sus archivos son demasiado grandes o son de un tipo de archivo que no está permitido. Los tipos de archivo permitidos son png, gif, jpg, doc, docx, pdf y txt.',
    ],

    'import' => [
        'import_button' => 'Importar',
        'error' => 'Algunos elementos no se pudieron importar correctamente.',
        'errorDetail' => 'Estos elementos no pudieron importarse debido a errores.',
        'success' => 'Su archivo ha sido importado',
        'file_delete_success' => 'Su archivo se ha eliminado correctamente',
        'file_delete_error' => 'El archivo no se pudo eliminar',
        'file_missing' => 'Falta el archivo seleccionado',
        'file_already_deleted' => 'El archivo seleccionado ya fue eliminado',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Uno o más atributos de la fila de encabezado contiene caracteres UTF-8 mal formados',
        'content_row_has_malformed_characters' => 'Uno o más atributos en la primera fila contienen caracteres UTF-8 mal formados',
        'transliterate_failure' => 'La transliteración de :encoding a UTF-8 falló debido a caracteres no válidos en la entrada',
        'bulk_delete' => [
            'button' => 'Eliminar :count seleccionados',
            'confirm_title' => '¿Eliminar archivos de importación seleccionados?',
            'confirm_body' => 'Está a punto de eliminar permanentemente :count archivo(s) importados. Esto no se puede deshacer.',
            'confirm_button' => 'Borrar',
            'success' => 'Archivo de importación eliminado correctamente.|:count archivos de importación eliminados correctamente.',
            'skipped' => ':count archivo(s) fueron omitidos porque no tiene permiso para eliminarlos.',
            'select_all' => 'Seleccionar todos los archivos de esta página',
            'select_row' => 'Seleccionar :file para eliminación masiva',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Importar',
        'required_fields_missing' => 'The following required fields are not mapped: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(missing asset tag)',
            'missing_asset_tag_message' => 'Row skipped: no asset tag provided.',
            'asset_not_found_message' => 'Asset with this tag does not exist. Import assets first, then re-run the history import.',
            'target_not_matched_message' => 'No :target_type matched ":name". For users, toggle the match-by options in step 1 or create the user first. For locations, make sure the CSV location name matches an existing location exactly.',
            'invalid_target_type_message' => 'Target Type ":value" is not recognized. Use "user" or "location", or leave the column blank to default to user.',
        ],
        'wizard' => [
            'step_type' => 'Choose type',
            'step_map' => 'Map fields',
            'step_preview' => 'Vista previa',
            'back' => 'Atrás',
            'next' => 'Siguiente',
            'preview_button' => 'Vista previa',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => '¿Está seguro de que desea eliminar este activo?',
        'error' => 'Hubo un problema al eliminar el activo. Por favor, inténtelo de nuevo.',
        'assigned_to_error' => '{1}Tag de activos: :asset_tag está actualmente desprotegido. Compruebe este dispositivo antes de eliminarlo. [2,*]Etiquetas de activos: :asset_tag están desactivadas. Revisa estos dispositivos antes de eliminarlos.',
        'nothing_updated' => 'No se seleccionaron los activos, por lo que no se eliminó nada.',
        'success' => 'El activo se ha eliminado correctamente.',
    ],

    'checkout' => [
        'error' => 'El activo no fue asignado, por favor inténtelo de nuevo',
        'success' => 'Equipo asignado correctamente.',
        'user_does_not_exist' => 'Este usuario no es correcto. Por favor, inténtelo de nuevo.',
        'not_available' => '¡Ese equipo no está disponible para ser asignado!',
        'no_assets_selected' => 'Debe seleccionar al menos un activo de la lista',
    ],

    'multi-checkout' => [
        'error' => 'El activo no fue asignado, por favor, intente nuevamente|Los activos no fueron asignados, por favor, intente nuevamente',
        'success' => 'El activo fue asignado correctamente|Los activos fueron asignados correctamente.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Debe seleccionar al menos un activo de la lista',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Debe seleccionar al menos un activo de la lista',
    ],

    'checkin' => [
        'error' => 'El activo no se pudo ingresar, por favor inténtelo de nuevo',
        'success' => 'El activo fue ingresado exitosamente.',
        'user_does_not_exist' => 'Este usuario no es correcto. Por favor, inténtelo de nuevo.',
        'already_checked_in' => 'El equipo ya ha sido recibido.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Solicitud no exitosa, por favor inténtelo de nuevo.',
        'success' => 'Solicitud enviada con éxito.',
        'canceled' => 'Solicitud cancelada con éxito.',
        'cancel' => 'Cancelar solicitud para este elemento',
        'duplicate' => 'You already have an active request for this item.',
        'no_active' => 'You have no active request to cancel for this item.',
        'insufficient_stock' => 'Not enough on hand to fulfill this request. Replenish first.',
        'confirm_cancel_by_admin' => "Cancel :user's request for :item?",
        'no_selection' => 'No rows were selected to fulfill.',
        'row_stale' => 'Request #:id is no longer pending and was skipped.',
        'row_qty_invalid' => 'Request #:id has an invalid quantity and was skipped.',
        'row_user_missing' => 'Request #:id has no valid target user and was skipped.',
        'row_asset_missing' => 'Request #:id has no valid target asset and was skipped.',
        'row_asset_not_requesters' => 'Request #:id skipped: selected asset does not belong to the requester.',
        'row_asset_not_available' => 'Request #:id skipped: selected asset is not an available unit of this model.',
        'row_asset_taken' => 'Request #:id skipped: selected asset was assigned to someone else before submit.',
        'row_company_mismatch' => 'Request #:id skipped: :user cannot receive items from this company.',
        'row_over_allocated' => 'Request #:id skipped: not enough on hand at the moment of submit.',
        'no_target_assets_for_user' => ':user has no assets to install into.',
        'no_available_units' => 'No available units of this model to hand out.',
        'bulk_summary' => 'Fulfilled :fulfilled of :total requests.',
        'bulk_fulfill_notification_intro' => 'The following will apply to each ticked request when fulfilled:',
    ],

];
