<?php

return [

    'undeployable' => 'Os seguintes conteúdos não podem ser implantados e foram removidos do checkout: :asset_tags',
    'does_not_exist' => 'O ativo não existe.',
    'does_not_exist_var' => 'Ativo com a etiqueta :asset_tag não encontrado.',
    'no_tag' => 'Nenhuma etiqueta de ativo fornecida.',
    'does_not_exist_or_not_requestable' => 'Esse ativo não existe ou não pode ser solicitado.',
    'assoc_users' => 'Este ativo está no momento associado com pelo menos um usuário e não pode ser deletado. Por favor, atualize seu ativo para que não referencie mais este usuário e tente novamente. ',
    'warning_audit_date_mismatch' => 'A próxima data de auditoria deste ativo (:next_audit_date) é anterior à última data de auditoria (:last_audit_date). Por favor, atualize a próxima data de auditoria.',
    'labels_generated' => 'Marcadores foram gerados com sucesso.',
    'error_generating_labels' => 'Erro ao gerar marcadores.',
    'no_assets_selected' => 'Nenhum ativo selecionado.',

    'create' => [
        'error' => 'O ativo não foi criado, tente novamente. :(',
        'success' => 'Ativo criado com sucesso. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
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
        'error' => 'O ativo não foi atualizado, tente novamente',
        'success' => 'Ativo atualizado com sucesso.',
        'encrypted_warning' => 'Os ativos atualizados com sucesso, mas campos personalizados criptografados não se devem às permissões',
        'nothing_updated' => 'Nenhum campo foi selecionado, então nada foi atualizado.',
        'no_assets_selected' => 'Nenhum ativo foi selecionado, portanto, nada foi atualizado.',
        'assets_do_not_exist_or_are_invalid' => 'Os arquivos selecionados não podem ser atualizados.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'O ativo não foi restaurado, tente novamente',
        'success' => 'Ativo restaurado com sucesso.',
        'bulk_success' => 'Ativo restaurado com sucesso.',
        'nothing_updated' => 'Nenhum ativo foi selecionado, então nada foi restaurado.',
    ],

    'audit' => [
        'error' => 'Auditoria de ativo malsucedida: :error ',
        'success' => 'Auditoria de equipamentos logada com sucesso.',
    ],

    'deletefile' => [
        'error' => 'O arquivo não foi excluído. Tente novamente.',
        'success' => 'Arquivo excluído com sucesso.',
    ],

    'upload' => [
        'error' => 'O(s) arquivo(s) não foi/foram carregado(s). Tente novamente.',
        'success' => 'Arquivo(s) carregado(s) com sucesso.',
        'nofiles' => 'Você não selecionou arquivos para carregar, ou o arquivo que você esta tentando carrega é muito grande',
        'invalidfiles' => 'Um ou mais de seus arquivos é muito grande ou está em um tipo de arquivo não permitido. Os tipos permitidos são png, gif, jpg, doc, docx, pdf, e txt.',
    ],

    'import' => [
        'import_button' => 'Processar Importação',
        'error' => 'Alguns itens não foram importados corretamente.',
        'errorDetail' => 'Os seguintes itens não foram importados devido a erros.',
        'success' => 'O seu arquivo foi importado',
        'file_delete_success' => 'O arquivo foi excluído com sucesso',
        'file_delete_error' => 'Não foi possível excluir o arquivo',
        'file_missing' => 'O arquivo selecionado está faltando',
        'file_already_deleted' => 'O arquivo selecionado já foi excluído',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Um ou mais atributos na linha do cabeçalho contém caracteres UTF-8 malformados',
        'content_row_has_malformed_characters' => 'Um ou mais atributos na primeira linha de conteúdo contém caracteres UTF-8 malformados',
        'transliterate_failure' => 'Transliteração de :encoding para UTF-8 falhou devido a caracteres inválidos na entrada',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Excluir',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Criado',
            'updated' => 'Updated',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Importar',
        'required_fields_missing' => 'The following required fields are not mapped: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(missing asset tag)',
            'missing_asset_tag_message' => 'Row skipped: no asset tag provided.',
            'asset_not_found_message' => 'Asset with this tag does not exist. Import assets first, then re-run the history import.',
            'target_not_matched_message' => 'No :target_type matched ":name". For users, toggle the match-by options in step 1 or create the user first. For locations, make sure the CSV location name matches an existing location exactly.',
            'invalid_target_type_message' => 'Target Type ":value" is not recognized. Use "user" or "location", or leave the column blank to default to user.',
        ],
        'wizard' => [
            'step_type' => 'Choose type',
            'step_map' => 'Map fields',
            'step_preview' => 'Prévia',
            'back' => 'Voltar',
            'next' => 'Próxima',
            'preview_button' => 'Prévia',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Tem certeza de que deseja excluir este ativo?',
        'error' => 'Houve um problema ao excluir o ativo. Tente novamente.',
        'assigned_to_error' => '{1}Etiqueta do ativo :asset_tag está atualmente em uso. Efetue a devolução deste dispositivo antes de excluí-lo | [2,*] Etiquetas dos ativos :asset_tag estão atualmente em uso. Efetue a devolução destes dispositivos antes de excluí-los',
        'nothing_updated' => 'Nenhum ativo foi selecionado, então nada foi deletado.',
        'success' => 'O ativo foi excluído com sucesso.',
    ],

    'checkout' => [
        'error' => 'Ativo não foi registrado, favor tentar novamente',
        'success' => 'Ativo registrado com sucesso.',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
        'not_available' => 'Esse recurso não está disponível para checkout!',
        'no_assets_selected' => 'Você deve selecionar pelo menos um recurso da lista',
    ],

    'multi-checkout' => [
        'error' => 'O ativo não foi registrado, por favor tente novamente. | Os ativos não foram registrados, por favor tente novamente',
        'success' => 'Ativo registrado com sucesso. | Ativos registrados com sucesso.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Você deve selecionar pelo menos um recurso da lista',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Você deve selecionar pelo menos um recurso da lista',
    ],

    'checkin' => [
        'error' => 'Ativo não foi retornado, favor tentar novamente',
        'success' => 'Ativo retornado com sucesso.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
        'already_checked_in' => 'Este ativo já foi devolvido.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'A solicitação não foi bem-sucedida. Por favor, tente novamente.',
        'success' => 'Solicitação enviada com sucesso.',
        'canceled' => 'Requisição cancelada com sucesso.',
        'cancel' => 'Cancelar solicitação deste item',
        'duplicate' => 'You already have an active request for this item.',
        'no_active' => 'You have no active request to cancel for this item.',
        'insufficient_stock' => 'Not enough on hand to fulfill this request. Replenish first.',
        'confirm_cancel_by_admin' => "Cancel :user's request for :item?",
        'no_selection' => 'No rows were selected to fulfill.',
        'row_stale' => 'Request #:id is no longer pending and was skipped.',
        'row_qty_invalid' => 'Request #:id has an invalid quantity and was skipped.',
        'row_user_missing' => 'Request #:id has no valid target user and was skipped.',
        'row_asset_missing' => 'Request #:id has no valid target asset and was skipped.',
        'row_asset_not_requesters' => 'Request #:id skipped: selected asset does not belong to the requester.',
        'row_asset_not_available' => 'Request #:id skipped: selected asset is not an available unit of this model.',
        'row_asset_taken' => 'Request #:id skipped: selected asset was assigned to someone else before submit.',
        'row_company_mismatch' => 'Request #:id skipped: :user cannot receive items from this company.',
        'row_over_allocated' => 'Request #:id skipped: not enough on hand at the moment of submit.',
        'no_target_assets_for_user' => ':user has no assets to install into.',
        'no_available_units' => 'No available units of this model to hand out.',
        'bulk_summary' => 'Fulfilled :fulfilled of :total requests.',
        'bulk_fulfill_notification_intro' => 'The following will apply to each ticked request when fulfilled:',
    ],

];
