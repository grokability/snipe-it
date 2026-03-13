<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Sus usuarios han sido actualizados con éxito, sin embargo, la entrada supervisor (manager) no fue guardada porque el supervisor seleccionado también estaba en la lista de usuarios a editar, y los usuarios no pueden ser su propio supervisor. Vuelva a seleccionar los usuarios, excluyendo al supervisor.',
    'user_exists'               => '¡El usuario ya existe!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'El usuario no existe o no tiene permiso para verlos.',
    'user_login_required'       => 'El campo usuario es obligatorio',
    'user_has_no_assets_assigned' => 'No hay activos asignados al usuario.',
    'user_password_required'    => 'La contraseña es obligatoria.',
    'insufficient_permissions'  => 'Permisos insuficientes.',
    'user_deleted_warning'      => 'Este usuario ha sido eliminado. Tendrá que restaurar este usuario para editarlo o para asignarle nuevos activos.',
    'ldap_not_configured'        => 'La integración con LDAP no ha sido configurada para esta instalación.',
    'password_resets_sent'      => 'Los usuarios seleccionados que están activados y tienen una dirección de correo electrónico válida han sido enviados un enlace de restablecimiento de contraseña.',
    'password_reset_sent'       => 'Un enlace para restablecer la contraseña ha sido enviado a :email!',
    'user_has_no_email'         => 'Este usuario no tiene una dirección de correo electrónico en su perfil.',
    'log_record_not_found'        => 'No se pudo encontrar un registro de eventos que coincida con este usuario.',


    'success' => array(
        'create'    => 'El usuario fue creado correctamente.',
        'update'    => 'Usuario actualizado exitosamente.',
        'update_bulk'    => '¡Usuarios actualizados con éxito!',
        'delete'    => 'El usuario se ha eliminado correctamente.',
        'ban'       => 'El usuario fue baneado con éxito.',
        'unban'     => 'El usuario fue desbloqueado correctamente.',
        'suspend'   => 'El usuario fue suspendido correctamente.',
        'unsuspend' => 'El usuario no fue suspendido correctamente.',
        'restored'  => 'Usuario restaurado correctamente.',
        'import'    => 'Usuarios importados con éxito.',
    ),

    'error' => array(
        'create' => 'Hubo un problema al crear el usuario. Por favor, inténtelo de nuevo.',
        'update' => 'Hubo un problema al actualizar el usuario. Por favor, inténtelo de nuevo.',
        'delete' => 'Hubo un problema al eliminar el usuario. Por favor, inténtelo de nuevo.',
        'delete_has_assets' => 'Este usuario tiene elementos asignados y no se pudo eliminar.',
        'delete_has_assets_var' => 'Este usuario todavía tiene un activo asignado. Por favor ingréselo primero.|Este usuario todavía tiene :count activos asignados. Por favor ingréselos primero.',
        'delete_has_licenses_var' => 'Este usuario todavía tiene una licencia asignada. Por favor ingrésela primero.|Este usuario todavía tiene :count licencias asignadas. Por favor ingréselas primero.',
        'delete_has_accessories_var' => 'Este usuario todavía tiene un accesorio asignado. Por favor ingréselo primero.|Este usuario todavía tiene :count accesorios asignados. Por favor ingréselos primero.',
        'delete_has_locations_var' => 'Este usuario todavía supervisa una ubicación. Por favor primero seleccione otro supervisor.|Este usuario todavía supervisa :count ubicaciones. Por favor primero seleccione otro supervisor.',
        'delete_has_users_var' => 'Este usuario todavía supervisa a otro usuario. Por favor primero seleccione otro supervisor para ese usuario.|Este usuario todavía supervisa :count usuarios. Por favor primero seleccione otro supervisor para ellos.',
        'unsuspend' => 'Hubo un problema marcando como no suspendido al usuario. Por favor, inténtelo de nuevo.',
        'import'    => 'Hubo un problema importando los usuarios. Por favor, inténtelo de nuevo.',
        'asset_already_accepted' => 'Este activo ya ha sido aceptado.',
        'accept_or_decline' => 'Debe aceptar o rechazar este activo.',
        'cannot_delete_yourself' => 'Nos sentiríamos muy mal si usted se eliminara, por favor reconsidérelo.',
        'incorrect_user_accepted' => 'El elemento que ha intentado aceptar no fue asignado a usted.',
        'ldap_could_not_connect' => 'No se pudo conectar al servidor LDAP. Por favor, compruebe la configuración del servidor LDAP en el archivo de configuración LDAP. <br>Error del servidor LDAP:',
        'ldap_could_not_bind' => 'No se ha podido vincular al servidor LDAP. Por favor verifique la configuración de su servidor LDAP en su archivo de configuración.<br> Error del servidor LDAP: ',
        'ldap_could_not_search' => 'No se pudo buscar en el servidor LDAP. Por favor, compruebe la configuración del servidor LDAP en el archivo de configuración LDAP. <br>Error del servidor LDAP:',
        'ldap_could_not_get_entries' => 'No se han podido obtener entradas del servidor LDAP. Por favor, compruebe la configuración del servidor LDAP en el archivo de configuración LDAP. <br>Error del servidor LDAP:',
        'password_ldap' => 'La contraseña para esta cuenta es administrada por LDAP / Active Directory. Póngase en contacto con su departamento de TI para cambiar su contraseña. ',
        'multi_company_items_assigned' => 'Este usuario tiene elementos asignados que pertenecen a una empresa diferente. Por favor, ingréselos o edite su empresa.'
    ),

    'deletefile' => array(
        'error'   => 'El archivo no fue borrado. Por favor, inténtelo de nuevo.',
        'success' => 'Archivo eliminado correctamente.',
    ),

    'upload' => array(
        'error'   => 'Archivo(s) no cargados. Por favor, inténtelo de nuevo.',
        'success' => 'Archivo(s) cargados correctamente.',
        'nofiles' => 'No seleccionó ningún archivo para cargar',
        'invalidfiles' => 'Uno o más de sus archivos son demasiado grandes o son de un tipo de archivo que no está permitido. Los tipos de archivo permitidos son png, gif, jpg, doc, docx, pdf y txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Este usuario no tiene ningún correo electrónico.',
        'success' => 'El usuario ha sido notificado sobre su inventario actual.'
    )
);