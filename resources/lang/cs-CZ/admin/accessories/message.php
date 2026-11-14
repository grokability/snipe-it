<?php

return [

    'does_not_exist' => 'Příslušenství [:id] neexistuje.',
    'not_found' => 'Toto příslušenství nebylo nalezeno.',
    'assoc_users' => 'Tato kategorie má nyní :count položek k předání uživatelům. Zkontrolujte převzetí příslušenství a zkuste to znovu. ',

    'create' => [
        'error' => 'Doplněk nebyl vytvořen, prosím zkuste to znovu.',
        'success' => 'Doplněk byl úspěšně vytvořen.',
    ],

    'update' => [
        'error' => 'Doplněk nebyl upraven, prosím zkuste to znovu',
        'success' => 'Doplněk byl úspěšně upraven.',
    ],

    'delete' => [
        'confirm' => 'Jste si jisti, že chcete odstranit toto příslušenství?',
        'error' => 'Vyskytl se problém při mazání kategorie. Zkuste to znovu prosím.',
        'success' => 'Příslušenství bylo úspěšně odstraněno.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Příslušenství nebylo převzato, zkuste to znovu',
        'success' => 'Příslušenství úspěšně předáno.',
        'unavailable' => 'Příslušenství nelze vydat. Zkontrolujte skladové zásoby.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to znovu.',
        'checkout_qty' => [
            'lte' => 'V současné době je k dispozici pouze jeden doplněk tohoto příslušenství a snažíte se vydat :checkout_qty. Upravte prosím množství k výdeji nebo celkový počet dostupných doplňků a zkuste to znovu.
K dispozici je celkem :number_currently_remaining doplňků, přičemž se pokoušíte vydat :checkout_qty. Upravte prosím množství k výdeji nebo celkový počet a zkuste to znovu.',
        ],

    ],

    'checkin' => [
        'error' => 'Příslušenství nebylo převzato, zkuste to znovu',
        'success' => 'Příslušenství úspěšně předáno.',
        'user_does_not_exist' => 'Neplatný uživatel. Zkuste to znovu.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Žádné pole nebyly změněny, takže nic nebylo aktualizováno.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
