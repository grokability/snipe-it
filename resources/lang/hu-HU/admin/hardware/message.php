<?php

return [

    'undeployable' => 'A következő eszközök nem telepíthetők, ezért eltávolításra kerültek a kölcsönzésből: :asset_tags',
    'does_not_exist' => 'Eszköz nem létezik.',
    'does_not_exist_var' => 'A :asset_tag jelzésű eszköz nem található.',
    'no_tag' => 'Nem lett megadva eszközcímke.',
    'does_not_exist_or_not_requestable' => 'Az eszköz nem létezik vagy nem igényelhető.',
    'assoc_users' => 'Ez az eszköz jelenleg ki van jelölve egy felhasználónak, és nem törölhető. Kérjük, először ellenőrizze az eszközt, majd próbálja meg újra törölni.',
    'warning_audit_date_mismatch' => 'Ennek az eszköznek a következő leltározási dátuma (:next_audit_date) korábbi, mint az utolsó leltározás dátuma (:last_audit_date). Kérjük, frissítse a következő leltározás dátumát.',
    'labels_generated' => 'A címkék sikeresen létre lettek hozva.',
    'error_generating_labels' => 'Hiba a címkék generálásakor.',
    'no_assets_selected' => 'Nincsenek kijelölt eszközök.',

    'create' => [
        'error' => 'Az eszköz nem jött létre, próbálkozzon újra. :(',
        'success' => 'Az eszköz sikeresen létrehozva. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Eszköz a :tag azonosítóval sikeresen létrehozva. <strong><a href=":link" style="color: white;">Kattintson ide a megtekintéshez</a></strong>.',
        'multi_success_linked' => 'A :links eszköz sikeresen létrehozva.|:count eszköz sikeresen létrehozva: :links.',
        'partial_failure' => 'Egy eszköz létrehozása nem sikerült. Oka: :failures|:count eszköz létrehozása nem sikerült. Okok: :failures',
        'target_not_found' => [
            'user' => 'A kijelölt felhasználó nem található.',
            'asset' => 'A kijelölt eszköz nem található.',
            'location' => 'A kijelölt helyszín nem található.',
        ],
    ],

    'update' => [
        'error' => 'Az eszköz nem frissült, próbálkozzon újra',
        'success' => 'Az eszköz sikeresen frissült.',
        'encrypted_warning' => 'Az eszköz sikeresen frissítve, de a titkosított egyéni mezők a jogosultságok miatt nem frissültek',
        'nothing_updated' => 'Nem választottak ki mezőket, így semmi sem frissült.',
        'no_assets_selected' => 'Egyetlen eszköz sem volt kiválasztva, így semmi sem frissült.',
        'assets_do_not_exist_or_are_invalid' => 'A kiválasztott eszközök nem frissíthetők.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'Egy eszköz sem frissült. Részletekért tekintse meg az eredményeket.',
    ],

    'restore' => [
        'error' => 'Az eszköz nem állt helyre, kérjük, próbálkozzon újra',
        'success' => 'Az eszköz sikeresen visszaállítva.',
        'bulk_success' => 'Az eszköz sikeresen visszaállítva.',
        'nothing_updated' => 'Nem voltak eszközök kiválasztva, így semmi sem lett visszállítva.',
    ],

    'audit' => [
        'error' => 'Az eszköz leltározása sikertelen: :error ',
        'success' => 'Az eszközellenőrzés sikeresen be van jelentkezve.',
    ],

    'deletefile' => [
        'error' => 'A fájl nem törölve. Kérlek próbáld újra.',
        'success' => 'A fájl sikeresen törölve.',
    ],

    'upload' => [
        'error' => 'Fel nem töltött fájl (ok). Kérlek próbáld újra.',
        'success' => 'Fájl (ok) sikeresen feltöltve.',
        'nofiles' => 'Nem választottál fel fájlokat a feltöltéshez, vagy a fájl, amelyet feltölteni próbálsz, túl nagy',
        'invalidfiles' => 'Egy vagy több fájl túl nagy vagy egy filetype, amely nem megengedett. Az engedélyezett fájltípusok png, gif, jpg, doc, docx, pdf és txt.',
    ],

    'import' => [
        'import_button' => 'Importálás feldolgozása',
        'error' => 'Egyes elemek nem importáltak helyesen.',
        'errorDetail' => 'Az alábbi elemeket nem importálták hiba miatt.',
        'success' => 'A fájlt importálta',
        'file_delete_success' => 'A fájlt sikeresen törölték',
        'file_delete_error' => 'A fájlt nem sikerült törölni',
        'file_missing' => 'A kijelölt fájl nem található',
        'file_already_deleted' => 'A kiválasztott fájl már törlésre került',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'A fejlécsorban egy vagy több attribútum hibás formájú UTF-8 karaktereket tartalmaz',
        'content_row_has_malformed_characters' => 'A tartalom első sorában egy vagy több attribútum hibás formájú UTF-8 karaktereket tartalmaz',
        'transliterate_failure' => 'A :encoding kódolásból UTF-8-ra való átírás sikertelen az érvénytelen karakterek miatt',
        'bulk_delete' => [
            'button' => 'Kiválasztottak törlése (:count)',
            'confirm_title' => 'Kiválasztott import fájlok törlése?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Törlés',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Minden fájl kijelölése az oldalon',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Létrehozva',
            'updated' => 'Frissítve',
            'skipped' => 'Duplikált, kihagyva',
            'errored' => 'Hiba történt',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Biztonsági mentés importálás előtt',
        'backup_complete' => 'Sikeres mentés',
        'import_label' => 'Importálás',
        'required_fields_missing' => 'The following required fields are not mapped: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(hiányzó eszközazonosító)',
            'missing_asset_tag_message' => 'Row skipped: no asset tag provided.',
            'asset_not_found_message' => 'Asset with this tag does not exist. Import assets first, then re-run the history import.',
            'target_not_matched_message' => 'No :target_type matched ":name". For users, toggle the match-by options in step 1 or create the user first. For locations, make sure the CSV location name matches an existing location exactly.',
            'invalid_target_type_message' => 'Target Type ":value" is not recognized. Use "user" or "location", or leave the column blank to default to user.',
        ],
        'wizard' => [
            'step_type' => 'Típus kiválasztása',
            'step_map' => 'Mezők megfeleltetése',
            'step_preview' => 'Előnézet',
            'back' => 'Vissza',
            'next' => 'Tovább',
            'preview_button' => 'Előnézet',
            'process' => 'Importálás indítása',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Biztos benne, hogy törli ezt az elemet?',
        'error' => 'Hiba történt az eszköz törlése közben. Kérlek próbáld újra.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'Egyetlen eszköz sem volt kiválasztva, így semmi sem lett törölve.',
        'success' => 'Az eszköz sikeresen törölve lett.',
    ],

    'checkout' => [
        'error' => 'Az eszköz nem lett kijelölve, próbáld újra',
        'success' => 'A készlet sikeresen ki lett állítva.',
        'user_does_not_exist' => 'Ez a felhasználó érvénytelen. Kérlek próbáld újra.',
        'not_available' => 'Ez az eszköz nem áll rendelkezésre pénztárnál!',
        'no_assets_selected' => 'Ki kell választania legalább egy elemet a listából',
    ],

    'multi-checkout' => [
        'error' => 'Az eszköz nem lett kivéve, kérjük, próbálja újra|Az eszközök nem lettek kivéve, kérjük, próbálja újra',
        'success' => 'Az eszköz sikeresen kivéve.|Az eszközök sikeresen kivéve.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Ki kell választania legalább egy elemet a listából',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Ki kell választania legalább egy elemet a listából',
    ],

    'checkin' => [
        'error' => 'Az eszköz nem lett visszavéve, próbálkozzon újra',
        'success' => 'Az Asset sikeresen ellenőrzött.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Ez a felhasználó érvénytelen. Kérlek próbáld újra.',
        'already_checked_in' => 'Ez az eszköz már be van jelölve.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'A kérés sikertelen volt, kérjük, próbálja újra.',
        'success' => 'A kérés sikeresen elküldve.',
        'canceled' => 'A kérés sikeresen törölve.',
        'cancel' => 'Eszközigénylés visszavonása',
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
