<?php

return array(
    'about_licenses_title'      => 'Sobre Licenças',
    'about_licenses'            => 'Licenças são utilizadas para controlar o uso dos softwares. Ela possuem uma quantidade específica que podem ser alocadas para os usuários',
    'checkin'  					=> 'Retorna Licença Compartilhada',
    'checkout_history'  		=> 'Histórico de Registros',
    'checkout'  				=> 'Registra Licença Compartilhada',
    'edit'  					=> 'Editar Licença',
    'filetype_info'				=> 'Tipos permitidos são png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, e rar.',
    'clone'  					=> 'Clonar Licença',
    'history_for'  				=> 'Histórico para ',
    'in_out'  					=> 'Retorna/Registra',
    'info'  					=> 'Informações da Licença',
    'license_seats'  			=> 'Compartilhamentos de Licença',
    'seat'  					=> 'Licença Compartilhada',
    'seat_count'  				=> 'Alocação :count',
    'seats'  					=> 'Licenças Compartilhadas',
    'software_licenses'  		=> 'Licenças de Software',
    'user'  					=> 'Usuário',
    'view'  					=> 'Ver Licença',
    'delete_disabled'           => 'Esta licença ainda não pode ser excluída porque algumas vagas ainda estão reservadas.',
    'bulk'                      =>
        [
            'checkin_all'           => [
                'button'            => 'Checkin todas as vagas',
                'modal'             => 'Esta ação devolverá uma alocação. | Esta ação devolverá todos os :checkedout_seats_count alocações para esta licença.',
                'enabled_tooltip'   => 'Check-in de TODOS as vagas para esta licença de usuários e ativos',
                'disabled_tooltip'  => 'Isto está desativado porque não há vagas desbloqueadas no momento',
                'disabled_tooltip_reassignable'  => 'Isto está desativado porque a licença não é transferível',
                'success'           => 'Licença desbloqueada com sucesso! | Todas as licenças foram verificadas com sucesso!',
                'log_msg'           => 'Devolvido via devolução em massa de licenças na interface gráfica de licença (GUI)',
            ],

            'checkout_all'              => [
                'button'                => 'Checkout de todas as vagas',
                'modal'                 => 'Esta ação verificará um lugar para o primeiro usuário disponível. | Esta ação verificará todos os :available_seats_count lugares para os primeiros usuários disponíveis. Um usuário é considerado disponível para esta vaga se ele ainda não tiver essa licença reservada para ele, e a propriedade de Autoatribuição de Licenças está ativada na sua conta de usuário.',
                'enabled_tooltip'   => 'Checkout TODAS as vagas (ou quantos estiverem disponíveis) para TODOS os usuários',
                'disabled_tooltip'  => 'Isto está desativado porque não há vagas desbloqueadas no momento',
                'success'           => 'Licença devolvida com sucesso! | :count licenças foram desbloqueadas com sucesso!',
                'error_no_seats'    => 'Não há mais vagas para esta licença.',
                'warn_not_enough_seats'    => ':count usuários foram atribuídos a esta licença, mas ficamos sem vagas de licença disponíveis.',
                'warn_no_avail_users'    => 'Nada a ser feito. Não há usuários que ainda não tenham essa licença atribuída a eles.',
                'log_msg'           => 'Disponibilizado via disponibilização em massa de licença na GUI',


            ],
    ],

    'below_threshold' => 'Existem apenas :remaining_count lugares para esta licença com uma quantidade mínima de :min_amt. Você pode querer considerar a compra de mais lugares.',
    'below_threshold_short' => 'Este item está abaixo da quantidade mínima necessária.',
);
