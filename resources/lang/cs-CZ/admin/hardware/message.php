<?php

return [

    'undeployable' => 'Tyto položky nebylo možné přiřadit, proto byly odstraněny z výdeje: :asset_tags',
    'does_not_exist' => 'Majetek nenalezen.',
    'does_not_exist_var' => 'Majetek se štítkem :asset_tag nebyl nalezen.',
    'no_tag' => 'Nebyl zadán žádný štítek',
    'does_not_exist_or_not_requestable' => 'Tento majetek neexistuje nebo jej nelze vyskladnit.',
    'assoc_users' => 'Majetek je předán svému uživateli a nelze jej odstranit. Před odstraněním jej nejprve převezměte. ',
    'warning_audit_date_mismatch' => 'Příští datum auditu tohoto majetku (:next_audit_date) je před posledním datem auditu (:last_audit_date). Aktualizujte prosím následující datum auditu.',
    'labels_generated' => 'Popisky byly úspěšně vygenerovány.',
    'error_generating_labels' => 'Chyba při generování popisků.',
    'no_assets_selected' => 'Žadná zařízení vybrána.',

    'create' => [
        'error' => 'Majetek se nepodařilo vytvořit, zkuste to prosím znovu.',
        'success' => 'Majetek byl v pořádku vytvořen.',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Zařízení se štítkem :tag byl úspěšně vytvořen. <strong><a href=":link" style="color: white;">Klidni zde pro zobrazení</a></strong>.',
        'multi_success_linked' => 'Zařízení se štítkem :links bylo úspěšně vytvořeno.|:count zařízení bylo úspěšně vytvořeno. :links.
',
        'partial_failure' => 'Zařízení se nepodařilo vytvořit. Důvod: :failures|:count zařízení se nepodařilo vytvořit. Důvody: :failures',
        'target_not_found' => [
            'user' => 'Přidělený uživatel nebyl nalezen.',
            'asset' => 'Přidělené zařízení nebylo nalezeno.',
            'location' => 'Přiřazené umístění se nepodařilo najít.',
        ],
    ],

    'update' => [
        'error' => 'Majetek se nepodařilo upravit, zkuste to prosím znovu',
        'success' => 'Majetek úspěšně aktualizován.',
        'encrypted_warning' => 'Majetek byl úspěšně aktualizován, ale šifrovaná vlastní pole nebyla způsobena oprávněním',
        'nothing_updated' => 'Nebyla zvolena žádná pole, nic se tedy neupravilo.',
        'no_assets_selected' => 'Nebyl zvolen žádný majetek, nic se tedy neupravilo.',
        'assets_do_not_exist_or_are_invalid' => 'Vybrané položky nelze aktualizovat.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Majetek se nepodařilo obnovit, zkuste to prosím později',
        'success' => 'Majetek byl v pořádku obnoven.',
        'bulk_success' => 'Majetek byl v pořádku obnoven.',
        'nothing_updated' => 'Nevybrali jste žádné položky, nic tedy nebylo obnoveno.',
    ],

    'audit' => [
        'error' => 'Audit zařízení byl neúspěšný: :error',
        'success' => 'Audit aktiv byl úspěšně zaznamenáván.',
    ],

    'deletefile' => [
        'error' => 'Soubor se nesmazal, prosím zkuste to znovu.',
        'success' => 'Soubor byl úspěšně smazán.',
    ],

    'upload' => [
        'error' => 'Soubor(y) se nepodařilo nahrát, zkuste to prosím znovu.',
        'success' => 'Soubor(y) byly v pořádku nahrány.',
        'nofiles' => 'K nahrání jste nevybrali žádný, nebo příliš velký soubor',
        'invalidfiles' => 'Jeden nebo více označených souborů je příliš velkých nebo nejsou podporované. Povolenými příponami jsou png, gif, pdf a txt.',
    ],

    'import' => [
        'import_button' => 'Import procesu',
        'error' => 'Některé položky nebyly správně importovány.',
        'errorDetail' => 'Následující položky nebyly importovány kvůli chybám.',
        'success' => 'Váš soubor byl importován',
        'file_delete_success' => 'Váš soubor byl úspěšně odstraněn',
        'file_delete_error' => 'Soubor nelze odstranit',
        'file_missing' => 'Vybraný soubor chybí',
        'file_already_deleted' => 'Vybraný soubor již byl odstraněn',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Jeden nebo více sloupců obsahuje v záhlaví poškozené UTF-8 znaky',
        'content_row_has_malformed_characters' => 'Jedna nebo více hodnot v prvním řádku obsahu obsahuje poškozené UTF-8 znaky',
        'transliterate_failure' => 'Přepis z :encoding do UTF-8 selhal kvůli neplatným znakům ve vstupu.',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Smazat',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Vytvořeno',
            'updated' => 'Aktualizováno',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Import',
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
            'step_preview' => 'Náhled',
            'back' => 'Zpět',
            'next' => 'Další',
            'preview_button' => 'Náhled',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Opravdu si přejete tento majetek odstranit?',
        'error' => 'Nepodařilo se nám tento majetek odstranit. Zkuste to prosím znovu.',
        'assigned_to_error' => '{1}Zařízení s označením :asset_tag je právě zapůjčeno. Před odstraněním je nutné ho vrátit.|[2,*]Zařízení s označeními :asset_tag jsou právě zapůjčena. Před odstraněním je nutné je vrátit.',
        'nothing_updated' => 'Žádný majetek nebyl vybrán, takže nic nebylo odstraněno.',
        'success' => 'Majetek byl úspěšně smazán.',
    ],

    'checkout' => [
        'error' => 'Majetek nebyl předán, zkuste to prosím znovu',
        'success' => 'Majetek byl v pořádku předán.',
        'user_does_not_exist' => 'Tento uživatel je neplatný. Zkuste to prosím znovu.',
        'not_available' => 'Tento majetek není k dispozici pro výdej!',
        'no_assets_selected' => 'Je třeba vybrat ze seznamu alespoň jeden majetek',
    ],

    'multi-checkout' => [
        'error' => 'Zařízení nebylo zapůjčeno, zkuste to prosím znovu|Zařízení nebyla zapůjčena, zkuste to prosím znovu

',
        'success' => 'Zařízení bylo úspěšně zapůjčeno.|Zařízení byla úspěšně zapůjčena.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Je třeba vybrat ze seznamu alespoň jeden majetek',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Je třeba vybrat ze seznamu alespoň jeden majetek',
    ],

    'checkin' => [
        'error' => 'Majetek nebyl převzat. Zkuste to prosím znovu',
        'success' => 'Majetek byl v pořádku převzat.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Tento uživatel je neplatný. Zkuste to prosím znovu.',
        'already_checked_in' => 'Tento majetek je již předaný.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Požadavek nebyl úspěšný, zkuste to prosím znovu.',
        'success' => 'Žádost byla úspěšně odeslána.',
        'canceled' => 'Žádost byla úspěšně zrušena.',
        'cancel' => 'Zrušit tuto žádost o položku',
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
