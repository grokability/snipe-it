<?php

return [

    'does_not_exist' => 'Príslušenstvo [:id] neexistuje.',
    'not_found' => 'Toto príslušenstvo nebolo nájdené.',
    'assoc_users' => 'Toto príslušenstvo má momentálne :count položiek priradených používateľom. Prosím prevezmite príslušenstvo od používateľov a skúste znovu. ',

    'create' => [
        'error' => 'Príslušenstvo nebolo vytvorené, skúste prosím znovu.',
        'success' => 'Príslušenstvo bolo úspešne vytvorené.',
    ],

    'update' => [
        'error' => 'Príslušenstvo nebolo upravené, skúste prosím znovu',
        'success' => 'Príslušenstvo bolo úspešne upravené.',
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete zmazať toto príslušenstvo?',
        'error' => 'Pri mazaní príslušenstva nastala chyba. Skúste prosím znovu.',
        'success' => 'Príslušenstvo bolo úspešne zmazané.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Príslušenstvo nebolo priradené, skúste prosím znovu',
        'success' => 'Príslušenstvo bolo úspešne priradené.',
        'unavailable' => 'Príslušenstvo nie je dostupné na priradenie. Overte dostupné množstvo',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'checkout_qty' => [
            'lte' => 'Aktuálne je dostupný iba jeden kus tohto príslušenstva a vy sa snažíte priradiť :checkout_qty. Prosím upravte priraďované množstvo alebo celkové zásoby tohto príslušenstva a skúste znovu.|Aktuálne sú dostupné iba :number_currently_remaining kusy tohto príslušenstva a vy sa snažíte priradiť :checkout_qty. Prosím upravte priraďované množstvo alebo celkové zásoby tohto príslušenstva a skúste znovu.',
        ],

    ],

    'checkin' => [
        'error' => 'Príslušenstvo nebolo prevzaté, skúste znovu',
        'success' => 'Príslušenstvo bolo úspešne prevzaté.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
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
