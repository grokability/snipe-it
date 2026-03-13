<?php

return array(

    'deleted' => 'Odstránený model majetku',
    'does_not_exist' => 'Model neexistuje.',
    'no_association' => 'VAROVANIE! Model majetku pre túto položku je neplatný alebo neexistuje!',
    'no_association_fix' => 'Tento stav môže spôsobiť nepredvídateľné problémy. Priraďte danému majetku správny model.',
    'assoc_users'	 => 'Tento model je použitý v jednom alebo viacerých majetkoch, preto nemôže byť odstránený. Prosím odstráňte príslušný majetok a skúste odstrániť znovu. ',
    'invalid_category_type' => 'Táto kategória musí byť kategóriou majetku.',

    'create' => array(
        'error'   => 'Model nebol vytovrený, prosím skúste znovu.',
        'success' => 'Model bol úspešne vytvorený.',
        'duplicate_set' => 'Model majetku s týmto názvom, výrobcom a číslom modelu už existuje.',
    ),

    'update' => array(
        'error'   => 'Model nebol upravený, prosím skúste znovu',
        'success' => 'Model bol úspešne upravený.',
    ),

    'delete' => array(
        'confirm'   => 'Ste si istý, že chcete odstrániť tento model majetku?',
        'error'   => 'Pri odstraňovaní modelu sa vyskytla chyba. Skúste prosím znovu.',
        'success' => 'Model bol úspešne odstránený.'
    ),

    'restore' => array(
        'error'   		=> 'Model nebol obnovený, prosím skúste znovu',
        'success' 		=> 'Model bol obnovený úspešne.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
        'success' 		=> 'Model bol úspešne upravený. |:model_count modelov bolo úspešne upravených.',
        'warn'          => 'Chystáte sa upraviť nastavenia nasledovného modelu:|Chystáte sa upraviť nastavenia nasledovných :model_count modelov:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Neboli vybrané ziadne modely, preto nebolo nič odmazané.',
        'success' 		    => 'Model zmazaný!|:success_count_models modelov zmazaných!',
        'success_partial' 	=> ':success_count model(y) odstránené, avšak :fail_count nebolo možné odstrániť pretože stále majú priradené majetky.'
    ),

);
