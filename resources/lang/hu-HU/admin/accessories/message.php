<?php

return [

    'does_not_exist' => 'A tartozék [:id] nem létezik.',
    'not_found' => 'A tartozék nem található.',
    'assoc_users' => 'Ebből a tartozékból jelenleg :count db van kiadva felhasználóknak. Kérem vegyen vissza tartozékot és próbálja újra! ',

    'create' => [
        'error' => 'A tartozék nem jött létre, kérem, próbálja újra!',
        'success' => 'A tartozék sikeresen létrejött.',
    ],

    'update' => [
        'error' => 'A tartozékot nem sikerült frissíteni, kérem, próbálja újra!',
        'success' => 'A tartozék sikeresen frissült.',
    ],

    'delete' => [
        'confirm' => 'Biztosan törölni akarja ezt a tartozékot?',
        'error' => 'A tartozék törlése közben probléma merült fel, kérjük, próbálja újra!',
        'success' => 'A tartozék sikeresen törlődött.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'A tartozékot nem sikerült kiadni, kérem, próbálja újra!',
        'success' => 'A tartozék sikeresen kiadva.',
        'unavailable' => 'A tartozékot nem lehet kiadni. Ellenőrizd a kiadható mennyiséget',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra!',
        'checkout_qty' => [
            'lte' => 'Jelenleg csak egy elérhető tartozék van ebből a típusból, Ön pedig :checkout_qty darabot próbál kiadni. Kérjük, módosítsa a kiadni kívánt mennyiséget, vagy növelje a tartozék készletét, majd próbálja újra.|Jelenleg :number_currently_remaining darab elérhető tartozék van ebből a típusból, Ön pedig :checkout_qty darabot próbál kiadni. Kérjük, módosítsa a kiadni kívánt mennyiséget, vagy növelje a tartozék készletét, majd próbálja újra.',
        ],

    ],

    'checkin' => [
        'error' => 'A tartozékot nem sikerült visszavenni, kérem, próbálja újra!',
        'success' => 'A tartozék sikeresen visszavéve.',
        'user_does_not_exist' => 'Érvénytelen felhasználó. Kérem, próbálja újra!',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nincsenek mezők megváltoztak, így semmi sem frissült.',
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
