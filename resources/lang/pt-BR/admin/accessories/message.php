<?php

return array(

    'does_not_exist' => 'Este acessório [:id] não existe.',
    'not_found' => 'Esse acessório não foi encontrado.',
    'assoc_users'	 => 'Este acessório tem atualmente :count itens alocado para os usuários. Por favor, verifique em acessórios e e tente novamente. ',

    'create' => array(
        'error'   => 'Acessório não criado, Por favor tente novamente.',
        'success' => 'Acessório criado com sucesso.'
    ),

    'update' => array(
        'error'   => 'Acessório não atualizado, Por favor tente novamente',
        'success' => 'Acessório atualizado com sucesso.'
    ),

    'delete' => array(
        'confirm'   => 'Tem certeza de que deseja excluir este acessório?',
        'error'   => 'Ocorreu um problema ao remover o acessório. Por favor, tente novamente.',
        'success' => 'O acessório foi excluído com sucesso.'
    ),

     'checkout' => array(
        'error'   		=> 'O acessório não foi alocado, por favor tente novamente',
        'success' 		=> 'Acessório alocado com sucesso.',
        'unavailable'   => 'Acessório não está disponível para saída. Verifique a quantidade disponível',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
         'checkout_qty' => array(
            'lte'  => 'Atualmente há apenas um acessório disponível deste tipo, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente. Existem :number_currently_remaining total accessoris disponíveis, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'O acessório não foi devolvido, por favor, tente novamente',
        'success' 		=> 'Acessório devolvido com sucesso.',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.'
    )


);
