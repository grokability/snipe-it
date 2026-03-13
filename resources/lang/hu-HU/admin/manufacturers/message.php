<?php

return array(

    'support_url_help' => 'A <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code> és <code>{MODEL_NAME}</code> változók használhatók a URL-ben, hogy ezek az értékek automatikusan kitöltődjenek az eszközök megtekintésekor – például: https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Gyártó nem létezik.',
    'assoc_users'	 => 'Ez a gyártó jelenleg legalább egy modellel társítva van, így nem lehet törölni. Kérjük, frissítse a modellt úgy, hogy ne hivatkozzon erre a gyártóra, és próbálkozzon újra. ',

    'create' => array(
        'error'   => 'Gyártó nem jött létre, próbálkozz újra.',
        'success' => 'Gyártó sikeresen létrehozva.'
    ),

    'update' => array(
        'error'   => 'Gyártó nem lett frissítve, próbálkozz újra',
        'success' => 'Gyártó sikeresen frissítve.'
    ),

    'restore' => array(
        'error'   => 'A gyártó nem lett visszaállítva, próbálja újra',
        'success' => 'Gyártó sikeresen visszaállítva.'
    ),

    'delete' => array(
        'confirm' => 'Biztosan törölni szeretnéd ezt a gyártót?',
        'error'   => 'Probléma adódott a gyártó törlése közben. Próbálkozz újra.',
        'success'                => 'A gyártó sikeresen törlésre került.',
        'bulk_success'           => 'A gyártók sikeresen törlésre kerültek.',
        'partial_success'        => 'A gyártó sikeresen törlésre került. További információk alább. | :count gyártó sikeresen törlésre került. További információk alább.',
    )

);
