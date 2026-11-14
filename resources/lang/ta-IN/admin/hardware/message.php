<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'சொத்து இல்லை.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users' => 'இந்த சொத்து தற்போது ஒரு பயனர் வெளியே சோதிக்கப்பட்டது மற்றும் நீக்க முடியாது. முதலில் சொத்தை சரிபார்த்து, மீண்டும் நீக்கி முயற்சிக்கவும்.',
    'warning_audit_date_mismatch' => 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated' => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error' => 'சொத்து உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும். :(',
        'success' => 'சொத்து வெற்றிகரமாக உருவாக்கப்பட்டது. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Asset with tag :tag was created successfully. <strong><a href=":link" style="color: white;">Click here to view</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error' => 'சொத்து புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'சொத்து வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated' => 'எந்த துறைகளும் தேர்ந்தெடுக்கப்படவில்லை, அதனால் எதுவும் புதுப்பிக்கப்படவில்லை.',
        'no_assets_selected' => 'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'சொத்து மீட்டமைக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'சொத்து வெற்றிகரமாக மீட்டமைக்கப்பட்டது.',
        'bulk_success' => 'சொத்து வெற்றிகரமாக மீட்டமைக்கப்பட்டது.',
        'nothing_updated' => 'No assets were selected, so nothing was restored.',
    ],

    'audit' => [
        'error' => 'Asset audit unsuccessful: :error ',
        'success' => 'சொத்து தணிக்கை வெற்றிகரமாக உள்நுழைந்தது.',
    ],

    'deletefile' => [
        'error' => 'கோப்பு நீக்கப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு வெற்றிகரமாக நீக்கப்பட்டது.',
    ],

    'upload' => [
        'error' => 'கோப்பு (கள்) பதிவேற்றப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு (கள்) வெற்றிகரமாக பதிவேற்றப்பட்டது.',
        'nofiles' => 'பதிவேற்றுவதற்கான எந்தவொரு கோப்பையும் நீங்கள் தேர்ந்தெடுக்கவில்லை அல்லது நீங்கள் பதிவேற்ற முயற்சிக்கும் கோப்பு மிகப்பெரியது',
        'invalidfiles' => 'உங்கள் கோப்புகளில் ஒன்று அல்லது அதற்கு மேற்பட்டவை மிக அதிகமாக உள்ளது அல்லது அனுமதிக்கப்படாத கோப்பு வகை உள்ளது. அனுமதிக்கப்பட்ட கோப்புரிமைகள் png, gif, jpg, doc, docx, pdf மற்றும் txt ஆகியவை.',
    ],

    'import' => [
        'import_button' => 'Process Import',
        'error' => 'சில உருப்படிகளை சரியாக இறக்குமதி செய்யவில்லை.',
        'errorDetail' => 'பிழைகள் காரணமாக பின்வரும் உருப்படிகளை இறக்குமதி செய்யப்படவில்லை.',
        'success' => 'உங்கள் கோப்பு இறக்குமதி செய்யப்பட்டது',
        'file_delete_success' => 'உங்கள் கோப்பு வெற்றிகரமாக நீக்கப்பட்டது',
        'file_delete_error' => 'கோப்பை நீக்க முடியவில்லை',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'அழி',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'உருவாக்கப்பட்டது',
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
        'import_label' => 'இறக்குமதி',
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
            'back' => 'மீண்டும்',
            'next' => 'அடுத்த',
            'preview_button' => 'Preview',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'இந்த சொத்தை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error' => 'சொத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'சொத்துகள் எதுவும் தேர்ந்தெடுக்கப்படவில்லை, எனவே எதுவும் நீக்கப்படவில்லை.',
        'success' => 'சொத்து வெற்றிகரமாக நீக்கப்பட்டது.',
    ],

    'checkout' => [
        'error' => 'சொத்து சரிபார்க்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'சொத்து வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'not_available' => 'புதுப்பித்துக்காக அந்த சொத்து கிடைக்கவில்லை!',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'checkin' => [
        'error' => 'சொத்து சரிபார்க்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'சொத்து வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'already_checked_in' => 'அந்தச் சொத்து ஏற்கனவே சோதிக்கப்பட்டுள்ளது.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'Request successfully canceled.',
        'cancel' => 'Cancel this item request',
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
