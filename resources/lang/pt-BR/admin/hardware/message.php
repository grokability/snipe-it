<?php

return [

    'undeployable' 		 => 'Os seguintes conteúdos não podem ser implantados e foram removidos do checkout: :asset_tags',
    'does_not_exist' 	 => 'O ativo não existe.',
    'does_not_exist_var' => 'Ativo com a etiqueta :asset_tag não encontrado.',
    'no_tag' 	         => 'Nenhuma etiqueta de ativo fornecida.',
    'does_not_exist_or_not_requestable' => 'Esse ativo não existe ou não pode ser solicitado.',
    'assoc_users'	 	 => 'Este ativo está no momento associado com pelo menos um usuário e não pode ser deletado. Por favor, atualize seu ativo para que não referencie mais este usuário e tente novamente. ',
    'warning_audit_date_mismatch' 	=> 'A próxima data de auditoria deste ativo (:next_audit_date) é anterior à última data de auditoria (:last_audit_date). Por favor, atualize a próxima data de auditoria.',
    'labels_generated'   => 'Marcadores foram gerados com sucesso.',
    'error_generating_labels' => 'Erro ao gerar marcadores.',
    'no_assets_selected' => 'Nenhum ativo selecionado.',

    'create' => [
        'error'   		=> 'O ativo não foi criado, tente novamente. :(',
        'success' 		=> 'Ativo criado com sucesso. :)',
        'success_linked' => 'O ativo com a tag :tag foi criado com sucesso. <strong><a href=":link" style="color: white;">clique aqui para ver</a></strong>.',
        'multi_success_linked' => 'O ativo com a tag :links foi criado com sucesso: :count ativos foram criados com sucesso. :links.',
        'partial_failure' => 'Um ativo não pôde ser criado. Motivo: :failures | :count assets não puderam ser criados. Motivos: :failures',
        'target_not_found' => [
            'user' => 'O usuário atribuído não pôde ser encontrado.',
            'asset' => 'O ativo atribuído não pôde ser encontrado.',
            'location' => 'A localização atribuída não pôde ser encontrada.',
        ],
    ],

    'update' => [
        'error'   			=> 'O ativo não foi atualizado, tente novamente',
        'success' 			=> 'Ativo atualizado com sucesso.',
        'encrypted_warning' => 'Os ativos atualizados com sucesso, mas campos personalizados criptografados não se devem às permissões',
        'nothing_updated'	=>  'Nenhum campo foi selecionado, então nada foi atualizado.',
        'no_assets_selected'  =>  'Nenhum ativo foi selecionado, portanto, nada foi atualizado.',
        'assets_do_not_exist_or_are_invalid' => 'Os arquivos selecionados não podem ser atualizados.',
    ],

    'restore' => [
        'error'   		=> 'O ativo não foi restaurado, tente novamente',
        'success' 		=> 'Ativo restaurado com sucesso.',
        'bulk_success' 		=> 'Ativo restaurado com sucesso.',
        'nothing_updated'   => 'Nenhum ativo foi selecionado, então nada foi restaurado.', 
    ],

    'audit' => [
        'error'   		=> 'Auditoria de ativo malsucedida: :error ',
        'success' 		=> 'Auditoria de equipamentos logada com sucesso.',
    ],


    'deletefile' => [
        'error'   => 'O arquivo não foi excluído. Tente novamente.',
        'success' => 'Arquivo excluído com sucesso.',
    ],

    'upload' => [
        'error'   => 'O(s) arquivo(s) não foi/foram carregado(s). Tente novamente.',
        'success' => 'Arquivo(s) carregado(s) com sucesso.',
        'nofiles' => 'Você não selecionou arquivos para carregar, ou o arquivo que você esta tentando carrega é muito grande',
        'invalidfiles' => 'Um ou mais de seus arquivos é muito grande ou está em um tipo de arquivo não permitido. Os tipos permitidos são png, gif, jpg, doc, docx, pdf, e txt.',
    ],

    'import' => [
        'import_button'         => 'Processar Importação',
        'error'                 => 'Alguns itens não foram importados corretamente.',
        'errorDetail'           => 'Os seguintes itens não foram importados devido a erros.',
        'success'               => 'O seu arquivo foi importado',
        'file_delete_success'   => 'O arquivo foi excluído com sucesso',
        'file_delete_error'      => 'Não foi possível excluir o arquivo',
        'file_missing' => 'O arquivo selecionado está faltando',
        'file_already_deleted' => 'O arquivo selecionado já foi excluído',
        'header_row_has_malformed_characters' => 'Um ou mais atributos na linha do cabeçalho contém caracteres UTF-8 malformados',
        'content_row_has_malformed_characters' => 'Um ou mais atributos na primeira linha de conteúdo contém caracteres UTF-8 malformados',
        'transliterate_failure' => 'Transliteração de :encoding para UTF-8 falhou devido a caracteres inválidos na entrada'
    ],


    'delete' => [
        'confirm'   	=> 'Tem certeza de que deseja excluir este ativo?',
        'error'   		=> 'Houve um problema ao excluir o ativo. Tente novamente.',
        'assigned_to_error' => '{1}Etiqueta do ativo :asset_tag está atualmente em uso. Efetue a devolução deste dispositivo antes de excluí-lo | [2,*] Etiquetas dos ativos :asset_tag estão atualmente em uso. Efetue a devolução destes dispositivos antes de excluí-los',
        'nothing_updated'   => 'Nenhum ativo foi selecionado, então nada foi deletado.',
        'success' 		=> 'O ativo foi excluído com sucesso.',
    ],

    'checkout' => [
        'error'   		=> 'Ativo não foi registrado, favor tentar novamente',
        'success' 		=> 'Ativo registrado com sucesso.',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
        'not_available' => 'Esse recurso não está disponível para checkout!',
        'no_assets_selected' => 'Você deve selecionar pelo menos um recurso da lista',
    ],

    'multi-checkout' => [
        'error'   => 'O ativo não foi registrado, por favor tente novamente. | Os ativos não foram registrados, por favor tente novamente',
        'success' => 'Ativo registrado com sucesso. | Ativos registrados com sucesso.',
    ],

    'checkin' => [
        'error'   		=> 'Ativo não foi retornado, favor tentar novamente',
        'success' 		=> 'Ativo retornado com sucesso.',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
        'already_checked_in'  => 'Este ativo já foi devolvido.',

    ],

    'requests' => [
        'error'   		=> 'A solicitação não foi bem-sucedida. Por favor, tente novamente.',
        'success' 		=> 'Solicitação enviada com sucesso.',
        'canceled'      => 'Requisição cancelada com sucesso.',
        'cancel'        => 'Cancelar solicitação deste item',
    ],

];
