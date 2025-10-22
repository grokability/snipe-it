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
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
