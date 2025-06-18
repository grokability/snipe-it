<?php

return array(

    'does_not_exist' => 'O local não existe.',
    'assoc_users'    => 'Este local não pode ser excluído no momento, pois é o local de registro de pelo menos um ativo ou usuário, possui ativos atribuídos a ele ou é o local principal de outro local. Atualize seus registros para que não façam mais referência a este local e tente novamente ',
    'assoc_assets'	 => 'Este local esta atualmente associado a pelo menos um ativo e não pode ser deletado. Por favor atualize seu ativo para não fazer mais referência a este local e tente novamente. ',
    'assoc_child_loc'	 => 'Este local é atualmente o principal de pelo menos local secundário e não pode ser deletado. Por favor atualize seus locais para não fazer mais referência a este local e tente novamente. ',
    'assigned_assets' => 'Ativos atribuídos',
    'current_location' => 'Localização Atual',
    'open_map' => 'Abrir :map_provider_icon Maps',


    'create' => array(
        'error'   => 'O local não foi criado, tente novamente.',
        'success' => 'Local criado com sucesso.'
    ),

    'update' => array(
        'error'   => 'O local não foi atualizado, tente novamente',
        'success' => 'Local atualizado com sucesso.'
    ),

    'restore' => array(
        'error'   => 'A localização não foi restaurada, por favor, tente novamente',
        'success' => 'Localização restaurada com sucesso.'
    ),

    'delete' => array(
        'confirm'   	=> 'Tem certeza de que quer excluir este local?',
        'error'   => 'Houve um problema ao excluir o local. Tente novamente.',
        'success' => 'O local foi excluído com sucesso.'
    )

);
