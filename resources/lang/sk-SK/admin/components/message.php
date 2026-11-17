<?php

return [

    'does_not_exist' => 'Komponent neexistuje.',

    'create' => [
        'error' => 'Komponent sa nepodarilo pridať, skúste znovu.',
        'success' => 'Komponent bol úspešne pridaný.',
    ],

    'update' => [
        'error' => 'Komponent sa nepodarilo upraviť, skúste znovu',
        'success' => 'Komponent bol úspešne upravený.',
    ],

    'restore' => [
        'error' => 'Komponent nebol obnovený, skúste to znova',
        'success' => 'Komponent bol úspešne obnovený.',
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete zmazať tento komponent?',
        'error' => 'Nastal problém pri mazani komponentu. Prosím skúste znovu.',
        'success' => 'Komponent bol úspešne zmazaný.',
        'error_qty' => 'Niektoré komponenty tohto typu sú stále odovzdané. Prosím prevezmite ich a skúste to znova.',
    ],

    'checkout' => [
        'error' => 'Komponent nebol priradený, prosím skúste znovu',
        'success' => 'Komponent bol úspešne priradený.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'unavailable' => 'Nedostatok komponentov: :remaining skladom, :requested vyžiadaných ',
    ],

    'bulk' => [
        'no_components_selected' => 'Neboli vybrané žiadne komponenty.',
        'no_action_selected' => 'Vyberte akciu z rozbaľovacieho zoznamu.',
        'no_fields_updated' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
        'skipped_unauthorized' => ':count komponentov bolo preskočených (nemáte oprávnenie ich aktualizovať).',

        'update' => [
            'success' => ':count komponent bol aktualizovaný.|:count komponentov bolo aktualizovaných.',
            'none_updated' => 'Vybraný komponent sa nepodarilo aktualizovať. Podrobnosti nájdete v chybách nižšie.|Žiadny z vybraných komponentov sa nepodarilo aktualizovať. Podrobnosti nájdete v chybách nižšie.',
        ],

        'delete' => [
            'success' => ':count komponent bol odstránený.|:count komponentov bolo odstránených.',
            'partial_success' => ':count komponent bol odstránený. Informácie o ostatných nájdete v chybách nižšie.|:count komponentov bolo odstránených. Informácie o ostatných nájdete v chybách nižšie.',
        ],

        'restore' => [
            'success' => ':count komponent bol obnovený.|:count komponentov bolo obnovených.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'Neboli vybrané žiadne odovzdané komponenty.',
            'success' => ':unit_count kus bol prevzatý späť z :count odovzdania.|:unit_count kusov bolo prevzatých späť z :count odovzdaní.',
        ],
    ],

    'checkin' => [
        'error' => 'Komponent nebol prevzatý, skúste znovu',
        'success' => 'Komponent bol úspešne prevzatý.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
    ],

];
