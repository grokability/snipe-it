<?php

return [

    'update' => [
        'error'                 => 'Ocorreu um erro ao atualizar. ',
        'success'               => 'Configurações atualizadas com sucesso.',
    ],
    'backup' => [
        'delete_confirm'        => 'Tem a certeza que pretende eliminar o ficheiro de backup? Não poderá reverter a acção. ',
        'file_deleted'          => 'Ficheiro de backup eliminado com sucesso. ',
        'generated'             => 'Ficheiro de backup criado com sucesso.',
        'file_not_found'        => 'O ficheiro de backup não foi encontrado no servidor.',
        'restore_warning'       => 'Sim, restaurar. Eu reconheço que isso irá substituir quaisquer dados existentes atualmente na base de dados. Isto também irá desligar todos os utilizadores existentes (incluindo você).',
        'restore_confirm'       => 'Tem a certeza que deseja restaurar a sua base de dados a partir de :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Ocorreu um erro ao eliminar os dados. ',
        'validation_failed'     => 'A confirmação para limpar os dados correu mal. Digite a palavra "Apagar" na caixa de confirmação.',
        'success'               => 'Os dados foram apagados com sucesso.',
    ],
    'mail' => [
        'sending' => 'Enviar e-mail de teste...',
        'success' => 'E-mail enviado!',
        'error' => 'O e-mail não pode ser enviado.',
        'additional' => 'Nenhuma mensagem de erro adicional foi fornecida. Verifique as suas configurações de e-mail e o log do aplicativo.'
    ],
    'ldap' => [
        'testing' => 'Testando a conexão LDAP, ligação e pesquisa ...',
        '500' => '500 Erro de Servidor. Por favor, verifique os logs do servidor para mais informações.',
        'error' => 'Ocorreu um erro :(',
        'sync_success' => 'Uma amostra de 10 utilizadores retornaram do servidor LDAP com base nas suas configurações:',
        'testing_authentication' => 'Testando Autenticação LDAP...',
        'authentication_success' => 'Utilizador autenticado no LDAP com sucesso!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'A enviar mensagem :app de teste...',
        'success' => 'Sua integração com :webhook_name funciona!',
        'success_pt1' => 'Sucesso! Verifique o ',
        'success_pt2' => ' canal para a sua mensagem de teste, e certifique-se de clicar em SALVAR abaixo para guardar as suas configurações.',
        '500' => '500 Erro de Servidor.',
        'error' => 'Algo deu erro. :app respondeu com: :error_message',
        'error_redirect' => 'ERRO: 301/302 :endpoint retorna um redirecionamento. Por razões de segurança, não seguimos redirecionamentos. Por favor, use o ponto de extremidade atual.',
        'error_misc' => 'Algo deu erro. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
