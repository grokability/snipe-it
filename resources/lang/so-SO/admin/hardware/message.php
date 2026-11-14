<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'Hantidu ma jirto.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Hantidaas ma jirto ama lama codsan karo.',
    'assoc_users' => 'Hantidan hadda waa la hubiyay isticmaale lamana tirtiri karo Fadlan marka hore hubi hantida, ka dibna isku day mar kale in aad tirtirto. ',
    'warning_audit_date_mismatch' => 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated' => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error' => 'Hantida lama abuurin, fadlan isku day mar kale. :(',
        'success' => 'Hantida loo sameeyay si guul leh :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Hanti leh sumad :tag si guul leh ayaa loo abuuray. <strong><a href=":link" style="color: white;">Riix halkan si aad u aragto</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error' => 'Hantida lama cusboonaysiin, fadlan isku day mar kale',
        'success' => 'Hantida si guul leh ayaa loo cusboonaysiiyay.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated' => 'Goobo lama dooran, markaa waxba lama cusboonaysiin.',
        'no_assets_selected' => 'Wax hanti ah lama dooran, markaa waxba lama cusboonaysiin.',
        'assets_do_not_exist_or_are_invalid' => 'Hantida la xushay lama cusboonaysiin karo.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Hantidii lama soo celin, fadlan isku day mar kale',
        'success' => 'Hantida si guul leh ayaa loo soo celiyay.',
        'bulk_success' => 'Hantida si guul leh ayaa loo soo celiyay.',
        'nothing_updated' => 'Wax hanti ah lama dooran, markaa waxba lama soo celin.',
    ],

    'audit' => [
        'error' => 'Asset audit unsuccessful: :error ',
        'success' => 'Hantidhawrka hantida ayaa si guul leh loo diiwaan geliyay.',
    ],

    'deletefile' => [
        'error' => 'Faylka lama tirtirin Fadlan isku day mar kale.',
        'success' => 'Faylka si guul leh waa la tirtiray.',
    ],

    'upload' => [
        'error' => 'Faylka lama soo rarin Fadlan isku day mar kale.',
        'success' => 'Faylka(yada) si guul leh loo soo raray.',
        'nofiles' => 'Ma aadan dooran wax fayl ah oo la soo geliyo, ama faylka aad isku dayeyso inaad geliyaan waa mid aad u weyn',
        'invalidfiles' => 'Mid ama in ka badan oo faylashaada ah aad bay u weyn yihiin ama waa nooc faylal ah oo aan la oggolayn. Noocyada faylalka la oggol yahay waa png, gif, jpg, doc, docx, pdf, iyo txt.',
    ],

    'import' => [
        'import_button' => 'Process Import',
        'error' => 'Alaabta qaar si sax ah uma soo dejin.',
        'errorDetail' => 'Alaabta soo socota looma soo dejin khaladaad dartood.',
        'success' => 'Faylkaaga waa la soo dejiyay',
        'file_delete_success' => 'Faylkaaga si guul leh ayaa loo tirtiray',
        'file_delete_error' => 'Faylka waa la tirtiri waayay',
        'file_missing' => 'Faylka la doortay waa maqan yahay',
        'file_already_deleted' => 'The file selected was already deleted',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Hal ama in ka badan oo sifooyin ah oo ku jira safka madaxa waxa ku jira xarfaha UTF-8 oo khaldan',
        'content_row_has_malformed_characters' => 'Hal ama in ka badan oo sifooyin ah safka koowaad ee nuxurka waxa ku jira xarfo UTF-8 oo khaldan',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Tirtir',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Abuuray',
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
        'import_label' => 'Soo dejinta',
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
            'back' => 'Dib u noqo',
            'next' => 'Xiga',
            'preview_button' => 'Preview',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Ma hubtaa inaad rabto inaad tirtirto hantidan?',
        'error' => 'Waxaa jirtay arrin la tirtiray hantida Fadlan isku day mar kale.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'Wax hanti ah lama dooran, markaa waxba lama tirtirin.',
        'success' => 'Hantida si guul leh ayaa loo tirtiray.',
    ],

    'checkout' => [
        'error' => 'Hantida lama hubin, fadlan isku day mar kale',
        'success' => 'Hantida si guul leh ayaa loo hubiyay.',
        'user_does_not_exist' => 'Isticmaalahaasi waa khalad Fadlan isku day mar kale.',
        'not_available' => 'Hantidaas looma hayo hubin!',
        'no_assets_selected' => 'Waa inaad liiska ka doorataa ugu yaraan hal hanti',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Waa inaad liiska ka doorataa ugu yaraan hal hanti',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Waa inaad liiska ka doorataa ugu yaraan hal hanti',
    ],

    'checkin' => [
        'error' => 'Hantida lama hubin, fadlan isku day mar kale',
        'success' => 'Hantida si guul leh ayaa loo hubiyay.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Isticmaalahaasi waa khalad Fadlan isku day mar kale.',
        'already_checked_in' => 'Hantidaas mar horeba waa la hubiyay.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'Request successfully canceled.',
        'cancel' => 'Jooji codsiga shaygan',
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
