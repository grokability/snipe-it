<?php

return [

    'invalid_category_type' => 'Kategória musí byť kategóriou spotrebného materiálu.',
    'does_not_exist' => 'Spotrebný materiál neexistuje.',

    'create' => [
        'error' => 'Spotrebný materiál nebol pridaný, prosím skúste znovu.',
        'success' => 'Spotrebný materiál bol úspešne pridaný.',
    ],

    'update' => [
        'error' => 'Spotrebný materiál nebol upravený, prosím skúste znovu',
        'success' => 'Spotrebný materiál bol úspešne upravený.',
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete zmazať tento spotrebný materiál?',
        'error' => 'Pri mazaní spotrebného materiálu nastal problém. Skúste prosím znovu.',
        'success' => 'Spotrebný materiál bol úspešne zmazaný.',
        'bulk_success' => 'Spotrebný materiál bol úspešne odstránený.|:count spotrebných materiálov bolo úspešne odstránených.',
        'partial_success' => ':count spotrebný materiál bol úspešne odstránený, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.|:count spotrebných materiálov bolo úspešne odstránených, ale ostatné sa nepodarilo odstrániť. Podrobnosti nájdete nižšie.',
    ],

    'checkout' => [
        'error' => 'Spotrebný materiál nebol priradený, prosím skúsiť znovu',
        'success' => 'Spotrebný materiál bol úspešne priradený.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'unavailable' => 'Nedostatok spotrebného materiálu pre priradenie. Prosím skontrolujte zostávajúce množstvo. ',
    ],

    'consumed' => [
        'success' => 'Výdaj bol označený ako spotrebovaný.',
        'bulk_success' => ':count výdaj bol označený ako spotrebovaný.|:count výdajov bolo označených ako spotrebované.',
        'already' => 'Tento výdaj už bol označený ako spotrebovaný.',
        'error' => 'Výdaj sa nepodarilo označiť ako spotrebovaný, skúste to znova.',
        'no_rows_selected' => 'Neboli vybrané žiadne platné výdaje.',
    ],

    'checkin' => [
        'error' => 'Spotrebný materiál nebol prevzatý, prosím skúste znovu',
        'success' => 'Spotrebný materiál bol prevzatý úspešne.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
    ],

    'restore' => [
        'error' => 'Spotrebný materiál nebol obnovený, skúste to znova',
        'success' => 'Spotrebný materiál bol úspešne obnovený.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'Nebol vybraný žiadny spotrebný materiál.',
        'no_action_selected' => 'Vyberte akciu z rozbaľovacieho zoznamu.',
        'no_fields_updated' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
        'skipped_unauthorized' => ':count spotrebných materiálov bolo preskočených (nemáte oprávnenie ich aktualizovať).',

        'update' => [
            'success' => ':count spotrebný materiál bol aktualizovaný.|:count spotrebných materiálov bolo aktualizovaných.',
            'none_updated' => 'Vybraný spotrebný materiál sa nepodarilo aktualizovať. Podrobnosti nájdete v chybách nižšie.|Žiadny z vybraných spotrebných materiálov sa nepodarilo aktualizovať. Podrobnosti nájdete v chybách nižšie.',
        ],

        'restore' => [
            'success' => ':count spotrebný materiál bol obnovený.|:count spotrebných materiálov bolo obnovených.',
        ],
    ],
];
