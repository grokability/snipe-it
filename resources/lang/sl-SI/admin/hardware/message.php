<?php

return [

    'undeployable' => 'Naslednjih sredstev ni mogoče namestiti in so bila odstranjena iz blagajne: :asset_tags',
    'does_not_exist' => 'Sredstvo ne obstaja.',
    'does_not_exist_var' => 'Sredstvo z oznako :oznaka_sredstva ni bilo najdeno.',
    'no_tag' => 'Oznaka sredstva ni podana.',
    'does_not_exist_or_not_requestable' => 'To sredstvo ne obstaja ali ga ni mogoče zahtevati.',
    'assoc_users' => 'To sredstvo je trenutno izdano uporabniku in ga ni mogoče izbrisati. Najprej preverite sredstvo in poskusite znova izbrisati. ',
    'warning_audit_date_mismatch' => 'Naslednji datum revizije tega sredstva (:next_audit_date) je pred zadnjim datumom revizije (:last_audit_date). Prosimo, posodobite naslednji datum revizije.',
    'labels_generated' => 'Oznake so bile uspešno ustvarjene.',
    'error_generating_labels' => 'Napaka pri ustvarjanju oznak.',
    'no_assets_selected' => 'Ni izbranih sredstev.',

    'create' => [
        'error' => 'Sredstvo ni bilo ustvarjeno, poskusite znova. :(',
        'success' => 'Sredstvo je uspešno ustvarjeno. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Sredstvo z oznako :tag je bilo uspešno ustvarjeno. <strong><a href=":link" style="color: white;">Kliknite tukaj za ogled</a></strong>.',
        'multi_success_linked' => 'Sredstvo z oznako :links je bilo uspešno ustvarjeno.|:count sredstev je bilo uspešno ustvarjenih. :links.',
        'partial_failure' => 'Sredstva ni bilo mogoče ustvariti. Razlog: :failures|:count sredstev ni bilo mogoče ustvariti. Razlogi: :failures',
        'target_not_found' => [
            'user' => 'Dodeljenega uporabnika ni bilo mogoče najti.',
            'asset' => 'Dodeljenega sredstva ni bilo mogoče najti.',
            'location' => 'Dodeljene lokacije ni bilo mogoče najti.',
        ],
    ],

    'update' => [
        'error' => 'Sredstvo ni bilo posodobljeno, poskusite znova',
        'success' => 'Sredstvo je uspešno posodobljeno.',
        'encrypted_warning' => 'Sredstvo je bilo uspešno posodobljeno, vendar šifrirana polja po meri niso bila zaradi dovoljenj',
        'nothing_updated' => 'Nobeno polje ni bilo izbrana, zato nebo nič posodobljeno.',
        'no_assets_selected' => 'Nobena sredstva niso bila izbrana, zato ni bilo nič izbrisanih.',
        'assets_do_not_exist_or_are_invalid' => 'Izbrana sredstva ni mogoče posodobiti.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Sredstvo ni bilo obnovljeno, poskusite znova',
        'success' => 'Sredstvo je bilo uspešno obnovljeno.',
        'bulk_success' => 'Sredstvo je bilo uspešno obnovljeno.',
        'nothing_updated' => 'Nobeno sredstvo ni bilo izbran, zato nebo nič obnovljeno.',
    ],

    'audit' => [
        'error' => 'Revizija sredstev ni bila uspešna: :error ',
        'success' => 'Revizija sredstva je uspešno zabeležena.',
    ],

    'deletefile' => [
        'error' => 'Datoteka ni izbrisana. Prosim poskusite ponovno.',
        'success' => 'Datoteka je uspešno izbrisana.',
    ],

    'upload' => [
        'error' => 'Datoteka(e) niso naložene. Prosim poskusite ponovno.',
        'success' => 'Datoteka(e) so bile uspešno naložene.',
        'nofiles' => 'Niste izbrali nobenih datotek za nalaganje, ali je datoteka ki jo poskušate naložiti prevelika',
        'invalidfiles' => 'Ena ali več vaših datotek je prevelika ali pa je tip datoteke, ki ni dovoljen. Dovoljeni tipi datotek so png, gif, jpg, doc, docx, pdf in txt.',
    ],

    'import' => [
        'import_button' => 'Uvoz postopka',
        'error' => 'Nekateri elementi niso bili pravilno uvoženi.',
        'errorDetail' => 'Naslednji elementi niso bili uvoženi zaradi napak.',
        'success' => 'Vaša datoteka je bila uvožena',
        'file_delete_success' => 'Vaša datoteka je bila uspešno izbrisana',
        'file_delete_error' => 'Datoteke ni bilo mogoče izbrisati',
        'file_missing' => 'Izbrana datoteka manjka',
        'file_already_deleted' => 'Izbrana datoteka je bila že izbrisana',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Eden ali več atributov v vrstici glave vsebuje napačno oblikovane znake UTF-8',
        'content_row_has_malformed_characters' => 'Eden ali več atributov v prvi vrstici vsebine vsebuje napačno oblikovane znake UTF-8',
        'transliterate_failure' => 'Prečrkovanje iz :encoding v UTF-8 ni uspelo zaradi neveljavnih znakov v vnosu',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Izbriši',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Ustvarjeno',
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
        'import_label' => 'Uvozi',
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
            'step_preview' => 'Predogled',
            'back' => 'Nazaj',
            'next' => 'Naprej',
            'preview_button' => 'Predogled',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Ali ste prepričani, da želite izbrisati to sredstvo?',
        'error' => 'Prišlo je do težave z izbrisom sredstva. Prosim poskusite ponovno.',
        'assigned_to_error' => '{1}Oznaka sredstva: :asset_tag je trenutno rezervirana. Pred brisanjem preverite to napravo.|[2,*]Oznake sredstev: :asset_tag so trenutno rezervirane. Pred brisanjem preverite te naprave.',
        'nothing_updated' => 'Nobena sredstva niso bila izbrana, zato ni bilo nič izbrisanih.',
        'success' => 'Sredstvo je bilo uspešno izbrisano.',
    ],

    'checkout' => [
        'error' => 'Sredstvo ni bila izdano, poskusite znova',
        'success' => 'Sredstvo je bilo uspešno izdano.',
        'user_does_not_exist' => 'Ta uporabnik ni veljaven. Prosim poskusite ponovno.',
        'not_available' => 'To sredstvo ni na voljo za izdajo!',
        'no_assets_selected' => 'Na seznamu morate izbrati vsaj eno sredstev',
    ],

    'multi-checkout' => [
        'error' => 'Sredstvo ni bilo rezervirano, poskusite znova|Sredstva niso bila rezervirana, poskusite znova',
        'success' => 'Sredstvo uspešno rezervirano.|Sredstva uspešno rezervirana.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Na seznamu morate izbrati vsaj eno sredstev',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Na seznamu morate izbrati vsaj eno sredstev',
    ],

    'checkin' => [
        'error' => 'Sredstev ni bilo prevzeto, poskusite znova',
        'success' => 'Sredstev je bilo uspešno prevzeta.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Ta uporabnik je neveljaven. Prosim poskusite ponovno.',
        'already_checked_in' => 'Ta sredstev je že izdana.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Zahteva ni bila uspešna, poskusite znova.',
        'success' => 'Zahteva uspešno poslana.',
        'canceled' => 'Zahteva je bila uspešno preklicana.',
        'cancel' => 'Prekliči to zahtevo za predmet',
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
