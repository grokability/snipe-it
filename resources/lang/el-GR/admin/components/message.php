<?php

return [

    'does_not_exist' => 'Το στοιχείο δεν υπάρχει.',

    'create' => [
        'error' => 'Το στοιχείο δεν δημιουργήθηκε, δοκιμάστε ξανά.',
        'success' => 'Το στοιχείο ενημερώθηκε με επιτυχία.',
    ],

    'update' => [
        'error' => 'Το στοιχείο δεν ενημερώθηκε, παρακαλώ προσπαθήστε ξανά',
        'success' => 'Το στοιχείο ενημερώθηκε με επιτυχία.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το εξάρτημα;',
        'error' => 'Παρουσιάστηκε ένα ζήτημα κατάργησης του στοιχείου. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'success' => 'Το εξάρτημα έχει διαγραφεί με επιτυχία.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Το στοιχείο δεν έχει ελεγχθεί, δοκιμάστε ξανά',
        'success' => 'Η συνιστώσα ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν είναι έγκυρος. Παρακαλώ δοκιμάστε ξανά.',
        'unavailable' => 'Δεν υπάρχουν αρκετά στοιχεία που απομένουν: :remaining απομένουν, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Δεν άλλαξαν πεδία, επομένως τίποτα δεν ενημερώθηκε.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Το στοιχείο δεν έχει επιλεγεί, δοκιμάστε ξανά',
        'success' => 'Το στοιχείο ελέγχου ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης είναι άκυρος. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
    ],

];
