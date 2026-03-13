<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'To πάγιο δεν υπάρχει.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Αυτό το στοιχείο δεν υπάρχει ή δεν απαιτείται.',
    'assoc_users'	 	 => 'Αυτό το στοιχείο είναι συνήθως αποσυνδεδεμένο από έναν χρήστη και δεν μπορεί να διαγραφεί. Ελέγξτε πρώτα το στοιχείο και, στη συνέχεια, δοκιμάστε ξανά τη διαγραφή.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Το περιουσιακού στοιχείο δεν δημιουργήθηκε, παρακαλώ προσπαθήστε ξανά. :(',
        'success' 		=> 'Το πάγιο δημιουργήθηκε επιτυχώς',
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
        'error'   			=> 'Το πάγιο δεν ενημερώθηκε, παρακαλώ προσπαθήστε ξανά',
        'success' 			=> 'Τα περιουσιακά στοιχεία ενημερώθηκαν επιτυχώς.',
        'encrypted_warning' => 'Το πάγιο ενημερώθηκε επιτυχώς, αλλά τα κρυπτογραφημένα προσαρμοσμένα πεδία δεν οφείλονταν στα δικαιώματα',
        'nothing_updated'	=>  'Δεν επιλέχθηκαν πεδία, επομένως τίποτα δεν ενημερώθηκε.',
        'no_assets_selected'  =>  'Δεν επιλέχθηκαν στοιχεία ενεργητικού, επομένως τίποτα δεν ενημερώθηκε.',
        'assets_do_not_exist_or_are_invalid' => 'Τα επιλεγμένα περιουσιακά στοιχεία δεν μπορούν να ενημερωθούν.',
    ],

    'restore' => [
        'error'   		=> 'Το ενεργητικό δεν έχει αποκατασταθεί, δοκιμάστε ξανά',
        'success' 		=> 'Τα πάγια επαναφέρθηκαν επιτυχώς.',
        'bulk_success' 		=> 'Τα πάγια επαναφέρθηκαν επιτυχώς.',
        'nothing_updated'   => 'Δεν επιλέχθηκαν στοιχεία ενεργητικού, οπότε τίποτα δεν αποκαταστάθηκε.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Ο έλεγχος περιουσιακών στοιχείων ολοκληρώθηκε με επιτυχία.',
    ],


    'deletefile' => [
        'error'   => 'Το αρχείο δεν έχει διαγραφεί. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Το αρχείο διαγράφηκε με επιτυχία.',
    ],

    'upload' => [
        'error'   => 'Τα αρχεία δεν μεταφορτώθηκαν. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Τα αρχεία ενημερώθηκαν με επιτυχία.',
        'nofiles' => 'Δεν έχετε επιλέξει οποιαδήποτε αρχείο για μεταφόρτωση ή το αρχείο που προσπαθείτε να φορτώσετε είναι πάρα πολύ μεγάλο',
        'invalidfiles' => 'Ένα ή περισσότερα από τα αρχεία σας είναι πολύ μεγάλα ή είναι τύπου αρχείου που δεν επιτρέπεται. Τα επιτρεπόμενα αρχεία τύπου png, gif, jpg, doc, docx, pdf και txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Ορισμένα στοιχεία δεν έχουν εισαχθεί σωστά.',
        'errorDetail'           => 'Τα παρακάτω στοιχεία δεν εισήχθησαν εξαιτίας σφαλμάτων.',
        'success'               => 'Το αρχείο σας έχει εισαχθεί',
        'file_delete_success'   => 'Το αρχείο σας έχει διαγραφεί με επιτυχία',
        'file_delete_error'      => 'Το αρχείο δεν μπόρεσε να διαγραφεί',
        'file_missing' => 'Λείπει το επιλεγμένο αρχείο',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Ένα ή περισσότερα χαρακτηριστικά στη σειρά κεφαλίδας περιέχουν κακοσχηματισμένους UTF-8 χαρακτήρες',
        'content_row_has_malformed_characters' => 'Ένα ή περισσότερα χαρακτηριστικά στην πρώτη σειρά περιεχομένου περιέχουν κακοσχηματισμένους UTF-8 χαρακτήρες',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το πάγιο;',
        'error'   		=> 'Παρουσιάστηκε ένα ζήτημα κατάργησης του στοιχείου. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Δεν επιλέχθηκαν στοιχεία ενεργητικού, οπότε τίποτα δεν διαγράφηκε.',
        'success' 		=> 'Το πάγιο διαγράφηκε με επιτυχία.',
    ],

    'checkout' => [
        'error'   		=> 'Το περιουσιακό στοιχείο δεν έχει ελεγχθεί, δοκιμάστε ξανά',
        'success' 		=> 'Το ενεργητικό ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης είναι άκυρος. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'not_available' => 'Αυτό το πάγιο δεν είναι διαθέσιμο για την ολοκλήρωση της παραγγελίας!',
        'no_assets_selected' => 'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο προς δημοσίευση.',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Το στοιχείο δεν έχει επιλεγεί, δοκιμάστε ξανά',
        'success' 		=> 'Το ενεργό στοιχείο ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν υπάρχει. Παρακαλώ δοκιμάστε ξανά.',
        'already_checked_in'  => 'Το στοιχείο αυτό έχει ήδη ελεγχθεί.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Ακυρώστε αυτό το αίτημα στοιχείου',
    ],

];
