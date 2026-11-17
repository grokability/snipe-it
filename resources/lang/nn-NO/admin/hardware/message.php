<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'Eiendel eksisterer ikke.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Eiendelen eksisterer ikke eller kan ikke forespørres.',
    'assoc_users' => 'Denne eiendelen er merket som utsjekket til en bruker og kan ikke slettes. Vennligst sjekk inn eiendelen først, og forsøk sletting på nytt. ',
    'warning_audit_date_mismatch' => 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated' => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error' => 'Eiendelen ble ikke opprettet, prøv igjen :(',
        'success' => 'Eiendelen ble opprettet :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Eiendelen med taggen :tag ble opprettet. <strong><a href=":link" style="color: white;">Klikk her for å vise</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error' => 'Eiendelen ble ikke oppdatert, prøv igjen',
        'success' => 'Oppdatering av eiendel vellykket.',
        'encrypted_warning' => 'Eiendel er oppdatert, men kryptert tilpassede felter var ikke grunnet tillatelser',
        'nothing_updated' => 'Ingen felter er valgt, så ingenting ble endret.',
        'no_assets_selected' => 'Ingen felter er valgt, så ingenting ble endret.',
        'assets_do_not_exist_or_are_invalid' => 'Valgte eiendeler kan ikke oppdateres.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Eiendel ble ikke gjenopprettet. Prøv igjen',
        'success' => 'Vellykket gjenoppretting av eiendel.',
        'bulk_success' => 'Ressursen ble gjenopprettet.',
        'nothing_updated' => 'Inger ressurser ble valgt, så ingenting ble gjenoprettet.',
    ],

    'audit' => [
        'error' => 'Asset audit unsuccessful: :error ',
        'success' => 'Asset audit ble logget.',
    ],

    'deletefile' => [
        'error' => 'Fil ble ikke slettet. Prøv igjen.',
        'success' => 'Vellykket sletting av fil.',
    ],

    'upload' => [
        'error' => 'Fil(er) ble ikke lastet opp. Prøv igjen.',
        'success' => 'Vellykket opplasting av fil(er).',
        'nofiles' => 'Ingen fil er valgt til opplasting, eller filen er for stor',
        'invalidfiles' => 'En eller flere av filene dine er for store eller av en ikke tillatt filtype. Tillatte filtyper er png, gif, jpg, doc, docx, pdf og txt.',
    ],

    'import' => [
        'import_button' => 'Process Import',
        'error' => 'Noen elementer ble ikke importert riktig.',
        'errorDetail' => 'Følgende elementer ble ikke importert på grunn av feil.',
        'success' => 'Filen har blitt importert',
        'file_delete_success' => 'Filen har blitt slettet',
        'file_delete_error' => 'Filen kunne ikke bli slettet',
        'file_missing' => 'Valgt fil mangler (fant ikke filen)',
        'file_already_deleted' => 'The file selected was already deleted',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'En eller flere attributter i overskriftsraden inneholder feilformede UTF-8 tegn',
        'content_row_has_malformed_characters' => 'En eller flere attributter i første rad i inneholdet inneholder feilformet UTF-8 tegn',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Slett',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Opprettet',
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
        'import_label' => 'Importer',
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
            'step_preview' => 'Preview',
            'back' => 'Tilbake',
            'next' => 'Neste',
            'preview_button' => 'Preview',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Er du sikker på at du vil slette eiendelen?',
        'error' => 'Det oppstod et problem under sletting av eiendel. Vennligst prøv igjen.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'Ingen assets ble valgt, så ingenting ble slettet.',
        'success' => 'Vellykket sletting av eiendel.',
    ],

    'checkout' => [
        'error' => 'Eiendel ble ikke sjekket ut. Prøv igjen',
        'success' => 'Vellykket utsjekk av eiendel.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'not_available' => 'Den eiendelen er ikke tilgjengelig til å sjekkes ut!',
        'no_assets_selected' => 'Du må velge minst én enhet fra listen',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Du må velge minst én enhet fra listen',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Du må velge minst én enhet fra listen',
    ],

    'checkin' => [
        'error' => 'Eiendel ble ikke sjekket inn. Prøv igjen',
        'success' => 'Vellykket innsjekk av eiendel.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'already_checked_in' => 'Den eiendelen er allerede sjekket inn.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'Request successfully canceled.',
        'cancel' => 'Avbryt forespørsel om element',
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
