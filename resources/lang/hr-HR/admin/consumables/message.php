<?php

return [

    'invalid_category_type' => 'Kategorija mora biti za potrošni materijal.',
    'does_not_exist' => 'Potrošnja ne postoji.',

    'create' => [
        'error' => 'Potrošnja nije izrađena, pokušajte ponovo.',
        'success' => 'Potrošnja je uspješno stvorena.',
    ],

    'update' => [
        'error' => 'Potrošnja nije ažurirana, pokušajte ponovo',
        'success' => 'Potrošni proizvodi su uspješno ažurirani.',
    ],

    'delete' => [
        'confirm' => 'Jeste li sigurni da želite izbrisati ovaj potrošni materijal?',
        'error' => 'Došlo je do problema s brisanjem potrošnog materijala. Molim te pokušaj ponovno.',
        'success' => 'Potrošni materijal je uspješno izbrisan.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Potrošnja nije provjerena, pokušajte ponovo',
        'success' => 'Isporuka potrošnog materijala uspješno je provjerena.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
        'unavailable' => 'Ne postoji dovoljno potrošnog materijala za ovo izdavanje. Molimo provjerite dostupnu količinu. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Potrošnja nije provjerena, pokušajte ponovo',
        'success' => 'Potrošni materijal uspješno je provjeren.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim te pokušaj ponovno.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nijedna polja nisu promijenjena, tako da ništa nije ažurirano.',
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
