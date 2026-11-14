<?php

return [

    'invalid_category_type' => 'A kategóriának fogyóeszköz típusúnak kell lennie.',
    'does_not_exist' => 'A fogyóeszköz nem létezik.',

    'create' => [
        'error' => 'Fogyóeszköz létrehozása sikertelen, próbálja újra.',
        'success' => 'Fogyóeszköz sikeresen létrehozva.',
    ],

    'update' => [
        'error' => 'A fogyóeszközt nem sikerült frissíteni, próbálja újra',
        'success' => 'Fogyóeszköz sikeresen frissítve.',
    ],

    'delete' => [
        'confirm' => 'Biztosan törli ezt a fogyóeszközt?',
        'error' => 'Hiba történt a fogyóeszköz törlése során. Próbálja újra.',
        'success' => 'A kellék sikeresen törölve lett.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'A fogyóeszköz nem lett kiadva, próbálkozzon újra',
        'success' => 'Fogyóeszköz sikeresen kiadva.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra.',
        'unavailable' => 'Nincs elegendő fogyóeszköz amit ki lehetne adni. Ellenőrizd a kiadható mennyiséget. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'A fogyóeszköz nem lett visszavéve, próbálkozzon újra',
        'success' => 'A kellék sikeresen visszavéve.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nincsenek mezők megváltoztak, így semmi sem frissült.',
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
