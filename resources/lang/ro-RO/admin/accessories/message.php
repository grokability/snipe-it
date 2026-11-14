<?php

return [

    'does_not_exist' => 'Accesoriul [:id] nu există.',
    'not_found' => 'Acel accesoriu nu a fost găsit.',
    'assoc_users' => 'Acest accesoriu are în prezent : count elemente predate la utilizatori. Vă rugăm să verificaţi accesoriile și încercați din nou. ',

    'create' => [
        'error' => 'Accesoriul nu a fost adaugat, va rugam incercati din nou.',
        'success' => 'Accesoriu adaugat cu succes.',
    ],

    'update' => [
        'error' => 'Accesoriul nu a fost actualizat, va rugam incercati din nou,',
        'success' => 'Accesoriu actualizat cu succes.',
    ],

    'delete' => [
        'confirm' => 'Sigur doriți să ștergeți acest accesoriu?',
        'error' => 'A apărut o problemă la ştergerea accesoriului. Vă rugăm să încercaţi din nou.',
        'success' => 'Accesoriul a fost şters cu succes.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Accesoriu nu a fost predat, vă rugăm să încercaţi din nou',
        'success' => 'Accesoriu a fost predat.',
        'unavailable' => 'Accesoriul nu este disponibil pentru checkout. Verificați cantitatea disponibilă',
        'user_does_not_exist' => 'Acest utilizator nu este valid. Vă rugăm să încercaţi din nou.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Accesoriul nu a fost primit, vă rugăm să încercaţi din nou',
        'success' => 'Accesoriu primit cu succes.',
        'user_does_not_exist' => 'Acest utilizator nu este valid. Vă rugăm să încercaţi din nou.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nu au fost modificate câmpuri, deci nimic nu a fost actualizat.',
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
