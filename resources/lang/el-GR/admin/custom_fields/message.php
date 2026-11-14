<?php

return [

    'field' => [
        'invalid' => 'Αυτό το πεδίο δεν υπάρχει.',
        'already_added' => 'Το πεδίο έχει ήδη προσθέσει',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Το πεδία δεν δημιουργήθηκαν, παρακαλώ προσπαθήστε ξανά.',
            'success' => 'Το πεδίο δημιουργήθηκε με επιτυχία.',
            'assoc_success' => 'Το πεδίο προστέθηκε με επιτυχία στο fieldset.',
        ],

        'update' => [
            'error' => 'Το πεδίο δεν ενημερώθηκε, δοκιμάστε ξανά',
            'success' => 'Το πεδίο ενημερώθηκε επιτυχώς.',
        ],

        'delete' => [
            'confirm' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το πεδίο;',
            'error' => 'Παρουσιάστηκε πρόβλημα κατά τη διαγραφή του πεδίου. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
            'success' => 'Το πεδίο διαγράφηκε με επιτυχία.',
            'in_use' => 'Το πεδίο παραμένει σε χρήση.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Το πεδίο δεν υπάρχει',

        'create' => [
            'error' => 'Το Fieldset δεν δημιουργήθηκε, δοκιμάστε ξανά.',
            'success' => 'Το σύνολο των πεδίων δημιουργήθηκε με επιτυχία.',
        ],

        'update' => [
            'error' => 'Το Fieldset δεν ενημερώθηκε, δοκιμάστε ξανά',
            'success' => 'Το σύνολο των πεδίων ενημερώθηκε με επιτυχία.',
        ],

        'delete' => [
            'confirm' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το σύνολο πεδίων;',
            'error' => 'Παρουσιάστηκε ένα ζήτημα κατάργησης του πεδίου. ΠΑΡΑΚΑΛΩ προσπαθησε ξανα.',
            'success' => 'Το πεδίο πεδίου διαγράφηκε με επιτυχία.',
            'in_use' => 'Το σύνολο των πεδίων παραμένουν σε χρήση.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Σφάλμα επικύρωσης προκαθορισμένων τιμών fieldset.',

    ],

];
