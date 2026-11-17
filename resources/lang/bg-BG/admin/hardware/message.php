<?php

return [

    'undeployable' => 'Следните активи не могат да бъдат предоставени и бяха премахнати от изписването: :asset_tags',
    'does_not_exist' => 'Активът не съществува.',
    'does_not_exist_var' => 'Активът с етике :asset_tag не е намерен.',
    'no_tag' => 'Не е предоставен етикет на актив.',
    'does_not_exist_or_not_requestable' => 'Актива не съществува или не може да бъде предоставян.',
    'assoc_users' => 'Активът е изписан на потребител и не може да бъде изтрит. Моля впишете го обратно и след това опитайте да го изтриете отново.',
    'warning_audit_date_mismatch' => 'Следващата дата на одит на този актив (:next_audit_date) е преди последната дата на одит (:last_audit_date). Моля, актуализирайте следващата дата на одита.',
    'labels_generated' => 'Етиката е успешно генериран.',
    'error_generating_labels' => 'Грешка при генериране на етикети.',
    'no_assets_selected' => 'Няма избрани активи.',

    'create' => [
        'error' => 'Активът не беше създаден. Моля опитайте отново.',
        'success' => 'Активът създаден успешно.',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Артикул с етикет :tag беше създаден успешно. <strong><a href=":link" style="color: white;">Щракнете тук за да го видите</a></strong>.',
        'multi_success_linked' => 'Актив с етикет :links беше създаден успешно.|:count активи бяха създадено успешно. :links.',
        'partial_failure' => 'Грешка при създаване на актив. Съобщението за грешка е: :failures|:count актива не бяха създадени. Съобщението за грешка е: :failures',
        'target_not_found' => [
            'user' => 'Назначеният потребител не можа да бъде намерен.',
            'asset' => 'Назначения артикул не може да бъде намерен.',
            'location' => 'Назначената локация не може да бъде намерена.',
        ],
    ],

    'update' => [
        'error' => 'Активът не беше обновен. Моля опитайте отново.',
        'success' => 'Активът обновен успешно.',
        'encrypted_warning' => 'Активът беше актуализиран успешно, но шифрованите персонализирани полета не бяха актуализирани поради разрешения',
        'nothing_updated' => 'Няма избрани полета, съответно нищо не беше обновено.',
        'no_assets_selected' => 'Няма избрани активи, така че нищо не бе обновено.',
        'assets_do_not_exist_or_are_invalid' => 'Избраните активи не могат да се обновят.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Активът не беше възстановен. Моля опитайте отново.',
        'success' => 'Активът възстановен успешно.',
        'bulk_success' => 'Активът възстановен успешно.',
        'nothing_updated' => 'Няма избрани активи, така че нищо не бе възстановено.',
    ],

    'audit' => [
        'error' => 'Одитът на активите е неуспешен: :error ',
        'success' => 'Активният одит бе успешно регистриран.',
    ],

    'deletefile' => [
        'error' => 'Файлът не беше изтрит. Моля опитайте отново.',
        'success' => 'Файлът изтрит успешно.',
    ],

    'upload' => [
        'error' => 'Качването неуспешно. Моля опитайте отново.',
        'success' => 'Качването успешно.',
        'nofiles' => 'Не сте избрали файлове за качване или са твърде големи.',
        'invalidfiles' => 'Един или повече файлове са твърде големи или с непозволен тип. Разрешените файлови типове за качване са png, gif, jpg, doc, docx, pdf и txt.',
    ],

    'import' => [
        'import_button' => 'Импортирай',
        'error' => 'Някои елементи не бяха въведени правилно.',
        'errorDetail' => 'Следните елементи не бяха въведени поради грешки.',
        'success' => 'Вашият файл беше въведен.',
        'file_delete_success' => 'Вашият файл беше изтрит успешно.',
        'file_delete_error' => 'Файлът не е в състояние да бъде изтрит',
        'file_missing' => 'Избраният файл липсва',
        'file_already_deleted' => 'Избрания файл беше вече изтрит',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Един или повече атрибути на заглавния ред съдържат неправилни UTF-8 символи',
        'content_row_has_malformed_characters' => 'Един или повече атрибути на заглавния ред съдържат неправилни UTF-8 символи',
        'transliterate_failure' => 'Транслитерацията от :encoding към UTF-8 беше неуспешна, поради невалидни символи',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Изтриване',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Създаден',
            'updated' => 'Обновено',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Зареждане',
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
            'step_preview' => 'Преглед',
            'back' => 'Назад',
            'next' => 'Следващ',
            'preview_button' => 'Преглед',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Сигурни ли сте, че желаете изтриване на актива?',
        'error' => 'Проблем при изтриване на актива. Моля опитайте отново.',
        'assigned_to_error' => '{1}Актива: :asset_tag е изписан. Впишете го обратно преди изтриване.|[2,*] Активите :asset_tag са изписани. Впишете ги обратно преди изтриване.',
        'nothing_updated' => 'Няма избрани активи, така че нищо не бе изтрито.',
        'success' => 'Активът е изтрит успешно.',
    ],

    'checkout' => [
        'error' => 'Активът не беше изписан. Моля опитайте отново.',
        'success' => 'Активът изписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'not_available' => 'Този актив не е наличен за отписване!',
        'no_assets_selected' => 'Трябва да изберете поне един елемент към списъка',
    ],

    'multi-checkout' => [
        'error' => 'Актива не беше изписан, моля опитайте отново|Активите не бяха изписани, моля опитайте отново',
        'success' => 'Актива е изписан успешно.|Активите са изписани успешно.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Трябва да изберете поне един елемент към списъка',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Трябва да изберете поне един елемент към списъка',
    ],

    'checkin' => [
        'error' => 'Активът не беше вписан. Моля опитайте отново.',
        'success' => 'Активът вписан успешно.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'already_checked_in' => 'Активът е вече вписан.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Опитат беше неуспешен, моля опитайте отново.',
        'success' => 'Заявката е успешно подадена.',
        'canceled' => 'Заявката е отменена.',
        'cancel' => 'Отмени тази заявка за артикул',
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
