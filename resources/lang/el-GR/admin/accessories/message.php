<?php

return array(

    'does_not_exist' => 'Το εξάρτημα δεν υπάρχει.',
    'not_found' => 'Αυτό το δευτερεύον στοιχείο δεν βρέθηκε.',
    'assoc_users'	 => 'Αυτό το εξάρτημα διαθέτει: τα στοιχεία καταμέτρησης που ελέγχθηκαν στους χρήστες. Ελέγξτε τα εξαρτήματα και δοκιμάστε ξανά.',

    'create' => array(
        'error'   => 'Το εξάρτημα δεν δημιουργήθηκε, παρακαλώ προσπαθήστε ξανά.',
        'success' => 'Το εξάρτημα δημιουργήθηκε με επιτυχία.'
    ),

    'update' => array(
        'error'   => 'Το εξάρτημα δεν ενημερώθηκε, παρακαλώ προσπαθήστε ξανά',
        'success' => 'Το εξάρτημα ενημερώθηκε με επιτυχία.'
    ),

    'delete' => array(
        'confirm'   => 'Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το εξάρτημα;',
        'error'   => 'Υπήρξε ένα ζήτημα διαγράφοντας το αξεσουάρ. Παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Το εξάρτημα διαγράφηκε με επιτυχία.'
    ),

     'checkout' => array(
        'error'   		=> 'Το αξεσουάρ δεν έχει ελεγχθεί, δοκιμάστε ξανά',
        'success' 		=> 'Το αξεσουάρ ολοκληρώθηκε με επιτυχία.',
        'unavailable'   => 'Το αξεσουάρ δεν είναι διαθέσιμο για ολοκλήρωση της παραγγελίας. Ελέγξτε την διαθέσιμη ποσότητα',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν είναι έγκυρος. Παρακαλώ δοκιμάστε ξανά.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Το αξεσουάρ δεν έχει συνδεθεί, δοκιμάστε ξανά',
        'success' 		=> 'Το αξεσουάρ ολοκληρώθηκε με επιτυχία.',
        'user_does_not_exist' => 'Αυτός ο χρήστης δεν είναι έγκυρος. Παρακαλώ δοκιμάστε ξανά.'
    )


);
