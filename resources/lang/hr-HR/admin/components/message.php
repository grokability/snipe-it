<?php

return [

    'does_not_exist' => 'Komponenta ne postoji.',

    'create' => [
        'error' => 'Komponenta nije izrađena, pokušajte ponovo.',
        'success' => 'Komponenta je uspješno izrađena.',
    ],

    'update' => [
        'error' => 'Komponenta nije ažurirana, pokušajte ponovo',
        'success' => 'Komponenta je uspješno ažurirana.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Jeste li sigurni da želite izbrisati ovu komponentu?',
        'error' => 'Došlo je do problema s brisanjem komponente. Molim te pokušaj ponovno.',
        'success' => 'Komponenta je uspješno izbrisana.',
        'error_qty' => 'Neke komponente ovoga tipa su i dalje izdate. Molimo prijavite ih i pokušajte ponovno.',
    ],

    'checkout' => [
        'error' => 'Komponenta nije provjerena, pokušajte ponovo',
        'success' => 'Komponenta je uspješno provjerena.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
        'unavailable' => 'Nema dovoljno preostalih komponenti. Preostalo je :remaining, zatraženo je :requested',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nijedna polja nisu promijenjena, tako da ništa nije ažurirano.',
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
        'error' => 'Komponenta nije prijavljena, pokušajte ponovo',
        'success' => 'Komponenta je uspješno prijavljena.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
    ],

];
