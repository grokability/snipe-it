<?php

return [

    'does_not_exist' => 'Componenta nu există.',

    'create' => [
        'error' => 'Componenta nu a fost creată, încercați din nou.',
        'success' => 'Componenta a fost creată cu succes.',
    ],

    'update' => [
        'error' => 'Componenta nu a fost actualizată, încercați din nou',
        'success' => 'Componenta sa actualizat cu succes.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Sigur doriți să ștergeți această componentă?',
        'error' => 'A apărut o problemă de ștergere a componentei. Vă rugăm să încercați din nou.',
        'success' => 'Componenta a fost șters cu succes.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Componenta nu a fost verificată, încercați din nou',
        'success' => 'Componenta verificată cu succes.',
        'user_does_not_exist' => 'Acest utilizator este nevalid. Vă rugăm să încercați din nou.',
        'unavailable' => 'Nu au mai rămas suficiente componente: :rămase :solicitat ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nu au fost modificate câmpuri, deci nimic nu a fost actualizat.',
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
        'error' => 'Componenta nu a fost verificată, încercați din nou',
        'success' => 'Componenta a fost verificată cu succes.',
        'user_does_not_exist' => 'Acest utilizator este nevalid. Vă rugăm să încercați din nou.',
    ],

];
