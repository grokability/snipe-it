<?php

return array(

    'does_not_exist' => 'Η τοποθεσία δεν υπάρχει.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Αυτή η τοποθεσία συσχετίζεται προς το παρόν με τουλάχιστον ένα στοιχείο και δεν μπορεί να διαγραφεί. Ενημερώστε τα στοιχεία σας ώστε να μην αναφέρονται πλέον στην τοποθεσία αυτή και να προσπαθήσετε ξανά.',
    'assoc_child_loc'	 => 'Αυτή η τοποθεσία είναι αυτήν τη στιγμή γονέας τουλάχιστον μιας τοποθεσίας παιδιού και δεν μπορεί να διαγραφεί. Ενημερώστε τις τοποθεσίες σας ώστε να μην αναφέρονται πλέον σε αυτήν την τοποθεσία και δοκιμάστε ξανά.',
    'assigned_assets' => 'Αντιστοιχισμένα Στοιχεία Ενεργητικού',
    'current_location' => 'Τρέχουσα Τοποθεσία',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Η τοποθεσία δεν έχει δημιουργηθεί, δοκιμάστε ξανά.',
        'success' => 'Η τοποθεσία δημιουργήθηκε με επιτυχία.'
    ),

    'update' => array(
        'error'   => 'Η τοποθεσία δεν έχει δημιουργηθεί, δοκιμάστε ξανά',
        'success' => 'Η τοποθεσία αναβαθμίστηκε επιτυχώς.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτήν την τοποθεσία;',
        'error'   => 'Παρουσιάστηκε πρόβλημα κατά τη διαγραφή της τοποθεσίας. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'success' => 'Η τοποθεσία διαγράφηκε με επιτυχία.'
    )

);
