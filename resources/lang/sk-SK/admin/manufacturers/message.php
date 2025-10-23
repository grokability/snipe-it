<?php

return array(

    'support_url_help' => 'Premenné <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> môžu byť použité v URL pre automatické vyplnenie týchto hodnôt pri prehliadaní aktív - napríklad https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Výrobca neexistuje.',
    'assoc_users'	 => 'Tento výrobca je uvedený minimálne pri jednom modely a preto nemôže byť zmazaný. Prosím upravte vaše modely, aby už nevyužívali referenciu na tohto výrobcu a skúste znovu. ',

    'create' => array(
        'error'   => 'Výrobca nebol pridaný, skúste znovu.',
        'success' => 'Výrobca bol úspešne pridaný.'
    ),

    'update' => array(
        'error'   => 'Výrobca nebol aktualizovaný, skúste znovu',
        'success' => 'Výrobca bol úspešne aktualizovaný.'
    ),

    'restore' => array(
        'error'   => 'Výrobca nebol obnovený, skúste znovu',
        'success' => 'Výrobca bol úspešne obnovený.'
    ),

    'delete' => array(
        'confirm' => 'Ste si istí, že chete zmazať tohto výrobcu?',
        'error'   => 'Pri mazaní výrobcu nastala chyba. Skúste znovu.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
