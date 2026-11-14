<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Το αναλώσιμο δεν υπάρχει.',

    'create' => [
        'error' => 'Δεν δημιουργήθηκε αναλώσιμο, δοκιμάστε ξανά.',
        'success' => 'Το αναλώσιμο δημιουργήθηκε με επιτυχία.',
    ],

    'update' => [
        'error' => 'Το αναλώσιμο δεν ενημερώθηκε, δοκιμάστε ξανά',
        'success' => 'Το αναλώσιμο ενημερώθηκε με επιτυχία.',
    ],

    'delete' => [
        'confirm' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το αναλώσιμο;',
        'error' => 'Παρουσιάστηκε πρόβλημα κατά τη διαγραφή του αναλώσιμου. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
        'success' => 'Το αναλώσιμο έχει διαγραφεί με επιτυχία.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Το αναλώσιμο δεν έχει ελεγχθεί, δοκιμάστε ξανά',
        'success' => 'Το αναλώσιμο ελεγχόταν με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν είναι έγκυρος. Παρακαλώ δοκιμάστε ξανά.',
        'unavailable' => 'Δεν υπάρχουν αρκετά αναλώσιμα για αυτήν την παραγγελία. Παρακαλώ ελέγξτε την ποσότητα που απομένει. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Το αναλώσιμο δεν έχει επιλεγεί, δοκιμάστε ξανά',
        'success' => 'Το αναλώσιμο ελεγχόταν με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης είναι άκυρος. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Δεν άλλαξαν πεδία, επομένως τίποτα δεν ενημερώθηκε.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
