<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'ទ្រព្យសកម្មមិនមានទេ។',
    'does_not_exist_var' => 'ទ្រព្យសកម្មដែលមានស្លាក៖ asset_tag រកមិនឃើញទេ។',
    'no_tag' => 'មិនមានស្លាកទ្រព្យសម្បត្តិត្រូវបានផ្តល់ឱ្យទេ។',
    'does_not_exist_or_not_requestable' => 'ទ្រព្យសកម្មនោះមិនមានទេ ឬមិនអាចស្នើសុំបាន។',
    'assoc_users' => 'This asset is currently checked out to a user and cannot be deleted. Please check the asset in first, and then try deleting again. ',
    'warning_audit_date_mismatch' => 'កាលបរិច្ឆេទសវនកម្មបន្ទាប់របស់ទ្រព្យសកម្មនេះ (:next_audit_date) គឺមុនកាលបរិច្ឆេទសវនកម្មចុងក្រោយ (:last_audit_date)។ សូមធ្វើបច្ចុប្បន្នភាពកាលបរិច្ឆេទសវនកម្មបន្ទាប់។',
    'labels_generated' => 'ស្លាកត្រូវបានបង្កើតដោយជោគជ័យ។',
    'error_generating_labels' => 'កំហុសខណៈពេលបង្កើតស្លាក។',
    'no_assets_selected' => 'មិនបានជ្រើសរើសទ្រព្យសម្បត្តិទេ។',

    'create' => [
        'error' => 'ទ្រព្យសកម្មមិនត្រូវបានបង្កើតទេ សូមព្យាយាមម្តងទៀត។ :(',
        'success' => 'ទ្រព្យសកម្មត្រូវបានបង្កើតដោយជោគជ័យ។ :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'ទ្រព្យសកម្មជាមួយស្លាក៖ ស្លាកត្រូវបានបង្កើតដោយជោគជ័យ។ <strong><a href=":link" style="color: white;">ចុចទីនេះដើម្បីមើល</a></strong>។',
        'multi_success_linked' => 'ទ្រព្យសកម្មជាមួយស្លាក៖ តំណត្រូវបានបង្កើតដោយជោគជ័យ។|: ទ្រព្យសម្បត្តិរាប់ត្រូវបានបង្កើតដោយជោគជ័យ។ ៖ តំណភ្ជាប់។',
        'partial_failure' => 'ទ្រព្យសកម្មមិនអាចបង្កើតបានទេ។ ហេតុផល៖ :failures|:រាប់ទ្រព្យសកម្មមិនអាចបង្កើតបានទេ។ មូលហេតុ៖៖ បរាជ័យ',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error' => 'ទ្រព្យសកម្មមិនត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'ទ្រព្យសកម្មបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated' => 'គ្មាន​វាល​ត្រូវ​បាន​ជ្រើស ដូច្នេះ​មិន​មាន​អ្វី​ត្រូវ​បាន​ធ្វើ​បច្ចុប្បន្នភាព​។',
        'no_assets_selected' => 'គ្មានទ្រព្យសម្បត្តិត្រូវបានជ្រើសរើស ដូច្នេះគ្មានអ្វីត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ។',
        'assets_do_not_exist_or_are_invalid' => 'ទ្រព្យសកម្មដែលបានជ្រើសរើសមិនអាចធ្វើបច្ចុប្បន្នភាពបានទេ។',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'ទ្រព្យសកម្មមិនត្រូវបានស្ដារឡើងវិញទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'ទ្រព្យសកម្មត្រូវបានស្ដារឡើងវិញដោយជោគជ័យ។',
        'bulk_success' => 'ទ្រព្យសកម្មត្រូវបានស្ដារឡើងវិញដោយជោគជ័យ។',
        'nothing_updated' => 'គ្មានទ្រព្យសម្បត្តិត្រូវបានជ្រើសរើស ដូច្នេះគ្មានអ្វីត្រូវបានស្ដារឡើងវិញទេ។',
    ],

    'audit' => [
        'error' => 'សវនកម្មទ្រព្យសកម្មមិនជោគជ័យ៖ : error ',
        'success' => 'សវនកម្មទ្រព្យសកម្មត្រូវបានកត់ត្រាដោយជោគជ័យ។',
    ],

    'deletefile' => [
        'error' => 'ឯកសារមិនត្រូវបានលុបទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'ឯកសារត្រូវបានលុបដោយជោគជ័យ។',
    ],

    'upload' => [
        'error' => 'ឯកសារ(ច្រើន) មិនត្រូវបានផ្ទុកឡើងទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'ឯកសារ(ច្រើន) ដែលបានបង្ហោះដោយជោគជ័យ។',
        'nofiles' => 'អ្នកមិនបានជ្រើសរើសឯកសារណាមួយសម្រាប់ផ្ទុកឡើង ឬឯកសារដែលអ្នកកំពុងព្យាយាមផ្ទុកឡើងមានទំហំធំពេក',
        'invalidfiles' => 'ឯកសារមួយ ឬច្រើនរបស់អ្នកមានទំហំធំពេក ឬជាប្រភេទឯកសារដែលមិនត្រូវបានអនុញ្ញាត។ ប្រភេទឯកសារដែលបានអនុញ្ញាតគឺ png, gif, jpg, doc, docx, pdf, និង txt ។',
    ],

    'import' => [
        'import_button' => 'ដំណើរការនាំចូល',
        'error' => 'ធាតុមួយចំនួនមិនបាននាំចូលត្រឹមត្រូវ។',
        'errorDetail' => 'ធាតុខាងក្រោមមិនត្រូវបាននាំចូលទេ ដោយសារមានកំហុស។',
        'success' => 'ឯកសាររបស់អ្នកត្រូវបាននាំចូល',
        'file_delete_success' => 'ឯកសាររបស់អ្នកត្រូវបានលុបដោយជោគជ័យ',
        'file_delete_error' => 'ឯកសារមិនអាចលុបបានទេ។',
        'file_missing' => 'បាត់ឯកសារដែលបានជ្រើសរើស',
        'file_already_deleted' => 'ឯកសារដែលបានជ្រើសរើសត្រូវបានលុបរួចហើយ',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'គុណលក្ខណៈមួយ ឬច្រើននៅក្នុងជួរបឋមកថាមានតួអក្សរ UTF-8 ខុសទម្រង់',
        'content_row_has_malformed_characters' => 'គុណលក្ខណៈមួយ ឬច្រើននៅក្នុងជួរទីមួយនៃមាតិកាមានតួអក្សរ UTF-8 ខុសទម្រង់',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'លុប',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'បានបង្កើត',
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
        'import_label' => 'នាំចូល',
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
            'step_preview' => 'ការមើលជាមុន',
            'back' => 'ត្រឡប់មកវិញ',
            'next' => 'បន្ទាប់',
            'preview_button' => 'ការមើលជាមុន',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'តើអ្នកប្រាកដថាចង់លុបទ្រព្យសម្បត្តិនេះទេ?',
        'error' => 'មានបញ្ហាក្នុងការលុបទ្រព្យសម្បត្តិ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'មិនមានទ្រព្យសម្បត្តិត្រូវបានជ្រើសរើស ដូច្នេះគ្មានអ្វីត្រូវបានលុបទេ។',
        'success' => 'ទ្រព្យសកម្មត្រូវបានលុបដោយជោគជ័យ។',
    ],

    'checkout' => [
        'error' => 'ទ្រព្យសកម្មមិនត្រូវបាន checked out ទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'ទ្រព្យសកម្មបាន checked out ដោយជោគជ័យ។',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'not_available' => 'ទ្រព្យ​សកម្ម​នោះ​មិន​អាច​ checkout បាន​ទេ!',
        'no_assets_selected' => 'អ្នកត្រូវតែជ្រើសរើសយ៉ាងហោចណាស់ទ្រព្យសកម្មមួយពីបញ្ជី',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'អ្នកត្រូវតែជ្រើសរើសយ៉ាងហោចណាស់ទ្រព្យសកម្មមួយពីបញ្ជី',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'អ្នកត្រូវតែជ្រើសរើសយ៉ាងហោចណាស់ទ្រព្យសកម្មមួយពីបញ្ជី',
    ],

    'checkin' => [
        'error' => 'ទ្រព្យសកម្មមិនត្រូវ checked in ទេ, សូមព្យាយាមម្តងទៀត',
        'success' => 'ទ្រព្យសកម្មបាន checked in ជោគជ័យ។',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'already_checked_in' => 'ទ្រព្យសកម្មនោះត្រូវ checked in រួចហើយ។',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'Request successfully canceled.',
        'cancel' => 'បោះបង់ការស្នើសុំធាតុនេះ។',
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
