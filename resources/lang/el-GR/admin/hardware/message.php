<?php

return [

    'undeployable' => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' => 'To πάγιο δεν υπάρχει.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Αυτό το στοιχείο δεν υπάρχει ή δεν απαιτείται.',
    'assoc_users' => 'Αυτό το στοιχείο είναι συνήθως αποσυνδεδεμένο από έναν χρήστη και δεν μπορεί να διαγραφεί. Ελέγξτε πρώτα το στοιχείο και, στη συνέχεια, δοκιμάστε ξανά τη διαγραφή.',
    'warning_audit_date_mismatch' => 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated' => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error' => 'Το περιουσιακού στοιχείο δεν δημιουργήθηκε, παρακαλώ προσπαθήστε ξανά. :(',
        'success' => 'Το πάγιο δημιουργήθηκε επιτυχώς',
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
        'error' => 'Το πάγιο δεν ενημερώθηκε, παρακαλώ προσπαθήστε ξανά',
        'success' => 'Τα περιουσιακά στοιχεία ενημερώθηκαν επιτυχώς.',
        'encrypted_warning' => 'Το πάγιο ενημερώθηκε επιτυχώς, αλλά τα κρυπτογραφημένα προσαρμοσμένα πεδία δεν οφείλονταν στα δικαιώματα',
        'nothing_updated' => 'Δεν επιλέχθηκαν πεδία, επομένως τίποτα δεν ενημερώθηκε.',
        'no_assets_selected' => 'Δεν επιλέχθηκαν στοιχεία ενεργητικού, επομένως τίποτα δεν ενημερώθηκε.',
        'assets_do_not_exist_or_are_invalid' => 'Τα επιλεγμένα περιουσιακά στοιχεία δεν μπορούν να ενημερωθούν.',
    ],

    'bulk_update' => [
        'success' => 'Asset updated successfully.|:count assets were updated successfully.',
        'partial' => ':success asset(s) updated successfully, :failed failed. See the results array for details.',
        'error' => 'No assets were updated. See the results array for details.',
    ],

    'restore' => [
        'error' => 'Το ενεργητικό δεν έχει αποκατασταθεί, δοκιμάστε ξανά',
        'success' => 'Τα πάγια επαναφέρθηκαν επιτυχώς.',
        'bulk_success' => 'Τα πάγια επαναφέρθηκαν επιτυχώς.',
        'nothing_updated' => 'Δεν επιλέχθηκαν στοιχεία ενεργητικού, οπότε τίποτα δεν αποκαταστάθηκε.',
    ],

    'audit' => [
        'error' => 'Asset audit unsuccessful: :error ',
        'success' => 'Ο έλεγχος περιουσιακών στοιχείων ολοκληρώθηκε με επιτυχία.',
    ],

    'deletefile' => [
        'error' => 'Το αρχείο δεν έχει διαγραφεί. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Το αρχείο διαγράφηκε με επιτυχία.',
    ],

    'upload' => [
        'error' => 'Τα αρχεία δεν μεταφορτώθηκαν. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Τα αρχεία ενημερώθηκαν με επιτυχία.',
        'nofiles' => 'Δεν έχετε επιλέξει οποιαδήποτε αρχείο για μεταφόρτωση ή το αρχείο που προσπαθείτε να φορτώσετε είναι πάρα πολύ μεγάλο',
        'invalidfiles' => 'Ένα ή περισσότερα από τα αρχεία σας είναι πολύ μεγάλα ή είναι τύπου αρχείου που δεν επιτρέπεται. Τα επιτρεπόμενα αρχεία τύπου png, gif, jpg, doc, docx, pdf και txt.',
    ],

    'import' => [
        'import_button' => 'Process Import',
        'error' => 'Ορισμένα στοιχεία δεν έχουν εισαχθεί σωστά.',
        'errorDetail' => 'Τα παρακάτω στοιχεία δεν εισήχθησαν εξαιτίας σφαλμάτων.',
        'success' => 'Το αρχείο σας έχει εισαχθεί',
        'file_delete_success' => 'Το αρχείο σας έχει διαγραφεί με επιτυχία',
        'file_delete_error' => 'Το αρχείο δεν μπόρεσε να διαγραφεί',
        'file_missing' => 'Λείπει το επιλεγμένο αρχείο',
        'file_already_deleted' => 'The file selected was already deleted',
        'file_missing_on_disk' => 'The file for this import is no longer on disk. It may have been deleted outside of Snipe-IT. Delete this entry and re-upload the file to try again.',
        'file_empty' => 'This file has no data rows. Nothing can be imported from it.',
        'already_processing' => 'This import is currently being processed by another user. Please wait for it to finish before trying again.',
        'header_row_missing' => 'This file does not have a recognized header row. Delete this entry and re-upload the file to try again.',
        'header_row_has_malformed_characters' => 'Ένα ή περισσότερα χαρακτηριστικά στη σειρά κεφαλίδας περιέχουν κακοσχηματισμένους UTF-8 χαρακτήρες',
        'content_row_has_malformed_characters' => 'Ένα ή περισσότερα χαρακτηριστικά στην πρώτη σειρά περιεχομένου περιέχουν κακοσχηματισμένους UTF-8 χαρακτήρες',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input',
        'bulk_delete' => [
            'button' => 'Delete Selected (:count)',
            'confirm_title' => 'Delete selected import files?',
            'confirm_body' => 'You are about to permanently delete :count import file(s). This cannot be undone.',
            'confirm_button' => 'Διαγραφή',
            'success' => 'Import file deleted successfully.|:count import files were deleted successfully.',
            'skipped' => ':count file(s) were skipped because you do not have permission to delete them.',
            'select_all' => 'Select all files on this page',
            'select_row' => 'Select :file for bulk delete',
        ],
        'row_count' => '{0} No data rows in this file|{1} :count data row to import|[2,*] :count data rows to import',
        'summary' => [
            'created' => 'Δημιουργήθηκε',
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
        'import_label' => 'Εισαγωγή',
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
            'back' => 'Προηγούμενο',
            'next' => 'Επόμενο',
            'preview_button' => 'Preview',
            'process' => 'Process import',
            'preview_intro' => 'Previewing the first :count row(s) after applying your mapping. Use the Back button if you need to edit the mapped attributes before importing.',
        ],
    ],

    'delete' => [
        'confirm' => 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το πάγιο;',
        'error' => 'Παρουσιάστηκε ένα ζήτημα κατάργησης του στοιχείου. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated' => 'Δεν επιλέχθηκαν στοιχεία ενεργητικού, οπότε τίποτα δεν διαγράφηκε.',
        'success' => 'Το πάγιο διαγράφηκε με επιτυχία.',
    ],

    'checkout' => [
        'error' => 'Το περιουσιακό στοιχείο δεν έχει ελεγχθεί, δοκιμάστε ξανά',
        'success' => 'Το ενεργητικό ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης είναι άκυρος. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'not_available' => 'Αυτό το πάγιο δεν είναι διαθέσιμο για την ολοκλήρωση της παραγγελίας!',
        'no_assets_selected' => 'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο προς δημοσίευση.',
    ],

    'multi-checkout' => [
        'error' => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'multi-checkin' => [
        'error' => 'Asset was not checked in, please try again|Assets were not checked in, please try again',
        'success' => 'Asset checked in successfully.|Assets checked in successfully.',
        'no_assets_selected' => 'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο προς δημοσίευση.',
    ],

    'multi-audit' => [
        'success' => ':count asset audited successfully.|:count assets audited successfully.',
        'partial_error' => ':success asset audited, :failed failed. Check the errors below and try again.|:success assets audited, :failed failed. Check the errors below and try again.',
        'no_assets_selected' => 'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο προς δημοσίευση.',
    ],

    'checkin' => [
        'error' => 'Το στοιχείο δεν έχει επιλεγεί, δοκιμάστε ξανά',
        'success' => 'Το ενεργό στοιχείο ολοκληρώθηκε με επιτυχία.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν υπάρχει. Παρακαλώ δοκιμάστε ξανά.',
        'already_checked_in' => 'Το στοιχείο αυτό έχει ήδη ελεγχθεί.',
        'force_checkin_orphaned_success' => 'Invalid assignment cleared successfully.',
        'force_checkin_not_orphaned' => 'Item is not in an invalid assignment state.',
        'force_checkin_error' => 'Could not clear invalid assignment.',

    ],

    'requests' => [
        'error' => 'Request was not successful, please try again.',
        'success' => 'Request successfully submitted.',
        'canceled' => 'Request successfully canceled.',
        'cancel' => 'Ακυρώστε αυτό το αίτημα στοιχείου',
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
