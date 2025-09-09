<?php

return array(

    'deleted' => 'Usunięty dostawca',
    'does_not_exist' => 'Dostawca nie istnieje.',


    'create' => array(
        'error'   => 'Dostawca nie został utworzony, spróbuj ponownie.',
        'success' => 'Dostawca utworzony pomyślnie.'
    ),

    'update' => array(
        'error'   => 'Dostawca nie został zaktualizowany, spróbuj ponownie',
        'success' => 'Dostawca zaktualizowany pomyślnie.'
    ),

    'delete' => array(
        'confirm'   => 'Czy na pewno usunąć tego dostawcę?',
        'error'   => 'Podczas usuwania dostawcy napotkano błąd. Spróbuj ponownie.',
        'success' => 'Dostawca usunięty pomyślnie.',
        'assoc_assets'	 => 'Ten dostawca jest obecnie powiązany z :asset_count aktywami i nie może zostać usunięty. Zaktualizuj aktywa aby nie były z nim powiązane i spróbuj ponownie. ',
        'assoc_licenses'	 => 'Ten dostawca jest obecnie powiązany z :licenses_count licencjami i nie może zostać usunięty. Zaktualizuj licencje tak aby do niego nie nawiązywały i spróbuj ponownie. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
