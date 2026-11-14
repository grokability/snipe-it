<?php

return [

    'does_not_exist' => 'Komponenta ne postoji.',

    'create' => [
        'error' => 'Komponenta nije kreirana, pokušajte ponovo.',
        'success' => 'Komponenta je uspešno kreirana.',
    ],

    'update' => [
        'error' => 'Komponenta nije ažurirana, pokušajte ponovo',
        'success' => 'Komponenta je uspešno ažurirana.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Da li ste sigurni da želite izbrisati ovu komponentu?',
        'error' => 'Došlo je do problema s brisanjem komponente. Molim pokušaj te ponovo.',
        'success' => 'Komponenta je uspješno izbrisana.',
        'error_qty' => 'Neke komponente ovog tipa su još uvek zadužene. Razdužite ih i pokušajte ponovo.',
    ],

    'checkout' => [
        'error' => 'Component was not checked out, please try again',
        'success' => 'Component checked out successfully.',
        'user_does_not_exist' => 'Korisnik nevažeći. Molim pokušaj te ponovo.',
        'unavailable' => 'Nema dovoljno preostalih komponenti: :remaining preostalo, :requested zatraženo ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Polja nisu menjana, tako da ništa nije ažurirano.',
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
        'success' => 'Komponenta je uspešno prijavljena.',
        'user_does_not_exist' => 'Korisnik je nevažeći. Molim pokušajte ponovo.',
    ],

];
