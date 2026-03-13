<?php

return array(

    'does_not_exist' => 'A licença não existe ou não tem permissão para visualizá-la.',
    'user_does_not_exist' => 'O usuário não existe ou você não tem permissão para visualizá-lo.',
    'asset_does_not_exist' 	=> 'O artigo que está a tentar associar com esta licença não existe.',
    'owner_doesnt_match_asset' => 'O proprietário do artigo que está a tentar associar com esta licença não é pessoa selecionada na dropdown.',
    'assoc_users'	 => 'Esta licença está correntemente alocada a um utilizador e não pode ser removida. Por favor devolva a licença e de seguida tente remover novamente. ',
    'select_asset_or_person' => 'Você deve selecionar um recurso ou um usuário, mas não ambos.',
    'not_found' => 'Licença não encontrada',
    'seats_available' => ':seat_count lugares disponíveis',


    'create' => array(
        'error'   => 'Licença não foi criada, por favor tente novamente.',
        'success' => 'Licença criada com sucesso.'
    ),

    'deletefile' => array(
        'error'   => 'Ficheiro não removido. Por favor, tente novamente.',
        'success' => 'Ficheiro removido com sucesso.',
    ),

    'upload' => array(
        'error'   => 'Ficheiro(s) não submetidos. Por favor, tente novamente.',
        'success' => 'Ficheiro(s) submetidos com sucesso.',
        'nofiles' => 'Não selecionou nenhum ficheiro para submissão, ou o ficheiro que pretende submeter é demasiado grande',
        'invalidfiles' => 'Um ou mais ficheiros excedem o tamanho ou são do tipo de ficheiro não é permitido. Os tipos permitidos são png, gif, jpg, doc, docx, pdf, txt, zip, rar, and rtf.',
    ),

    'update' => array(
        'error'   => 'Licença não foi atualizada, por favor tente novamente',
        'success' => 'Licença atualizada com sucesso.'
    ),

    'delete' => array(
        'confirm'   => 'Tem a certeza que pretende remover esta licença?',
        'error'   => 'Ocorreu um problema ao remover esta licença. Por favor, tente novamente.',
        'success' => 'A licença foi removida com sucesso.'
    ),

    'checkout' => array(
        'error'   => 'Ocorreu um problema ao atribuir esta licença. Por favor, tente novamente.',
        'success' => 'A licença foi alocada com sucesso',
        'not_enough_seats' => 'Não há assentos de licença suficientes disponíveis para o pagamento',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Ocorreu um problema ao devolver esta licença. Por favor, tente novamente.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'A licença foi devolvida com sucesso'
    ),

);
