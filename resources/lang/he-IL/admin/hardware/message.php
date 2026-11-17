<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'הנכס אינו קיים.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' => 'לא סופק תג נכס.',
    'does_not_exist_or_not_requestable' => 'הנכס אינו קיים או לא זמין.',
    'assoc_users' => 'הנכס הזה מסומן כרגע למשתמש ולא ניתן למחוק אותו. בדוק תחילה את הנכס ולאחר מכן נסה למחוק שוב.',
    'warning_audit_date_mismatch' => 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated' => 'תוויות נוצרו בהצלחה.',
    'error_generating_labels' => 'שגיאה ביצירת תוויות.',
    'no_assets_selected' => 'לא נבחרו נכסים.',

    'create' => [
        'error' => 'הנכס לא נוצר, נסה שוב. You',
        'success' => 'הנכס נוצר בהצלחה. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'נכס עם תג :tag נוצר בהצלחה. <strong><a href=":link" style="color: white;">לחץ כאן לצפייה</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'המשתמש המוגדר לא ניתן לאיתור.',
            'asset' => 'הפריט המוקצה לא ניתן לאיתור.',
            'location' => 'המיקום המוגדר לא נמצא.',
        ],
    ],

    'update' => [
        'error' => 'הנכס לא עודכן, נסה שוב',
        'success' => 'הנכס עודכן בהצלחה.',
        'encrypted_warning' => 'הנכס עודכן בהצלחה, אבל השדות המותאמים-אישית שמוצפנים לא עודכנו בגלל מחסור בהרשאות',
        'nothing_updated' => 'לא נבחרו שדות, ולכן דבר לא עודכן.',
        'no_assets_selected' => 'לא נבחרו פריטים, לא בוצעו שינויים.',
        'assets_do_not_exist_or_are_invalid' => 'הפריטים הנבחרים לא יכולים להתעדכן.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'הנכס לא שוחזר, נסה שוב',
        'success' => 'הנכס שוחזר בהצלחה.',
        'bulk_success' => 'הנכס שוחזר בהצלחה.',
        'nothing_updated' => 'לא נבחרו פריטים, כלום לא שוחזר.',
    ],

    'audit' => [
        'error' => 'ביקורת פריט לא הצליחה: :error ',
        'success' => 'ביקורת נכסים נרשמה בהצלחה.',
    ],

    'deletefile' => [
        'error' => 'הקובץ לא נמחק. בבקשה נסה שוב.',
        'success' => 'הקובץ נמחק בהצלחה.',
    ],

    'upload' => [
        'error' => 'הקובץ לא הועלה. בבקשה נסה שוב.',
        'success' => 'הקבצים הועלו בהצלחה.',
        'nofiles' => 'לא בחרת קבצים להעלאה, או שהקובץ שאתה מנסה להעלות גדול מדי',
        'invalidfiles' => 'אחד או יותר מהקבצים שלך גדול מדי או שהוא סוג קובץ שאינו מותר. סוגי קבצים מותרים הם png, gif, jpg, doc, docx, pdf ו- txt.',
    ],

    'import' => [
        'import_button' => 'יבוא תהליך',
        'error' => 'פריטים מסוימים לא ייבאו כראוי.',
        'errorDetail' => 'הפריטים הבאים לא יובאו בגלל שגיאות.',
        'success' => 'הקובץ שלך יובא',
        'file_delete_success' => 'הקובץ שלך נמחק בהצלחה',
        'file_delete_error' => 'לא ניתן היה למחוק את הקובץ',
        'file_missing' => 'הקובץ הנבחר חסר',
        'file_already_deleted' => 'הקובץ הנבחר כבר נמחק',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'אחד או יותר נתונים בעמודות הכותרת מכילים תווי UTF-8 פגומים',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'לִמְחוֹק',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'נוצר',
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
        'import_label' => 'יְבוּא',
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
            'step_preview' => 'תצוגה מקדימה',
            'back' => 'חזור',
            'next' => 'הַבָּא',
            'preview_button' => 'תצוגה מקדימה',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'האם אתה בטוח שברצונך למחוק את הנכס הזה?',
        'error' => 'היתה בעיה במחיקת הנכס. בבקשה נסה שוב.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'לא נבחרו נכסים ולכן לא נמחק דבר.',
        'success' => 'הנכס נמחק בהצלחה.',
    ],

    'checkout' => [
        'error' => 'הנכס לא נבדק, נסה שוב',
        'success' => 'הנכס הוצא בהצלחה.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
        'not_available' => 'הנכס הזה אינו זמין לקופה!',
        'no_assets_selected' => 'עליך לבחור לפחות בנכס אחד מהרשימה',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'עליך לבחור לפחות בנכס אחד מהרשימה',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'עליך לבחור לפחות בנכס אחד מהרשימה',
    ],

    'checkin' => [
        'error' => 'הנכס לא נבדק, נסה שוב',
        'success' => 'הנכס נבדק בהצלחה.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'משתמש זה אינו חוקי. בבקשה נסה שוב.',
        'already_checked_in' => 'הנכס כבר נבדק.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'הבקשה לא הצליחה, נא לנסות שוב.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'הבקשה בוטלה בהצלחה.',
        'cancel' => 'בטל בקשה עבור פריט זה',
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
