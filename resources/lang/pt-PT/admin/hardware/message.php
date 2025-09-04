<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Artigo não existente.',
    'does_not_exist_var' => 'Ativo com a tag :asset_tag não encontrado.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Esse artigo não existe ou não é solicitável.',
    'assoc_users'	 	 => 'Este artigo está correntemente alocado a um utilizador e não pode ser removido. Por favor devolva o artigo e de seguida tente remover novamente. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Não foi possível criar o Artigo. Por favor, tente novamente. :(',
        'success' 		=> 'Artigo criado com sucesso. :)',
        'success_linked' => 'O ativo com a tag :tag foi criado com sucesso. <strong><a href=":link" style="color: white;">clique aqui para ver</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Artigo não foi atualizado. Por favor, tente novamente',
        'success' 			=> 'Artigo atualizado com sucesso.',
        'encrypted_warning' => 'Os ativos atualizados com sucesso, mas campos personalizados criptografados não se devem às permissões',
        'nothing_updated'	=>  'Nenhum atributo foi selecionado, portanto nada foi atualizado.',
        'no_assets_selected'  =>  'Nenhum ativo foi selecionado, por isso nada foi atualizado.',
        'assets_do_not_exist_or_are_invalid' => 'Os arquivos selecionados não podem ser atualizados.',
    ],

    'restore' => [
        'error'   		=> 'O Artigo não foi restaurado, por favor tente novamente',
        'success' 		=> 'Artigo restaurado com sucesso.',
        'bulk_success' 		=> 'Artigo restaurado com sucesso.',
        'nothing_updated'   => 'Nenhum artigo foi selecionado, assim nada restaurado.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Auditoria de ativos logada com sucesso.',
    ],


    'deletefile' => [
        'error'   => 'Ficheiro não removido. Por favor, tente novamente.',
        'success' => 'Ficheiro removido com sucesso.',
    ],

    'upload' => [
        'error'   => 'Ficheiro(s) não submetidos. Por favor, tente novamente.',
        'success' => 'Ficheiro(s) submetidos com sucesso.',
        'nofiles' => 'Não selecionou nenhum ficheiro para submissão, ou o ficheiro que pretende submeter é demasiado grande',
        'invalidfiles' => 'Um ou mais ficheiros são demasiado grandes ou trata-se de um tipo de ficheiro não permitido. Os tipos de ficheiro permitidos são png, gif, jpg, jpeg, doc, docx, pdf e txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Alguns itens não foram importados corretamente.',
        'errorDetail'           => 'Os seguintes itens não foram importados devido a erros.',
        'success'               => 'O seu ficheiro foi importado',
        'file_delete_success'   => 'Ficheiro eliminado com sucesso',
        'file_delete_error'      => 'Não foi possível eliminar o ficheiro',
        'file_missing' => 'Ficheiro selecionado está a faltar',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Um ou mais atributos na linha do cabeçalho contém caracteres UTF-8 mal formados',
        'content_row_has_malformed_characters' => 'Um ou mais atributos na primeira linha de conteúdo contém caracteres UTF-8 mal formados',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Tem a certeza de que pretende eliminar este artigo?',
        'error'   		=> 'Ocorreu um problema ao remover o artigo. Por favor, tente novamente.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Nenhum recurso foi selecionado, então nada foi excluído.',
        'success' 		=> 'O artigo foi removido com sucesso.',
    ],

    'checkout' => [
        'error'   		=> 'Não foi possível alocar o artigo, por favor tente novamente',
        'success' 		=> 'Artigo alocado com sucesso.',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
        'not_available' => 'Esse recurso não está disponível para checkout!',
        'no_assets_selected' => 'Deve escolher pelo menos um artigo da lista',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Não foi possível devolver o artigo, por favor tente novamente',
        'success' 		=> 'Artigo devolvido com sucesso.',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
        'already_checked_in'  => 'Este artigo já foi devolvido.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancelar solicitação deste artigo',
    ],

];
