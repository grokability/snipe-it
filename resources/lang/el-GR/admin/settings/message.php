<?php

return [

    'update' => [
        'error'                 => 'Παρουσιάστηκε ένα σφάλμα κατά την ενημέρωση. ',
        'success'               => 'Οι ρυθμίσεις αναβαθμίστηκαν επιτυχώς.',
    ],
    'backup' => [
        'delete_confirm'        => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το αρχείο αντιγράφων ασφαλείας; Αυτή η ενέργεια δεν μπορεί να αναιρεθεί. ',
        'file_deleted'          => 'Το αντίγραφο ασφαλείας διαγράφηκε επιτυχώς. ',
        'generated'             => 'Δημιουργήθηκε με επιτυχία ένα νέο αρχείο δημιουργίας αντιγράφων ασφαλείας.',
        'file_not_found'        => 'Αυτό το αρχείο αντιγράφων ασφαλείας δεν βρέθηκε στο διακομιστή.',
        'restore_warning'       => 'Ναι, να το αποκαταστήσω, αναγνωρίζω ότι αυτό θα αντικαταστήσει όλα τα υπάρχοντα δεδομένα που υπάρχουν αυτή τη στιγμή στη βάση δεδομένων. Αυτό θα αποσυνδεθεί επίσης από όλους τους υπάρχοντες χρήστες (συμπεριλαμβανομένων και εσείς).',
        'restore_confirm'       => 'Είστε βέβαιοι ότι θέλετε να επαναφέρετε τη βάση δεδομένων σας από :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Παρουσιάστηκε ένα σφάλμα κατά την εκκαθάριση. ',
        'validation_failed'     => 'Η επιβεβαίωση καθαρισμού είναι εσφαλμένη. Παρακαλούμε πληκτρολογήστε τη λέξη «Διαγραφή» στο πλαίσιο επιβεβαίωσης.',
        'success'               => 'Οι διαγραμμένες εγγραφές καθαρίστηκαν με επιτυχία.',
    ],
    'mail' => [
        'sending' => 'Αποστολή Δοκιμής Email...',
        'success' => 'Η αλληλογραφία στάλθηκε!',
        'error' => 'Δεν ήταν δυνατή η αποστολή του ταχυδρομείου.',
        'additional' => 'Δεν δόθηκε κανένα πρόσθετο μήνυμα σφάλματος. Ελέγξτε τις ρυθμίσεις αλληλογραφίας και το αρχείο καταγραφής της εφαρμογής σας.'
    ],
    'ldap' => [
        'testing' => 'Δοκιμή Σύνδεσης Ldap, Δεσμεύσεων & Ερωτήματος...',
        '500' => '500 Σφάλμα διακομιστή. Παρακαλώ ελέγξτε τα αρχεία καταγραφής του διακομιστή σας για περισσότερες πληροφορίες.',
        'error' => 'Κάτι πήγε στραβά :(',
        'sync_success' => 'Ένα δείγμα 10 χρηστών που επιστρέφονται από το διακομιστή LDAP με βάση τις ρυθμίσεις σας:',
        'testing_authentication' => 'Δοκιμή Πιστοποίησης Ldap...',
        'authentication_success' => 'Ο χρήστης πιστοποιήθηκε με επιτυχία στο LDAP!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Αποστολή δοκιμαστικού μηνύματος :app...',
        'success' => 'Το :webhook_name σας λειτουργεί!',
        'success_pt1' => 'Επιτυχία! Ελέγξτε το ',
        'success_pt2' => ' κανάλι για το δοκιμαστικό μήνυμά σας και βεβαιωθείτε ότι κάνετε κλικ στο SAVE παρακάτω για να αποθηκεύσετε τις ρυθμίσεις σας.',
        '500' => '500 Σφάλμα Διακομιστή.',
        'error' => 'Κάτι πήγε στραβά. :app απάντησε με: :error_message',
        'error_redirect' => 'ΣΦΑΛΜΑ: 301/302:endpoint επιστρέφει μια ανακατεύθυνση. Για λόγους ασφαλείας, δεν ακολουθούμε ανακατευθύνσεις. Παρακαλούμε χρησιμοποιήστε το πραγματικό τελικό σημείο.',
        'error_misc' => 'Κάτι πήγε στραβά. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
