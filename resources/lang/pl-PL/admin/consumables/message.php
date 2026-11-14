<?php

return [

    'invalid_category_type' => 'Kategoria musi być materiałem eksploatacyjnym.',
    'does_not_exist' => 'Materiał eksploatacyjny nie istnieje.',

    'create' => [
        'error' => 'Materiał eksploatacyjny nie został utworzony, spróbuj ponownie.',
        'success' => 'Materiał eksploatacyjny utworzony pomyślnie.',
    ],

    'update' => [
        'error' => 'Materiał eksploatacyjny nie został uaktualniony, spróbuj ponownie',
        'success' => 'Materiał eksploatacyjny został zaktualizowany pomyślnie.',
    ],

    'delete' => [
        'confirm' => 'Czy na pewno chcesz usunąć materiał eksploatacyjny?',
        'error' => 'Wystąpił problem podczas usuwania materiału eksploatacyjnego. Spróbuj ponownie.',
        'success' => 'Materiał eksploatacyjny został usunięty pomyślnie.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Materiał eksploatacyjny nie został wydany, spróbuj ponownie',
        'success' => 'Materiał eksploatacyjny został wydany pomyślnie.',
        'user_does_not_exist' => 'Użytkownik nie istnieje. Spróbuj ponownie.',
        'unavailable' => 'Nie ma wystarczającej ilości materiałów do tego zamówienia. Proszę sprawdzić pozostałą ilość. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Materiał eksploatacyjny nie został odebrany, spróbuj ponownie',
        'success' => 'Materiał eksploatacyjny odebrany pomyślnie.',
        'user_does_not_exist' => 'Użytkownik nie istnieje. Spróbuj ponownie.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Żadne pole nie zostało zmodyfikowane, więc nic nie zostało zaktualizowane.',
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
