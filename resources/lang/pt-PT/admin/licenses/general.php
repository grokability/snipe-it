<?php

return array(
    'about_licenses_title'      => 'Sobre as Licenças',
    'about_licenses'            => 'As licenças são usadas para controlar o software.  Eles têm um número especificado de lugares disponíveis para atribuir',
    'checkin'  					=> 'Devolver instalação da Licença',
    'checkout_history'  		=> 'Alocar histórico',
    'checkout'  				=> 'Alocar instalação da Licença',
    'edit'  					=> 'Editar Licença',
    'filetype_info'				=> 'Os tipos de ficheiro permitidos são png, gif, jpg, jpeg, doc, docx, pdf, txt, zip e rar.',
    'clone'  					=> 'Clonar licença',
    'history_for'  				=> 'Histórico para ',
    'in_out'  					=> 'Entrada/Saída',
    'info'  					=> 'Informação de Licença',
    'license_seats'  			=> 'Instalações da Licença',
    'seat'  					=> 'Instalação',
    'seat_count'  				=> 'Seat :count',
    'seats'  					=> 'Instalações',
    'software_licenses'  		=> 'Licenças de Software',
    'user'  					=> 'Utilizador',
    'view'  					=> 'Ver Licença',
    'delete_disabled'           => 'Esta licença ainda não pode ser excluída porque alguns lugares ainda estão reservados.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Receber todos os lugares',
                'modal'             => 'This action will checkin one seat. | This action will checkin all :checkedout_seats_count seats for this license.',
                'enabled_tooltip'   => 'Entrega de TODOS os lugares para esta licença de utilizadores e ativos',
                'disabled_tooltip'  => 'Isto está desativado porque não há lugares recebidos no momento',
                'disabled_tooltip_reassignable'  => 'Isso está desativado porque a licença não é reatribuível',
                'success'           => 'Licença entregue com sucesso! Todas as licenças foram entregues com sucesso!',
                'log_msg'           => 'Checked in via bulk license checkin in license GUI',
            ],

            'checkout_all'              => [
                'button'                => 'Entrega de todos os lugares',
                'modal'                 => 'Esta ação entregará um lugar para o primeiro usuário disponível. Esta ação entregará todos os :available_seats_count lugares para os primeiros utilizadores disponíveis. Um utilizador é considerado disponível para este lugar se ele ainda não tiver essa licença reservada para ele, e a propriedade de Licença Atribuir Automaticamente está ativada na sua conta de utilizador.',
                'enabled_tooltip'   => 'Entrega de TODOS os lugares (ou quantos estiverem disponíveis) para TODOS os utilizadores',
                'disabled_tooltip'  => 'Isto está desativado porque não há lugares disponíveis no momento',
                'success'           => 'Licença entregue com sucesso! | :count licenças foram entregues com sucesso!',
                'error_no_seats'    => 'Não há mais lugares para esta licença.',
                'warn_not_enough_seats'    => ':count utilizadores foram atribuídos a esta licença, mas ficamos sem lugares de licença disponíveis.',
                'warn_no_avail_users'    => 'Nada a fazer. Não há utilizadores sem essa licença atribuída.',
                'log_msg'           => 'Entrega feita através da entrega em massa da licença',


            ],
    ],

    'below_threshold' => 'Existem apenas :remaining_count lugares para esta licença com uma quantidade mínima de :min_amt. Você pode querer considerar a compra de mais lugares.',
    'below_threshold_short' => 'Este item está abaixo da quantidade mínima necessária.',
);
