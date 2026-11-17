<?php

return [

    'does_not_exist' => 'Το εξάρτημα δεν υπάρχει.',
    'not_found' => 'Αυτό το δευτερεύον στοιχείο δεν βρέθηκε.',
    'assoc_users' => 'Αυτό το εξάρτημα διαθέτει: τα στοιχεία καταμέτρησης που ελέγχθηκαν στους χρήστες. Ελέγξτε τα εξαρτήματα και δοκιμάστε ξανά.',

    'create' => [
        'error' => 'Το εξάρτημα δεν δημιουργήθηκε, παρακαλώ προσπαθήστε ξανά.',
        'success' => 'Το εξάρτημα δημιουργήθηκε με επιτυχία.',
    ],

    'update' => [
        'error' => 'Το εξάρτημα δεν ενημερώθηκε, παρακαλώ προσπαθήστε ξανά',
        'success' => 'Το εξάρτημα ενημερώθηκε με επιτυχία.',
    ],

    'delete' => [
        'confirm' => 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το εξάρτημα;',
        'error' => 'Υπήρξε ένα ζήτημα διαγράφοντας το αξεσουάρ. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Το εξάρτημα διαγράφηκε με επιτυχία.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Το αξεσουάρ δεν έχει ελεγχθεί, δοκιμάστε ξανά',
        'success' => 'Το αξεσουάρ ολοκληρώθηκε με επιτυχία.',
        'unavailable' => 'Το αξεσουάρ δεν είναι διαθέσιμο για ολοκλήρωση της παραγγελίας. Ελέγξτε την διαθέσιμη ποσότητα',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν είναι έγκυρος. Παρακαλώ δοκιμάστε ξανά.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Το αξεσουάρ δεν έχει συνδεθεί, δοκιμάστε ξανά',
        'success' => 'Το αξεσουάρ ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν είναι έγκυρος. Παρακαλώ δοκιμάστε ξανά.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Δεν άλλαξαν πεδία, επομένως τίποτα δεν ενημερώθηκε.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
