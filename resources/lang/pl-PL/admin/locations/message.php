<?php

return array(

    'does_not_exist' => 'Lokalizacja nie istnieje.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Lokalizacja obecnie jest skojarzona z minimum jednym aktywem i nie może zostać usunięta. Uaktualnij właściwości aktywów tak aby nie było relacji z tą lokalizacją i spróbuj ponownie. ',
    'assoc_child_loc'	 => 'Lokalizacja obecnie jest rodzicem minimum jeden innej lokalizacji i nie może zostać usunięta. Uaktualnij właściwości lokalizacji tak aby nie było relacji z tą lokalizacją i spróbuj ponownie. ',
    'assigned_assets' => 'Przypisane aktywa',
    'current_location' => 'Bieżąca lokalizacja',
    'open_map' => 'Otwórz w mapach :map_provider_icon',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


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
