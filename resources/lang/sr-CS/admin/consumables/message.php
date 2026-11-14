<?php

return [

    'invalid_category_type' => 'Kategorija mora biti kategorija potrošne robe.',
    'does_not_exist' => 'Potrošni materijal ne postoji.',

    'create' => [
        'error' => 'Potrošni materijal nije kreiran, pokušajte ponovo.',
        'success' => 'Potrošni materijal uspešno kreiran.',
    ],

    'update' => [
        'error' => 'Potrošni materijal nije ažuriran, pokušajte ponovo',
        'success' => 'Potrošni materijal uspešno ažuriran.',
    ],

    'delete' => [
        'confirm' => 'Da li ste sigurni da želite izbrisati ovaj potrošni materijal?',
        'error' => 'Došlo je do problema s brisanjem potrošnog materijala. Molim pokušaj te ponovo.',
        'success' => 'Potrošni materijal je uspešno izbrisan.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Consumable was not checked out, please try again',
        'success' => 'Consumable checked out successfully.',
        'user_does_not_exist' => 'Korisnik je nevažeći. Molim pokušaj te ponovo.',
        'unavailable' => 'Nema dovoljno potrošnog materijala za ovo zaduženje. Molim vas proverite preostalu količinu. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Consumable was not checked in, please try again',
        'success' => 'Consumable checked in successfully.',
        'user_does_not_exist' => 'Korisnik je nevažeći. Molim pokušaj te ponovo.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Polja nisu menjana, tako da ništa nije ažurirano.',
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
