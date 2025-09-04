<?php

return array(

    'accepted'                  => 'Este aceitou este ativo com sucesso.',
    'declined'                  => 'Você recusou com sucesso esse ativo.',
    'bulk_manager_warn'	        => 'Os usuários foram atualizados com êxito, no entanto seu Gerenciador de entrada não foi salvo porque o gerente selecionado estava também na lista de usuários a ser editado e usuários podem não ser seu próprio gerente. Por favor, selecione os usuários novamente, excluindo o gerente.',
    'user_exists'               => 'O usuário já existe!',
    'user_not_found'            => 'O usuário não existe ou você não tem permissão para visualizá-lo.',
    'user_login_required'       => 'O campo de login é requerido',
    'user_has_no_assets_assigned' => 'Não há ativos atualmente atribuídos ao usuário.',
    'user_password_required'    => 'A senha é requerida.',
    'insufficient_permissions'  => 'Permissões Insuficientes.',
    'user_deleted_warning'      => 'Este usuário foi deletado. Você terá que restaurar este usuário para editá-los ou atribui-lós novos bens.',
    'ldap_not_configured'        => 'Integração LDAP não foi configurada para esta instalação.',
    'password_resets_sent'      => 'Os usuários selecionados que são ativados e têm um endereço de e-mail válido receberam um link de redefinição de senha.',
    'password_reset_sent'       => 'Um link de redefinição de senha foi enviado para :email!',
    'user_has_no_email'         => 'Esse usuário não tem um endereço de e-mail no seu perfil.',
    'log_record_not_found'        => 'Não foi possível encontrar um histórico de registro correspondente para este usuário.',


    'success' => array(
        'create'    => 'O usuário foi criado com sucesso.',
        'update'    => 'O usuário foi atualizado com sucesso.',
        'update_bulk'    => 'Usuários atualizados com sucesso!',
        'delete'    => 'O usuário foi excluído com sucesso.',
        'ban'       => 'O usuário foi banido com sucesso.',
        'unban'     => 'O usuário foi desbanido com sucesso.',
        'suspend'   => 'O usuário foi suspenso com sucesso.',
        'unsuspend' => 'O usuário foi removido da suspensão com sucesso.',
        'restored'  => 'O usuário foi restaurado com sucesso.',
        'import'    => 'Usuários importados com sucesso.',
    ),

    'error' => array(
        'create' => 'Houve um problema ao criar o usuário. Tente novamente.',
        'update' => 'Houve um problema ao atualizar o usuário. Tente novamente.',
        'delete' => 'Houve um problema ao excluir o usuário. Tente novamente.',
        'delete_has_assets' => 'Este usuário tem itens atribuídos e não pôde ser excluído.',
        'delete_has_assets_var' => 'Este usuário ainda tem um ativo atribuído. Por favor, faça a devolução primeiro. | Este usuário ainda tem :count ativos atribuídos. Por favor, faça a devolução dos ativos primeiro.',
        'delete_has_licenses_var' => 'Este usuário ainda tem uma licença atribuída. Por favor, faça a devolução primeiro. | Este usuário ainda tem :count licenças atribuídas. Por favor, faça a devolução delas primeiro.',
        'delete_has_accessories_var' => 'Este usuário ainda tem um acessório atribuído. Por favor, faça a devolução primeiro. | Este usuário ainda tem :count acessórios atribuídos. Por favor, faça a devolução dos ativos primeiro.',
        'delete_has_locations_var' => 'Este usuário ainda gerencia um local. Por favor, selecione outro gerente primeiro. | Este usuário ainda gerencia :count locais. Por favor, selecione outro gerente primeiro.',
        'delete_has_users_var' => 'Este usuário ainda gerencia outro usuário. Por favor, selecione outro gerente para esse usuário primeiro. | Este usuário ainda gerencia :count usuários. Por favor, selecione outro gerente para eles primeiro.',
        'unsuspend' => 'Houve um problema ao remover a suspensão do usuário. Tente novamente.',
        'import'    => 'Houve um problema ao importar usuários. Tente novamente.',
        'asset_already_accepted' => 'Este ativo já foi aceito.',
        'accept_or_decline' => 'Você precisa aceita ou rejeitar esse ativo.',
        'cannot_delete_yourself' => 'Nós nos sentiríamos muito mal se você se deletasse, por favor, reconsidere.',
        'incorrect_user_accepted' => 'O ativo que tentou aceitar não foi solicitado por você.',
        'ldap_could_not_connect' => 'Não foi possível conectar ao servidor LDAP. Por favor verifique as configurações do servidor LDAP no arquivo de configurações.<br>Erro do Servidor LDAP:',
        'ldap_could_not_bind' => 'Não foi possível se ligar ao servidor LDAP. Por favor verifique as configurações do servidor LDAP no arquivo de configurações.<br>Erro do Servidor LDAP: ',
        'ldap_could_not_search' => 'Não foi possível procurar o servidor LDAP. Por favor verifique as configurações do servidor LDAP no arquivo de configurações.<br>Erro do Servidor LDAP:',
        'ldap_could_not_get_entries' => 'Não foi possível obter informações do servidor LDAP. Por favor verifique as configurações do servidor LDAP no arquivo de configurações.<br>Erro do Servidor LDAP:',
        'password_ldap' => 'A senha desta conta é gerenciada pelo LDAP / Active Directory. Entre em contato com seu departamento de TI para alterar sua senha. ',
        'multi_company_items_assigned' => 'Este usuário tem itens atribuídos que pertencem a uma empresa diferente. Por favor, verifique-os ou edite a empresa deles.'
    ),

    'deletefile' => array(
        'error'   => 'Arquivo não deletado. Por favor tente novamente.',
        'success' => 'Arquivo foi deletado com sucesso.',
    ),

    'upload' => array(
        'error'   => 'Arquivo(s) não carregados. Por favor tente novamente.',
        'success' => 'Arquivo(s) carregados com sucesso.',
        'nofiles' => 'Você não selecionou nenhum arquivo para carregar',
        'invalidfiles' => 'Um ou mais de seus arquivos são muito grande ou o tipo de arquivo não é permitido. Tipos permitidos são png, gif, jpg, doc, docx, pdf e txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Este usuário não tem e-mail definido.',
        'success' => 'O usuário foi notificado sobre seu inventário atual.'
    )
);