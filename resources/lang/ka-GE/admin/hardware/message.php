<?php

return [

    'undeployable' => '<strong>გაფრთხილება: </strong> ეს ინვენტარი აღნიშნულია როგორც დაზიანებული. თუ სტატუსი შეიცვალა, გთხოვთ განაახლოთ ინვენტარის სტატუსი.',
    'does_not_exist' => 'ინვენტარი არ არსებობს.',
    'does_not_exist_var' => 'ინვენტარი ნომრით :asset_tag ვერ მოიძებნა.',
    'no_tag' => 'ინვენტარის ნომერი არ არის მითითებული.',
    'does_not_exist_or_not_requestable' => 'ინვენტარი არ არსებობს ან არ არის მოთხოვნადი.',
    'assoc_users' => 'ეს ინვენტარი ამჟამად მინიჭებულია მომხმარებელზე და ვერ წაიშლება. გთხოვთ, ჯერ დააბრუნეთ იგი და შემდეგ სცადეთ წაშლა.',
    'warning_audit_date_mismatch' => 'ამ ინვენტარის მომდევნო აუდიტის თარიღი (:next_audit_date) ადრეულია ბოლო აუდიტის თარიღზე (:last_audit_date). გთხოვთ, განაახლეთ მომდევნო აუდიტის თარიღი.',
    'labels_generated' => 'ეტიკეტები წარმატებით გენერირდა.',
    'error_generating_labels' => 'ეტიკეტების გენერირებისას მოხდა შეცდომა.',
    'no_assets_selected' => 'არცერთი ინვენტარი არ არის მონიშნული.',

    'create' => [
        'error' => 'ინვენტარი ვერ შეიქმნა, გთხოვთ, სცადეთ თავიდან. :(',
        'success' => 'ინვენტარი წარმატებით შეიქმნა. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'ინვენტარი ნომრით :tag წარმატებით შეიქმნა. <strong><a href=":link" style="color: white;">დასათვალიერებლად დააჭირეთ აქ</a></strong>.',
        'multi_success_linked' => 'ინვენტარი ნომრით :links წარმატებით შეიქმნა.|:count ინვენტარი წარმატებით შეიქმნა. :links.',
        'partial_failure' => 'ერთი ინვენტარის შექმნა ვერ მოხერხდა. მიზეზი: :failures|:count ინვენტარის შექმნა ვერ მოხერხდა. მიზეზები: :failures',
        'target_not_found' => [
            'user' => 'მინიჭებული მომხმარებელი ვერ მოიძებნა.',
            'asset' => 'მინიჭებული ინვენტარი ვერ მოიძებნა.',
            'location' => 'მინიჭებული ადგილმდებარეობა ვერ მოიძებნა.',
        ],
    ],

    'update' => [
        'error' => 'ინვენტარი ვერ განახლდა, გთხოვთ, სცადეთ თავიდან.',
        'success' => 'ინვენტარი წარმატებით განახლდა.',
        'encrypted_warning' => 'ინვენტარი წარმატებით განახლდა, თუმცა დაშიფრული პერსონალური ველები ვერ განახლდა გარკვეული უფლებების გამო.',
        'nothing_updated' => 'ველი არ შევსებულა, შესაბამისად არაფერი განახლდა.',
        'no_assets_selected' => 'ინვენტარი არ არის არჩეული, შესაბამისად არაფერი განახლდა.',
        'assets_do_not_exist_or_are_invalid' => 'არჩეული ინვენტარი არ არსებობს ან არასწორია და ვერ განახლდება.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'ინვენტარის აღდგენა ვერ ხერხდება, გთხოვთ, სცადეთ თავიდან.',
        'success' => 'ინვენტარის აღდგენა წარმატებით დასრულდა.',
        'bulk_success' => 'ინვენტარის აღდგენა წარმატებით დასრულდა.',
        'nothing_updated' => 'ინვენტარი არ არის არჩეული, შესაბამისად არაფერი აღდგა.',
    ],

    'audit' => [
        'error' => 'ინვენტარის აუდიტი ვერ განხორციელდა: :error',
        'success' => 'ინვენტარის აუდიტის ლოგირება წარმატებით განხორციელდა.',
    ],

    'deletefile' => [
        'error' => 'ფაილი ვერ წაიშალა. გთხოვთ, ხელახლა სცადეთ.',
        'success' => 'ფაილი წარმატებით წაიშალა.',
    ],

    'upload' => [
        'error' => 'ფაილი(ები) ვერ აიტვირთა. გთხოვთ, სცადეთ თავიდან.',
        'success' => 'ფაილი(ები) წარმატებით აიტვირთა.',
        'nofiles' => 'თქვენ არ მიუთითეთ ფაილები ასატვირთად, ან ფაილი რომელსაც ცდილობთ ატვირთოთ, ძალიან დიდია.',
        'invalidfiles' => 'ერთი ან რამდენიმე ფაილი ძალიან დიდია ან მისი ფაილის ტიპი არ არის დაუშვებელი. დაუშვებელია მხოლოდ შემდეგი ტიპები: png, gif, jpg, doc, docx, pdf, და txt.',
    ],

    'import' => [
        'import_button' => 'იმპორტის დამუშავება',
        'error' => 'ზოგიერთი ელემენტის იმპორტირება სწორად ვერ განხორციელდა.',
        'errorDetail' => 'შემდეგი ელემენტების იმპორტირება ვერ მოხდა შეცდომების გამო.',
        'success' => 'თქვენი ფაილის იმპორტი წარმატებით განხორციელდა.',
        'file_delete_success' => 'თქვენი ფაილი წარმატებით წაიშალა.',
        'file_delete_error' => 'ფაილის წაშლა ვერ მოხერხდა.',
        'file_missing' => 'არჩეული ფაილი არ არსებობს.',
        'file_already_deleted' => 'არჩეული ფაილი უკვე წაშლილია.',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'სათაური შეიცავს ერთ ან მეტ არასწორ UTF-8 სიმბოლოს.',
        'content_row_has_malformed_characters' => 'კონტენტი შეიცავს არასწორ UTF-8 სიმბოლოს.',
        'transliterate_failure' => 'ტრანსლიტერაცია :encoding-დან UTF-8-ში ვერ მოხერხდა არასწორი სიმბოლოების გამო.',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'წაშლა',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Created',
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
        'import_label' => 'იმპორტი',
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
            'step_preview' => 'გადახედვა',
            'back' => 'უკან დაბრუნება',
            'next' => 'შემდეგი',
            'preview_button' => 'გადახედვა',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'დარწმუნებული ხართ, რომ გსურთ ეს ინვენტარი წაშალოთ?',
        'error' => 'ინვენტარის წაშლისას მოხდა შეცდომა. გთხოვთ, სცადოთ ხელახლა.',
        'assigned_to_error' => '{1}ინვენტარის ნომერი: :asset_tag ამჟამად გაცემულია. წასაშლელად აუცილებელია ამ მოწყობილობის ჩაბარება.|[2,*]ინვენტარის ნომრები: :asset_tag ამჟამად გადაცემულია. წასაშლელად აუცილებელია ამ მოწყობილობების ჩაბარება.',
        'nothing_updated' => 'არანაირი ინვენტარი არ არის არჩეული, შესაბამისად არაფერი წაიშალა.',
        'success' => 'ინვენტარი წარმატებით წაიშალა.',
    ],

    'checkout' => [
        'error' => 'ინვენტარის გაცემა ვერ ხერხდება, გთხოვთ სცადოთ თავიდან.',
        'success' => 'ინვენტარის გაცემა წარმატებით განხორციელდა.',
        'user_does_not_exist' => 'მომხმარებელი არ არსებობს ან არავალიდურია. გთხოვთ ხელახლა სცადოთ.',
        'not_available' => 'ეს ინვენტარი ამჟამად არ არის ხელმისაწვდომი გასაცემად!',
        'no_assets_selected' => 'უნდა აირჩიოთ მინიმუმ ერთი ინვენტარი აღნიშნული სიიდან.',
    ],

    'multi-checkout' => [
        'error' => 'ინვენტარის გაცემა ვერ ხერხდება, გთხოვთ სცადოთ თავიდან.',
        'success' => 'ინვენტარის გაცემა წარმატებით განხორციელდა.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'უნდა აირჩიოთ მინიმუმ ერთი ინვენტარი აღნიშნული სიიდან.',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'უნდა აირჩიოთ მინიმუმ ერთი ინვენტარი აღნიშნული სიიდან.',
    ],

    'checkin' => [
        'error' => 'ინვენტარი არ დაბრუნებულა, გთხოვთ სცადოთ თავიდან.',
        'success' => 'ინვენტარი გაცემა წარმატებით განხორციელდა.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'მომხმარებელი არასწორია. გთხოვთ სცადოთ თავიდან.',
        'already_checked_in' => 'ეს ინვენტარი უკვე ჩაბარებულია.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'მოთხოვნა არ შესრულდა, გთხოვთ სცადოთ თავიდან.',
        'success' => 'მოთხოვნა წარმატებით გაიგზავნა.',
        'canceled' => 'მოთხოვნა წარმატებით გაუქმდა.',
        'cancel' => 'მოთხოვნის გაუქმება',
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
