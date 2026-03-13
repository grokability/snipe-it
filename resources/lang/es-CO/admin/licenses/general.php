<?php

return array(
    'about_licenses_title'      => 'Acerca de licencias',
    'about_licenses'            => 'Las licencias se utilizan para hacer un seguimiento del software.  Tienen una cantidad determinada que puede ser asignada a individuos',
    'checkin'  					=> 'Ingresar licencia',
    'checkout_history'  		=> 'Historial de asignaciones',
    'checkout'  				=> 'Asignar licencia',
    'edit'  					=> 'Editar licencia',
    'filetype_info'				=> 'Los tipos de archivo permitidos son png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, y rar.',
    'clone'  					=> 'Clonar licencia',
    'history_for'  				=> 'Historial para ',
    'in_out'  					=> 'Entrada/Salida',
    'info'  					=> 'Información de licencia',
    'license_seats'  			=> 'Total de licencias',
    'seat'  					=> 'Licencia',
    'seat_count'  				=> 'Licencia :count',
    'seats'  					=> 'Total de licencias',
    'software_licenses'  		=> 'Licencias de software',
    'user'  					=> 'Usuario',
    'view'  					=> 'Ver licencia',
    'delete_disabled'           => 'Esta licencia no se puede eliminar porque todavía está asignada.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Ingresar todas las licencias',
                'modal'             => 'Esta acción ingresará una licencia. | Esta acción ingresará todas las :checkedout_seats_count licencias.',
                'enabled_tooltip'   => 'Recibir TODAS las licencias tanto de usuarios como de activos',
                'disabled_tooltip'  => 'Esto está deshabilitado porque no hay licencias asignadas actualmente',
                'disabled_tooltip_reassignable'  => 'Esto está desactivado porque la licencia no es reasignable',
                'success'           => '¡Licencia recibida correctamente! | ¡Todas las licencias fueron recibidas correctamente!',
                'log_msg'           => 'Ingresada mediante ingreso masivo en página de licencias',
            ],

            'checkout_all'              => [
                'button'                => 'Asignar todas las licencias',
                'modal'                 => 'Esta acción asignará una licencia para el primer usuario disponible. | Esta acción asignará todas las :available_seats_count licencias a los primeros usuarios disponibles. Se considera que un usuario está disponible si aún no tiene esta licencia asignada y la propiedad "Autoasignación de licencia" está habilitada en su cuenta de usuario.',
                'enabled_tooltip'   => 'Asignar TODAS las licencias (o tantas como estén disponibles) para TODOS los usuarios',
                'disabled_tooltip'  => 'Esto está deshabilitado porque actualmente no hay licencias disponibles',
                'success'           => '¡Licencia asignada exitosamente! | ¡Licencias :count asignadas exitosamente!',
                'error_no_seats'    => 'No quedan licencias disponibles.',
                'warn_not_enough_seats'    => 'Se asignaron :count usuarios a esta licencia, y se agotaron las licencias disponibles.',
                'warn_no_avail_users'    => 'Nada que hacer. No hay usuarios que no tengan esta licencia asignada.',
                'log_msg'           => 'Asignada vía asignación masiva en página de licencias',


            ],
    ],

    'below_threshold' => 'Solo quedan :remaining_count licencias y su cantidad mínima es de :min_amt. Puede que desee considerar la compra de más licencias.',
    'below_threshold_short' => 'Este artículo está por debajo de la cantidad mínima requerida.',
);
