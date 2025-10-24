<?php

return array(

    'does_not_exist' => 'Κατηγορία δεν υπάρχει.',
    'assoc_models'	 => 'Αυτή η κατηγορία συσχετίζεται επί του παρόντος με τουλάχιστον ένα μοντέλο και δεν μπορεί να διαγραφεί. Ενημερώστε τα μοντέλα σας ώστε να μην αναφέρονται πλέον στην κατηγορία αυτή και δοκιμάστε ξανά.',
    'assoc_items'	 => 'Αυτή η κατηγορία συσχετίζεται προς το παρόν με τουλάχιστον ένα: asset_type και δεν μπορεί να διαγραφεί. Ενημερώστε το στοιχείο: asset_type ώστε να μην αναφέρεται πλέον αυτή η κατηγορία και δοκιμάστε ξανά.',

    'create' => array(
        'error'   => 'Η κατηγορία δεν δημιουργήθηκε, παρακαλώ δοκιμάστε ξανά.',
        'success' => 'Η κατηγορία δημιουργήθηκε με επιτυχία.'
    ),

    'update' => array(
        'error'   => 'Η κατηγορία δεν ενημερώθηκε, παρακαλώ δοκιμάστε ξανά',
        'success' => 'Η κατηγορία ενημερώθηκε με επιτυχία.',
        'cannot_change_category_type'   => 'Δεν μπορείτε να αλλάξετε τον τύπο κατηγορίας μόλις δημιουργηθεί',
    ),

    'delete' => array(
        'confirm'                => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτήν την κατηγορία;',
        'error'                  => 'Υπήρξε ένα ζήτημα διαγράφοντας αυτή την κατηγορία. Παρακαλώ δοκιμάστε ξανά.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
