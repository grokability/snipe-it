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
        'bulk_success' => 'Príslušenstvo bolo úspešne odstránené.|:count príslušenstiev bolo úspešne odstránených.',
        'partial_success' => ':count príslušenstvo bolo úspešne odstránené, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.|:count príslušenstiev bolo úspešne odstránených, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.',
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
        'error' => 'Príslušenstvo nebolo obnovené, skúste to znova',
        'success' => 'Príslušenstvo bolo úspešne obnovené.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'Nebolo vybrané žiadne príslušenstvo.',
        'no_action_selected' => 'Vyberte akciu z rozbaľovacieho zoznamu.',
        'no_fields_updated' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
        'skipped_unauthorized' => ':count príslušenstiev bolo preskočených (nemáte oprávnenie ich aktualizovať).',

        'update' => [
            'success' => ':count príslušenstvo bolo aktualizované.|:count príslušenstiev bolo aktualizovaných.',
            'none_updated' => 'Vybrané príslušenstvo sa nepodarilo aktualizovať. Podrobnosti nájdete v chybách nižšie.|Žiadne z vybraných príslušenstiev sa nepodarilo aktualizovať. Podrobnosti nájdete v chybách nižšie.',
        ],

        'restore' => [
            'success' => ':count príslušenstvo bolo obnovené.|:count príslušenstiev bolo obnovených.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'Nebolo vybrané žiadne odovzdané príslušenstvo.',
            'success' => ':count odovzdané príslušenstvo bolo prevzaté späť.|:count odovzdaných príslušenstiev bolo prevzatých späť.',
        ],
    ],
];
