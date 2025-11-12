<?php

return array(

    'does_not_exist' => 'Lokalizacja nie istnieje.',
    'assoc_users'    => 'Tej lokalizacji nie można obecnie usunąć, ponieważ istnieje co najmniej jeden środek lub użytkownik przypisany do niej bądź jest to lokalizacja nadrzędna względem innej lokalizacji. Zaktualizuj problematyczne odwołania i spróbuj ponownie.',
    'assoc_assets'	 => 'Lokalizacja obecnie jest skojarzona z minimum jednym środkiem i nie może zostać usunięta. Uaktualnij właściwości środków tak, aby nie było relacji z tą lokalizacją i spróbuj ponownie. ',
    'assoc_child_loc'	 => 'Lokalizacja obecnie jest rodzicem minimum jeden innej lokalizacji i nie może zostać usunięta. Uaktualnij właściwości lokalizacji tak aby nie było relacji z tą lokalizacją i spróbuj ponownie. ',
    'assigned_assets' => 'Przypisane środki',
    'current_location' => 'Bieżąca lokalizacja',
    'open_map' => 'Otwórz w mapach :map_provider_icon',
    'deleted_warning' => 'Ta lokalizacja została usunięta. Przywróć lokalizację przed wprowadzeniem zmian.',


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
