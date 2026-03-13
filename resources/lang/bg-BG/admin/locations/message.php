<?php

return array(

    'does_not_exist' => 'Местоположението не съществува.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Местоположението е свързано с поне един актив и не може да бъде изтрито. Моля, актуализирайте активите, така че да не са свързани с това местоположение и опитайте отново. ',
    'assoc_child_loc'	 => 'В избраното местоположение е присъединено едно или повече местоположения. Моля преместете ги в друго и опитайте отново.',
    'assigned_assets' => 'Изписани Активи',
    'current_location' => 'Текущо местоположение',
    'open_map' => 'Отвори в :map_provider_icon карти',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Местоположението не е създадено. Моля, опитайте отново.',
        'success' => 'Местоположението е създадено.'
    ),

    'update' => array(
        'error'   => 'Местоположението не е обновено. Моля, опитайте отново',
        'success' => 'Местоположението е обновено.'
    ),

    'restore' => array(
        'error'   => 'Местоположението не беше възстановено, моля опитайте отново',
        'success' => 'Местоположението е възстановено.'
    ),

    'delete' => array(
        'confirm'   	=> 'Сигурни ли сте, че искате да изтриете това местоположение?',
        'error'   => 'Възникна проблем при изтриване на местоположението. Моля, опитайте отново.',
        'success' => 'Местоположението е изтрито.'
    )

);
