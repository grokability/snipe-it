<?php

return [
    'does_not_exist' => 'Spoločnosť neexistuje.',
    'deleted' => 'Zmazané spoločnosti',
    'assoc_users' => 'Táto spoločnosť je momentálne priradená minimálne k jednej ďalšej položke (lokalita, používateľ, licencia, majetok atď.) v Snipe-IT, preto ju nie je možné odstrániť. Upravte príslušné záznamy tak, aby na túto spoločnosť už neodkazovali, a skúste to znova. ',
    'create' => [
        'error' => 'Spoločnosť nebola pridaná, prosím skúste znovu.',
        'success' => 'Spoločnosť bola úspešne pridaná.',
    ],
    'update' => [
        'error' => 'Spoločnosť nebola upravená, prosím skúste znovu',
        'success' => 'Spoločnosť bola úspešne upravená.',
    ],
    'delete' => [
        'confirm' => 'Ste si istý, že chcete zmazať túto spoločnosť?',
        'error' => 'Pri mazaní spoločnosti nastal problém. Skúste znovu.',
        'success' => 'Spoločnosť bola úspešne zmazaná.',
        'bulk_success' => 'Spoločnosť bola úspešne odstránená.|:count spoločností bolo úspešne odstránených.',
        'partial_success' => 'Spoločnosť bola úspešne odstránená. Ďalšie informácie nájdete nižšie. | :count spoločností bolo úspešne odstránených. Ďalšie informácie nájdete nižšie.',
    ],
    'webhook_fields_require_superuser' => 'Polia konfigurácie webhooku (:fields) môže upravovať iba superpoužívateľ.',
];
