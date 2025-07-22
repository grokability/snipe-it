<?php

return array(

    'does_not_exist' => 'Lokalizacja nie istnieje.',
    'assoc_users'    => 'Ta lokalizacja nie jest obecnie usuwana, ponieważ jest to lokalizacja rekordu dla co najmniej jednego zasobu lub użytkownika, posiada przypisane do niego aktywa lub jest miejscem macierzystym innej lokalizacji. Zaktualizuj swoje rekordy, aby nie odnosić się już do tej lokalizacji i spróbuj ponownie ',
    'assoc_assets'	 => 'Lokalizacja obecnie jest skojarzona z minimum jednym aktywem i nie może zostać usunięta. Uaktualnij właściwości aktywów tak aby nie było relacji z tą lokalizacją i spróbuj ponownie. ',
    'assoc_child_loc'	 => 'Lokalizacja obecnie jest rodzicem minimum jeden innej lokalizacji i nie może zostać usunięta. Uaktualnij właściwości lokalizacji tak aby nie było relacji z tą lokalizacją i spróbuj ponownie. ',
    'assigned_assets' => 'Przypisane aktywa',
    'current_location' => 'Bieżąca lokalizacja',
    'open_map' => 'Otwórz w mapach :map_provider_icon',


    'create' => array(
        'error'   => 'Lokalizacja nie została stworzona. Spróbuj ponownie.',
        'success' => 'Lokalizacja stworzona pomyślnie.'
    ),

    'update' => array(
        'error'   => 'Lokalizacja nie została zaktualizowana, spróbuj ponownie',
        'success' => 'Lokalizacja zaktualizowana pomyślnie.'
    ),

    'restore' => array(
        'error'   => 'Lokalizacja nie została przywrócona, spróbuj ponownie',
        'success' => 'Lokalizacja została przywrócona pomyślnie.'
    ),

    'delete' => array(
        'confirm'   	=> 'Czy na pewno usunąć wybraną lokalizację?',
        'error'   => 'Podczas usuwania lokalizacji napotkano problem. Spróbuj ponownie.',
        'success' => 'Lokalizacja usunięta pomyślnie.'
    )

);
