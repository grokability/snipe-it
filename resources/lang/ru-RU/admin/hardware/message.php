<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'Актив не существует.',
    'does_not_exist_var' => 'Актив с тегом :asset_tag не найден.',
    'no_tag' => 'Тег актива не предоставлен.',
    'does_not_exist_or_not_requestable' => 'Этот актив не существует или не подлежит запросу.',
    'assoc_users' => 'Этот актив в настоящее время привязан к пользователю и не может быть удален. Пожалуйста сначала снимите привязку, и затем попробуйте удалить снова. ',
    'warning_audit_date_mismatch' => 'Дата следующего аудита этого актива (:next_audit_date) не может быть раньше последней даты аудита (:last_audit_date). Пожалуйста, обновите следующую дату аудита.',
    'labels_generated' => 'Этикетки успешно сгенерированы.',
    'error_generating_labels' => 'Ошибка при создании этикеток.',
    'no_assets_selected' => 'Активы не выбраны.',

    'create' => [
        'error' => 'Актив не был создан, пожалуйста попробуйте снова. :(',
        'success' => 'Актив успешно создан. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Актив с тегом :tag успешно создан. <strong><a href=":link" style="color: white;">Нажмите для просмотра</a></strong>.',
        'multi_success_linked' => 'Актив с номером :links успешно создан.|:count активов успешно созданы. :links.',
        'partial_failure' => 'Актив не может быть создан. Причина: :failures|:count активов не могут быть созданы. Причины: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error' => 'Актив не был изменен, пожалуйста попробуйте снова',
        'success' => 'Актив успешно изменен.',
        'encrypted_warning' => 'Актив обновлен успешно, но зашифрованные пользовательские поля не были из-за разрешений',
        'nothing_updated' => 'Поля не выбраны, нечего обновлять.',
        'no_assets_selected' => 'Никакие ресурсы не были выбраны, поэтому ничего не обновлялось.',
        'assets_do_not_exist_or_are_invalid' => 'Выбранные медиафайлы не могут быть обновлены.',
    ],

    'bulk_update' => [
        'success' => 'Актив успешно обновлен.|:count активов были успешно обновлены.',
        'partial' => ':success активы(ы) успешно обновлены, :failed faily. Смотрите результаты массива для деталей.',
        'error' => 'Активы не были обновлены. Подробнее см. в массиве результатов.',
    ],

    'restore' => [
        'error' => 'Актив не был восстановлен, повторите попытку',
        'success' => 'Актив успешно восстановлен.',
        'bulk_success' => 'Актив успешно восстановлен.',
        'nothing_updated' => 'Ни один из активов не выбран, поэтому ничего не восстановлено.',
    ],

    'audit' => [
        'error' => 'Аудит активов не удался: :error ',
        'success' => 'Аудит успешно выполнен.',
    ],

    'deletefile' => [
        'error' => 'Не удалось удалить файл. Повторите попытку.',
        'success' => 'Файл успешно удален.',
    ],

    'upload' => [
        'error' => 'Не удалось загрузить файл(ы). Повторите попытку.',
        'success' => 'Файл(ы) успешно загружены.',
        'nofiles' => 'Не выбрано ни одного файла для загрузки или файл, который вы пытаетесь загрузить, слишком большой',
        'invalidfiles' => 'Один или несколько ваших файлов слишком большого размера или имеют неподдерживаемый формат. Разрешены только следующие форматы файлов:  png, gif, jpg, doc, docx, pdf, txt.',
    ],

    'import' => [
        'import_button' => 'Процесс Импорта',
        'error' => 'Некоторые элементы не были импортированы корректно.',
        'errorDetail' => 'Следующие элементы не были импортированы из за ошибок.',
        'success' => 'Ваш файл был импортирован',
        'file_delete_success' => 'Ваш файл был успешно удален',
        'file_delete_error' => 'Невозможно удалить файл',
        'file_missing' => 'Выбранный файл отсутствует',
        'file_already_deleted' => 'Выбранный файл уже удален',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Один или несколько атрибутов в строке заголовка содержат неправильно сформированные символы UTF-8',
        'content_row_has_malformed_characters' => 'Один или несколько атрибутов в первой строке содержимого содержат неправильно сформированные символы UTF-8',
        'transliterate_failure' => 'Транслитерация из :encoding в UTF-8 не удалась из-за недопустимых символов во входных данных',
        'bulk_delete' => [
            'button' => 'Удалить выбранное (:count)',
            'confirm_title' => 'Удалить выбранные файлы импорта?',
            'confirm_body' => 'Вы собираетесь безвозвратно удалить :count файлов импорта. Это невозможно отменить.',
            'confirm_button' => 'Удалить',
            'success' => 'Файл импорта успешно удален.|:count файлов импорта успешно удалены.',
            'skipped' => ':count файла(ов) было пропущено, поскольку у вас нет прав на их удаление.',
            'select_all' => 'Выбрать все файлы на этой странице',
            'select_row' => 'Выберите :file для массового удаления',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Создано',
            'updated' => 'Обновлено',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Импорт',
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
            'step_preview' => 'Предпросмотр',
            'back' => 'Назад',
            'next' => 'Далее',
            'preview_button' => 'Предпросмотр',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Вы уверены что хотите удалить этот актив?',
        'error' => 'При удалении актива возникла проблема. Пожалуйста попробуйте снова.',
        'assigned_to_error' => '{1}Инвентарный номер: :asset_tag выдан в данный момент. Верните это устройство перед удалением. [2,*]Инвентарные номера: :asset_tag выданы в данный момент. Верните эти устройства перед удалением.',
        'nothing_updated' => 'Ни один из активов не выбран, поэтому ничего не удалено.',
        'success' => 'Актив был успешно удален.',
    ],

    'checkout' => [
        'error' => 'Актив не был привязан, пожалуйста попробуйте снова',
        'success' => 'Актив успешно привязан.',
        'user_does_not_exist' => 'Этот пользователь является недопустимым. Пожалуйста, попробуйте еще раз.',
        'not_available' => 'Данный актив недоступен к выдаче!',
        'no_assets_selected' => 'Вы должны выбрать хотя бы один актив из списка',
    ],

    'multi-checkout' => [
        'error' => 'Актив не был выдан, пожалуйста попробуйте снова|Активы не были выданы, пожалуйста попробуйте снова',
        'success' => 'Актив успешно выдан.|Активы успешно выданы.',
    ],

    'multi-checkin' => [
        'error' => 'Актив не был выдан, пожалуйста, попробуйте еще раз|Активы не были выданы, пожалуйста, попробуйте еще раз',
        'success' => 'Актив успешно выдан.|Активы успешно выданы.',
        'no_assets_selected' => 'Вы должны выбрать хотя бы один актив из списка',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Вы должны выбрать хотя бы один актив из списка',
    ],

    'checkin' => [
        'error' => 'Актив не был отвязан, пожалуйста попробуйте снова',
        'success' => 'Актив успешно отвязан.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Этот пользователь является недопустимым. Пожалуйста, попробуйте еще раз.',
        'already_checked_in' => 'Этот актив уже привязан.',
        'force_checkin_orphaned_success' => 'Некорректное назначение успешно удалено.',
        'force_checkin_not_orphaned' => 'Элемент не находится в состоянии некорректного назначения.',
        'force_checkin_error' => 'Не удалось очистить недопустимое назначение.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Запрос успешно отправлен.',
        'canceled' => 'Запрос успешно отменен.',
        'cancel' => 'Отменить запрос предмета',
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
