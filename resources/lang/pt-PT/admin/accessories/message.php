<?php

return array(

    'does_not_exist' => 'O acessório [:id] não existe.',
    'not_found' => 'Esse acessório não foi encontrado.',
    'assoc_users'	 => 'Esta acessório tem atualmente :count items alocados a utilizadores. Por favor, devolva-os e tente novamente. ',

    'create' => array(
        'error'   => 'Acessório não foi criado, por favor tente novamente.',
        'success' => 'Acessório criado com sucesso.'
    ),

    'update' => array(
        'error'   => 'Acessório não foi actualizado, por favor tente novamente',
        'success' => 'Acessório actualizado com sucesso.'
    ),

    'delete' => array(
        'confirm'   => 'Tem a certeza que pretende remover este acessório?',
        'error'   => 'Ocorreu um problema ao remover o acessório. Por favor, tente novamente.',
        'success' => 'O acessório foi removido com sucesso.'
    ),

     'checkout' => array(
        'error'   		=> 'O acessório não foi alocado. Por favor, tente novamente',
        'success' 		=> 'Acessório alocado com sucesso.',
        'unavailable'   => 'O acessório não está disponível para check-out. Verifique a quantidade disponível',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
         'checkout_qty' => array(
            'lte'  => 'Atualmente há apenas um acessório disponível deste tipo, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente. Existem :number_currently_remaining total accessoris disponíveis, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'O acessório não foi devolvido. Por favor, tente novamente',
        'success' 		=> 'Acessório devolvido com sucesso.',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.'
    )


);
