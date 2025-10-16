<?php

return array(

    'does_not_exist' => 'A licença não existe ou você não tem permissão para visualizá-la.',
    'user_does_not_exist' => 'O usuário não existe ou você não tem permissão para visualizá-lo.',
    'asset_does_not_exist' 	=> 'O ativo do qual você está tentando associar com esta licença não existe.',
    'owner_doesnt_match_asset' => 'O ativo que você está tentando associar a está licença é propriedade de alguma outra pessoa que não está selecionada na lista suspensa.',
    'assoc_users'	 => 'Esta licença está atualmente disponibilizada para um usuário e não pode ser excluído. Por favor, atualize seu ativo para que não referencie mais este usuário e, em seguida, tente apagar novamente. ',
    'select_asset_or_person' => 'Você deve selecionar um ativo ou um usuário, mas não ambos.',
    'not_found' => 'Licença não encontrada',
    'seats_available' => ':seat_count vagas disponíveis',


    'create' => array(
        'error'   => 'A licença não foi criada, tente novamente.',
        'success' => 'Licença criada com sucesso.'
    ),

    'deletefile' => array(
        'error'   => 'O arquivo não foi excluído. Tente novamente.',
        'success' => 'Arquivo excluído com sucesso.',
    ),

    'upload' => array(
        'error'   => 'O(s) arquivo(s) não foi/foram carregado(s). Tente novamente.',
        'success' => 'Arquivo(s) carregado(s) com sucesso.',
        'nofiles' => 'Você não selecionou arquivos para carregar, ou o arquivo que você esta tentando carregar é muito grande',
        'invalidfiles' => 'Um ou mais arquivos excedem o tamanho ou são do tipo de arquivo não permitido. Os tipos permitidos são png, gif, jpg, doc, docx, pdf, txt, zip, rar, and rtf.',
    ),

    'update' => array(
        'error'   => 'A licença não foi atualizada, tente novamente',
        'success' => 'Licença atualizada com sucesso.'
    ),

    'delete' => array(
        'confirm'   => 'Tem certeza de que deseja excluir esta licença?',
        'error'   => 'Houve um problema ao excluir esta licença. Tente novamente.',
        'success' => 'A licença foi excluída com sucesso.'
    ),

    'checkout' => array(
        'error'   => 'Houve um problema de registro na licença. Favor tentar novamente.',
        'success' => 'A licença foi registrada com sucesso',
        'not_enough_seats' => 'Não há vagas de licença suficientes disponíveis para o pagamento',
        'mismatch' => 'A alocação de licença fornecida não corresponde à licença',
        'unavailable' => 'Esta alocação não está disponível para empréstimo.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Houve um problema de registro na licença. Favor tentar novamente.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'A licença foi registrada com sucesso.'
    ),

);
