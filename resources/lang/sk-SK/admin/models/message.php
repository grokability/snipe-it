<?php

return [

    'deleted' => 'Odstránený model majetku',
    'does_not_exist' => 'Model neexistuje.',
    'no_association' => 'VAROVANIE! Model majetku pre túto položku je neplatný alebo neexistuje!',
    'no_association_fix' => 'Tento stav môže spôsobiť nepredvídateľné problémy. Priraďte danému majetku správny model.',
    'assoc_users' => 'Tento model je použitý v jednom alebo viacerých majetkoch, preto nemôže byť odstránený. Prosím odstráňte príslušný majetok a skúste odstrániť znovu. ',
    'invalid_category_type' => 'Táto kategória musí byť kategóriou majetku.',

    'create' => [
        'error' => 'Model nebol vytovrený, prosím skúste znovu.',
        'success' => 'Model bol úspešne vytvorený.',
        'duplicate_set' => 'Model majetku s týmto názvom, výrobcom a číslom modelu už existuje.',
    ],

    'update' => [
        'error' => 'Model nebol upravený, prosím skúste znovu',
        'success' => 'Model bol úspešne upravený.',
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete odstrániť tento model majetku?',
        'error' => 'Pri odstraňovaní modelu sa vyskytla chyba. Skúste prosím znovu.',
        'success' => 'Model bol úspešne odstránený.',
    ],

    'restore' => [
        'error' => 'Model nebol obnovený, prosím skúste znovu',
        'success' => 'Model bol obnovený úspešne.',
    ],

    'bulkedit' => [
        'error' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
        'success' => 'Model bol úspešne upravený. |:model_count modelov bolo úspešne upravených.',
        'warn' => 'Chystáte sa upraviť nastavenia nasledovného modelu:|Chystáte sa upraviť nastavenia nasledovných :model_count modelov:',

    ],

    'bulkdelete' => [
        'error' => 'Neboli vybrané ziadne modely, preto nebolo nič odmazané.',
        'nothing_deletable' => 'Žiadny z vybraných modelov nie je možné odstrániť, pretože sú k nim stále priradené položky majetku.',
        'success' => 'Model zmazaný!|:success_count_models modelov zmazaných!',
        'success_partial' => ':success_count model(y) odstránené, avšak :fail_count nebolo možné odstrániť pretože stále majú priradené majetky.',
    ],

    'merge' => [
        'min_two' => 'Na zlúčenie vyberte aspoň dva modely.',
        'no_target' => 'Pred zlúčením vyberte, ktorý model sa má zachovať.',
        'not_found' => 'Jeden alebo viacero vybraných modelov sa nepodarilo načítať. Obnovte zoznam modelov a skúste to znova.',
        'information' => 'Chystáte sa zlúčiť :count modelov. Vyberte model, ktorý chcete zachovať. Všetky položky majetku priradené k ostatným modelom budú preradené k vybranému modelu a zdrojové modely potom budú odstránené.',
        'warning' => 'Túto akciu nie je možné vrátiť späť. Preradený majetok prevezme kategóriu, skupinu polí a nastavenia odpisov zachovaného modelu.',
        'pick_target' => 'Ktorý model chcete zachovať?',
        'success' => 'Do modelu ":target" boli zlúčené modely (:source_count). Preradené boli položky majetku (:asset_count).',
    ],

];
