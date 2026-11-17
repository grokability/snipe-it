<?php

return [

    'deleted' => 'Törölt beszállító',
    'does_not_exist' => 'Beszállító nem létezik.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Beszállító nem lett létrehozva, próbálja meg újra.',
        'success' => 'A szállító sikeresen létrejött.',
    ],

    'update' => [
        'error' => 'Szállító nem frissült, próbálkozzon újra',
        'success' => 'Szállító sikeresen frissült.',
    ],

    'delete' => [
        'confirm' => 'Biztosan törölni szeretné ezt a szállítót?',
        'error' => 'Hiba történt a Szállító törlése közben. Kérem, próbálja újra.',
        'success' => 'A szállító sikeresen törölve lett.',
        'not_found' => 'A beszállító nem található.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
