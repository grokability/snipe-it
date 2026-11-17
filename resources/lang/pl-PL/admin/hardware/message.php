<?php

return [

    'undeployable' => 'Następujące środki nie mogą zostać wydane i zostały ograniczone w tym zakresie: :asset_tags',
    'does_not_exist' => 'Środek nie istnieje',
    'does_not_exist_var' => 'Nie znaleziono środka o tagu :asset_tag',
    'no_tag' => 'Nie podano numeru środka.',
    'does_not_exist_or_not_requestable' => 'Środek nie istnieje albo nie można o niego wnioskować.',
    'assoc_users' => 'Ten środek jest przypisany do użytkownika i nie może być usunięty. Proszę sprawdzić przypisanie środków a następnie spróbować ponownie.',
    'warning_audit_date_mismatch' => 'Data następnego audytu (:next_audit_date) jest przed datą poprzedniego audytu (:last_audit_date). Zaktualizuj datę następnego audytu.',
    'labels_generated' => 'Etykiety zostały pomyślnie wygenerowane.',
    'error_generating_labels' => 'Błąd podczas generowania etykiet.',
    'no_assets_selected' => 'Nie wybrano żadnych środków.',

    'create' => [
        'error' => 'Środeknie został utworzony, proszę spróbować ponownie. :(',
        'success' => 'Nowy środek został utworzony.  :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Środek o numerze: :tag został utworzony pomyślnie. <strong><a href=":link" style="color: white;">Kliknij tutaj, aby go wyświetlić</a></strong>.',
        'multi_success_linked' => 'Środek o numerze: :link został utworzony pomyślnie.|:count środków zostało utworzonych pomyślnie. :links.',
        'partial_failure' => 'Nie można utworzyć środka. Powód: :failures|:count środków nie mogło zostać utworzonych. Powód: :failed',
        'target_not_found' => [
            'user' => 'Nie znaleziono przypisanego użytkownika.',
            'asset' => 'Nie znaleziono przypisanego środka.',
            'location' => 'Nie znaleziono przypisanej lokalizacji.',
        ],
    ],

    'update' => [
        'error' => 'Nie zaktualizowano środka, proszę spróbować ponownie',
        'success' => 'Aktualizacja poprawna.',
        'encrypted_warning' => 'Środek zaktualizowany pomyślnie, ale zaszyfrowane pola niestandardowe nie zostały zaktualizowane ze względu na brak uprawnień.',
        'nothing_updated' => 'Żadne pole nie zostało wybrane, więc nic nie zostało zmienione.',
        'no_assets_selected' => 'Żadne środki nie zostały wybrane, więc nic nie zostało zmienione.',
        'assets_do_not_exist_or_are_invalid' => 'Wybrane środki nie mogą zostać zaktualizowane.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Środek nie został przywrócony, spróbuj ponownie.',
        'success' => 'Środek został przywrócony.',
        'bulk_success' => 'Środek został pomyślnie przywrócony.',
        'nothing_updated' => 'Żadne środki nie zostały wybrane, więc nic nie zostało przywrócone. ',
    ],

    'audit' => [
        'error' => 'Audyt środka zakończony niepowodzeniem :error ',
        'success' => 'Audyt środka pomyślnie zarejestrowany.',
    ],

    'deletefile' => [
        'error' => 'Plik nie zostały usunięty. Spróbuj ponownie.',
        'success' => 'Plik został usunięty.',
    ],

    'upload' => [
        'error' => 'Plik(i) nie zostały wysłane. Spróbuj ponownie.',
        'success' => 'Plik(i) zostały wysłane.',
        'nofiles' => 'Nie wybrałeś żadnych plików do przesłania, albo plik, który próbujesz przekazać jest zbyt duży',
        'invalidfiles' => 'Jeden lub więcej z wybranych przez ciebie plików jest za duży lub jego typ jest niewłaściwy. Dopuszczalne typy plików: png, gif, jpg, doc, docx, pdf, oraz txt.',
    ],

    'import' => [
        'import_button' => 'Przetwórz import',
        'error' => 'Niektóre elementy nie zostały poprawnie zaimportowane.',
        'errorDetail' => 'Następujące elementy nie zostały zaimportowane z powodu błędów.',
        'success' => 'Twój plik został zaimportowany',
        'file_delete_success' => 'Twój plik został poprawnie usunięty',
        'file_delete_error' => 'Plik nie może zostać usunięty',
        'file_missing' => 'Brakuje wybranego pliku',
        'file_already_deleted' => 'Wybrany plik został już usunięty',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Jeden lub więcej atrybutów w wierszu nagłówka zawiera nieprawidłowe znaki UTF-8',
        'content_row_has_malformed_characters' => 'Jeden lub więcej atrybutów w pierwszym rzędzie zawartości zawiera nieprawidłowe znaki UTF-8',
        'transliterate_failure' => 'Transformacja z :encoding do UTF-8 zakończyła się niepowodzeniem z powodu nieprawidłowych znaków wejściowych',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Kasuj',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Utworzone',
            'updated' => 'Zaktualizowano',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Zaimportuj',
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
            'step_preview' => 'Podgląd',
            'back' => 'Powrót',
            'next' => 'Następny',
            'preview_button' => 'Podgląd',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Czy na pewno chcesz usunąć?',
        'error' => 'Nie można usunąć. Proszę spróbować ponownie.',
        'assigned_to_error' => '{1}Tag środka: :asset_tag jest obecnie wydany. Przyjmij ponownie ten sprzęt przed usunięciem.|[2,*]Tag środka: :asset_tag są obecnie wydane. Przyjmij ponownie te sprzętu przed usunięciem.',
        'nothing_updated' => 'Środki nie zostały wybrane, więc nic nie zostało usunięte.',
        'success' => 'Środek został usunięty.',
    ],

    'checkout' => [
        'error' => 'Nie można wydać środka. Spróbuj ponownie.',
        'success' => 'Środek przypisano pomyślnie.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Proszę spróbować ponownie.',
        'not_available' => 'Ten środek nie jest dostępny do wydania!',
        'no_assets_selected' => 'Musisz wybrać co najmniej jeden środek z listy',
    ],

    'multi-checkout' => [
        'error' => 'Środek nie został przypisany, spróbuj ponownie|Środki nie zostały przypisane, spróbuj ponownie',
        'success' => 'Środek wydany pomyślnie.|Środki wydane pomyślnie.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Musisz wybrać co najmniej jeden środek z listy',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Musisz wybrać co najmniej jeden środek z listy',
    ],

    'checkin' => [
        'error' => 'Środek nie został przyjęty, proszę spróbować ponownie',
        'success' => 'Pomyślnie przyjęto środek.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Nieprawidłowy użytkownik. Proszę spróbować ponownie.',
        'already_checked_in' => 'Środek jest już przyjęty.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Wniosek zakończył się niepowodzeniem, spróbuj ponownie.',
        'success' => 'Pomyślnie wysłano wniosek.',
        'canceled' => 'Pomyślnie anulowano wniosek.',
        'cancel' => 'Anuluj żądanie tego elementu',
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
