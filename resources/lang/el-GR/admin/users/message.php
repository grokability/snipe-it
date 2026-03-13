<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Οι χρήστες σας ενημερώθηκαν με επιτυχία, ωστόσο η καταχώριση του διαχειριστή σας δεν αποθηκεύτηκε, επειδή ο διαχειριστής που επιλέξατε ήταν επίσης στη λίστα χρηστών για επεξεργασία και οι χρήστες ενδέχεται να μην είναι ο δικός τους διαχειριστής. Επιλέξτε ξανά τους χρήστες σας, εξαιρουμένου του διαχειριστή.',
    'user_exists'               => 'Ο χρήστης υπάρχει ήδη!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Ο χρήστης δεν υπάρχει ή δεν έχετε δικαίωμα να τον δείτε.',
    'user_login_required'       => 'Το πεδίο εισόδου είναι υποχρεωτικό',
    'user_has_no_assets_assigned' => 'Δεν έχουν εκχωρηθεί στοιχεία ενεργητικού στο χρήστη.',
    'user_password_required'    => 'Ο κωδικός είναι απαραίτητος.',
    'insufficient_permissions'  => 'Δεν έχετε επαρκή δικαιώματα.',
    'user_deleted_warning'      => 'Αυτός ο χρήστης έχει διαγραφεί. Θα πρέπει να επαναφέρετε αυτό το χρήστη για να τον επεξεργαστείτε ή να του εκχωρήσετε νέα πάγια.',
    'ldap_not_configured'        => 'Η ενσωμάτωση LDAP δεν έχει ρυθμιστεί για αυτήν την εγκατάσταση.',
    'password_resets_sent'      => 'Οι επιλεγμένοι χρήστες που είναι ενεργοποιημένοι και έχουν μια έγκυρη διεύθυνση ηλεκτρονικού ταχυδρομείου έχουν σταλεί ένα σύνδεσμο επαναφοράς κωδικού πρόσβασης.',
    'password_reset_sent'       => 'Ένας σύνδεσμος επαναφοράς κωδικού πρόσβασης έχει σταλεί στο :email!',
    'user_has_no_email'         => 'Αυτός ο χρήστης δεν έχει μια διεύθυνση ηλεκτρονικού ταχυδρομείου στο προφίλ του.',
    'log_record_not_found'        => 'Δεν ήταν δυνατή η εύρεση μιας εγγραφής καταγραφής που να ταιριάζει με αυτόν το χρήστη.',


    'success' => array(
        'create'    => 'Ο χρήστης δημιουργήθηκε με επιτυχία.',
        'update'    => 'Ο χρήστης ενημερώθηκε με επιτυχία.',
        'update_bulk'    => 'Οι χρήστες ενημερώθηκαν με επιτυχία!',
        'delete'    => 'Ο χρήστης διαφράφηκε με επιτυχία.',
        'ban'       => 'Ο χρήστης έχει αποκλειστεί επιτυχώς.',
        'unban'     => 'Ο Χρήστης επαναφέρθηκε με επιτυχία.',
        'suspend'   => 'Ο χρήστης αναβλήθηκε με επιτυχία.',
        'unsuspend' => 'Ο χρήστης καταργήθηκε με επιτυχία.',
        'restored'  => 'Ο Χρήστης επαναφέρθηκε με επιτυχία.',
        'import'    => 'Οι χρήστες εισήχθησαν με επιτυχία.',
    ),

    'error' => array(
        'create' => 'Παρουσιάστηκε ένα πρόβλημα δημιουργίας του χρήστη. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'update' => 'Παρουσιάστηκε ένα πρόβλημα ενημέρωσης του χρήστη. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'delete' => 'Παρουσιάστηκε πρόβλημα κατά τη διαγραφή του χρήστη. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'delete_has_assets' => 'Αυτός ο χρήστης έχει αναθέσει στοιχεία και δεν ήταν δυνατή η διαγραφή του.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Παρουσιάστηκε ένα ζήτημα που δεν ανέβαλε τον χρήστη. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'import'    => 'Παρουσιάστηκε πρόβλημα κατά την εισαγωγή χρηστών. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'asset_already_accepted' => 'Το στοιχείο αυτό έχει ήδη γίνει αποδεκτό.',
        'accept_or_decline' => 'Πρέπει είτε να αποδεχθείτε είτε να απορρίψετε αυτό το στοιχείο.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Το περιουσιακό στοιχείο που προσπαθήσατε να δεχτείτε δεν σας έχει αποσταλεί.',
        'ldap_could_not_connect' => 'Δεν ήταν δυνατή η σύνδεση με το διακομιστή LDAP. Ελέγξτε τη διαμόρφωση του διακομιστή LDAP στο αρχείο ρύθμισης LDAP. <br>Ερώτηση από διακομιστή LDAP:',
        'ldap_could_not_bind' => 'Δεν ήταν δυνατή η δέσμευση του διακομιστή LDAP. Ελέγξτε τη διαμόρφωση του διακομιστή LDAP στο αρχείο ρύθμισης LDAP. <br>Ερώτηση από διακομιστή LDAP:',
        'ldap_could_not_search' => 'Δεν ήταν δυνατή η αναζήτηση στον διακομιστή LDAP. Ελέγξτε τη διαμόρφωση του διακομιστή LDAP στο αρχείο ρύθμισης LDAP. <br>Ερώτηση από διακομιστή LDAP:',
        'ldap_could_not_get_entries' => 'Δεν ήταν δυνατή η λήψη καταχωρήσεων από το διακομιστή LDAP. Ελέγξτε τη διαμόρφωση του διακομιστή LDAP στο αρχείο ρύθμισης LDAP. <br>Ερώτηση από διακομιστή LDAP:',
        'password_ldap' => 'Ο κωδικός πρόσβασης για αυτόν τον λογαριασμό γίνεται από το LDAP / Active Directory. Επικοινωνήστε με το τμήμα πληροφορικής σας για να αλλάξετε τον κωδικό πρόσβασής σας.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Το αρχείο δεν έχει διαγραφεί. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Το αρχείο διαγράφηκε με επιτυχία.',
    ),

    'upload' => array(
        'error'   => 'Τα αρχεία δεν μεταφορτώθηκαν. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Τα αρχεία ενημερώθηκαν με επιτυχία.',
        'nofiles' => 'Δεν έχετε επιλέξει κανένα αρχείο για ενημέρωση',
        'invalidfiles' => 'Ένα ή περισσότερα από τα αρχεία σας είναι πολύ μεγάλα ή είναι τύπου αρχείου που δεν επιτρέπεται. Τα επιτρεπόμενα αρχεία τύπου png, gif, jpg, doc, docx, pdf και txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Αυτός ο χρήστης δεν έχει ορίσει email.',
        'success' => 'Ο χρήστης έχει ενημερωθεί για το τρέχον απόθεμά του.'
    )
);