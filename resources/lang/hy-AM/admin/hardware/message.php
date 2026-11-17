<?php

return [

    'undeployable' => 'Հետևյալ ակտիվները հնարավոր չէ տեղակայել և հեռացվել են հանձնումից՝ :asset_tags',
    'does_not_exist' => 'Ակտիվը գոյություն չունի։',
    'does_not_exist_var' => ':asset_tag թեգով ակտիվը չի գտնվել։',
    'no_tag' => 'Ակտիվի թեգ չի տրամադրվել։',
    'does_not_exist_or_not_requestable' => 'Այդ ակտիվը գոյություն չունի կամ հնարավոր չէ հայցել։',
    'assoc_users' => 'Այս ակտիվը ներկայումս հանձնված է օգտատիրոջը և հնարավոր չէ ջնջել։ Խնդրում ենք նախ վերադարձնել ակտիվը, ապա կրկին փորձել ջնջել։',
    'warning_audit_date_mismatch' => 'Այս ակտիվի հաջորդ աուդիտի ամսաթիվը (:next_audit_date) նախորդ աուդիտի ամսաթվից (:last_audit_date) վաղ է։ Խնդրում ենք թարմացնել հաջորդ աուդիտի ամսաթիվը։',
    'labels_generated' => 'Պիտակները հաջողությամբ ստեղծվել են։',
    'error_generating_labels' => 'Պիտակների ստեղծման ժամանակ սխալ է տեղի ունեցել։',
    'no_assets_selected' => '
Ոչ մի ակտիվ չի ընտրվել։',

    'create' => [
        'error' => 'Ակտիվը չի ստեղծվել, խնդրում ենք կրկին փորձել։ :',
        'success' => 'Ակտիվը հաջողությամբ ստեղծվել է։ :)
',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => ':tag թեգով ակտիվը հաջողությամբ ստեղծվել է։ <strong><a href=":link" style="color: white;">Սեղմեք այստեղ՝ դիտելու համար</a></strong>։


',
        'multi_success_linked' => ':links թեգով ակտիվը հաջողությամբ ստեղծվել է։|:count ակտիվ հաջողությամբ ստեղծվել է։ :links։
',
        'partial_failure' => 'Մեկ ակտիվ հնարավոր չեղավ ստեղծել։ Պատճառը՝ :failures|:count ակտիվ հնարավոր չեղավ ստեղծել։ Պատճառները՝ :failures',
        'target_not_found' => [
            'user' => 'Հանձնված օգտատերը չի գտնվել։',
            'asset' => 'Հանձնված ակտիվը չի գտնվել։',
            'location' => 'Հանձնված գտնվելու վայրը չի գտնվել։',
        ],
    ],

    'update' => [
        'error' => 'Ակտիվը չի թարմացվել, խնդրում ենք կրկին փորձել։
',
        'success' => 'Ակտիվը հաջողությամբ թարմացվել է։',
        'encrypted_warning' => 'Ակտիվը հաջողությամբ թարմացվել է, սակայն կոդավորված հատուկ դաշտերը չեն թարմացվել թույլտվությունների պատճառով',
        'nothing_updated' => 'Դաշտեր չեն ընտրվել, ուստի ոչինչ չի թարմացվել։
',
        'no_assets_selected' => 'Ակտիվներ չեն ընտրվել, ուստի ոչինչ չի թարմացվել։',
        'assets_do_not_exist_or_are_invalid' => 'Ընտրված ակտիվները հնարավոր չէ թարմացնել։',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Ակտիվը չի վերականգնվել, խնդրում ենք կրկին փորձել։',
        'success' => 'Ակտիվը հաջողությամբ վերականգնվել է։',
        'bulk_success' => 'Ակտիվը հաջողությամբ վերականգնվել է։',
        'nothing_updated' => 'Ակտիվներ չեն ընտրվել, ուստի ոչինչ չի վերականգնվել։',
    ],

    'audit' => [
        'error' => 'Ակտիվի աուդիտը ձախողվել է՝ :error',
        'success' => 'Ակտիվի աուդիտը հաջողությամբ գրանցվել է։',
    ],

    'deletefile' => [
        'error' => 'Ֆայլը չի ջնջվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Ֆայլը հաջողությամբ ջնջվել է։',
    ],

    'upload' => [
        'error' => 'Ֆայլ(եր)ը չի(են) վերբեռնվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Ֆայլ(եր)ը հաջողությամբ վերբեռնվել է(են)։',
        'nofiles' => 'Դուք չեք ընտրել որևէ ֆայլ վերբեռնելու համար, կամ վերբեռնել փորձող ֆայլը չափազանց մեծ է',
        'invalidfiles' => 'Ձեր ֆայլերից մեկը կամ մի քանիսը չափազանց մեծ են կամ անթույլատրելի տեսակի են։ Թույլատրելի ֆայլի տեսակներն են՝ png, gif, jpg, doc, docx, pdf և txt։',
    ],

    'import' => [
        'import_button' => 'Մշակել ներմուծումը',
        'error' => 'Որոշ տարրեր ճիշտ չեն ներմուծվել։',
        'errorDetail' => 'Հետևյալ տարրերը սխալների պատճառով չեն ներմուծվել։
',
        'success' => 'Ձեր ֆայլը ներմուծվել է։',
        'file_delete_success' => 'Ձեր ֆայլը հաջողությամբ ջնջվել է։',
        'file_delete_error' => 'Ֆայլը հնարավոր չեղավ ջնջել։',
        'file_missing' => 'Ընտրված ֆայլը բացակայում է',
        'file_already_deleted' => 'Ընտրված ֆայլն արդեն ջնջվել է',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Վերնագրի տողի մեկ կամ մի քանի հատկանիշ պարունակում են սխալ UTF-8 նիշեր',
        'content_row_has_malformed_characters' => 'Բովանդակության առաջին տողի մեկ կամ մի քանի հատկանիշ պարունակում են սխալ UTF-8 նիշեր',
        'transliterate_failure' => ':encoding-ից UTF-8 տառադարձումը ձախողվել է մուտքագրման անվավեր նիշերի պատճառով',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Ջնջել',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Ստեղծվել է',
            'updated' => 'Թարմացված',
            'skipped' => 'Skipped as duplicates',
            'errored' => 'Errored',
            'no_changes' => 'The import finished but nothing was created or updated. Every row was skipped, usually because the underlying records already existed. Check the counts below and adjust the CSV or import type if that is not what you expected.',
        ],
        'type_required' => 'Please select an import type before continuing.',
        'processing' => 'Processing your import. Please wait until this finishes before closing the page.',
        'backup_running' => 'Running backup before importing. This can take a while on larger files. Please wait.',
        'backup_label' => 'Pre-import backup',
        'backup_complete' => 'Backup complete',
        'import_label' => 'Ներմուծում',
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
            'step_preview' => 'Նախադիտում',
            'back' => 'Հետ',
            'next' => 'Հաջորդ',
            'preview_button' => 'Նախադիտում',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս ակտիվը։',
        'error' => 'Ակտիվի ջնջման ժամանակ խնդիր է առաջացել։ Խնդրում ենք կրկին փորձել։',
        'assigned_to_error' => '{1}Ակտիվի թեգը՝ :asset_tag, ներկայումս հանձնված է։ Ջնջելուց առաջ վերադարձրեք այս սարքը։|[2,*]Ակտիվի թեգերը՝ :asset_tag, ներկայումս հանձնված են։ Ջնջելուց առաջ վերադարձրեք այս սարքերը։',
        'nothing_updated' => 'Ակտիվներ չեն ընտրվել, ուստի ոչինչ չի ջնջվել։',
        'success' => 'Ակտիվը հաջողությամբ ջնջվել է։',
    ],

    'checkout' => [
        'error' => 'Ակտիվը չի հանձնվել, խնդրում ենք կրկին փորձել։',
        'success' => 'Ակտիվը հաջողությամբ հանձնվել է։',
        'user_does_not_exist' => 'Այդ օգտատերն անվավեր է։ Խնդրում ենք կրկին փորձել։',
        'not_available' => 'Այդ ակտիվը հասանելի չէ հանձնելու համար։',
        'no_assets_selected' => 'Դուք պետք է ցանկից ընտրեք առնվազն մեկ ակտիվ։',
    ],

    'multi-checkout' => [
        'error' => 'Ակտիվը հաջողությամբ հանձնվել է։|Ակտիվները հաջողությամբ հանձնվել են։',
        'success' => 'Ակտիվը հաջողությամբ հանձնվել է։|Ակտիվները հաջողությամբ հանձնվել են։',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Դուք պետք է ցանկից ընտրեք առնվազն մեկ ակտիվ։',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Դուք պետք է ցանկից ընտրեք առնվազն մեկ ակտիվ։',
    ],

    'checkin' => [
        'error' => 'Ակտիվը չի վերադարձվել, խնդրում ենք կրկին փորձել։',
        'success' => 'Ակտիվը հաջողությամբ վերադարձվել է։',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Այդ օգտատերն անվավեր է։ Խնդրում ենք կրկին փորձել։',
        'already_checked_in' => 'Այդ ակտիվն արդեն վերադարձվել է։',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Հարցումը հաջողված չէ, խնդրում ենք կրկին փորձել։',
        'success' => 'Հարցումը հաջողությամբ ուղարկվել է։',
        'canceled' => 'Հարցումը հաջողությամբ չեղարկվել է։',
        'cancel' => 'Չեղարկել այս տարրի հարցումը',
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
