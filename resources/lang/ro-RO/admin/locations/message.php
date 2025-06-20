<?php

return array(

    'does_not_exist' => 'Locația nu există.',
    'assoc_users'    => 'Această locație nu poate fi ștearsă în prezent deoarece este locația principală pentru cel puțin un activ sau utilizator, are active alocate sau este locația părinte a unei alte locații. Te rog să actualizezi înregistrările pentru a nu mai face referire la această locație și să încerci din nou.',
    'assoc_assets'   => 'Această locație este asociată în prezent cu cel puțin un activ și nu poate fi ștearsă. Te rog să actualizezi activele pentru a nu mai face referire la această locație și să încerci din nou.',
    'assoc_child_loc' => 'Această locație este în prezent părintele a cel puțin unei locații copil și nu poate fi ștearsă. Te rog să actualizezi locațiile pentru a nu mai face referire la această locație și să încerci din nou.',
    'assigned_assets' => 'Active alocate',
    'current_location' => 'Locația curentă',
    'open_map' => 'Deschide în :map_provider_icon Hărți',

    'create' => array(
        'error'   => 'Locația nu a fost creată, te rog să încerci din nou.',
        'success' => 'Locația a fost creată cu succes.'
    ),

    'update' => array(
        'error'   => 'Locația nu a fost actualizată, te rog să încerci din nou',
        'success' => 'Locația a fost actualizată cu succes.'
    ),

    'restore' => array(
        'error'   => 'Locația nu a fost restaurată, te rog să încerci din nou',
        'success' => 'Locația a fost restaurată cu succes.'
    ),

    'delete' => array(
        'confirm'   => 'Ești sigur că dorești să ștergi această locație?',
        'error'   => 'A apărut o problemă la ștergerea locației. Te rog să încerci din nou.',
        'success' => 'Locația a fost ștearsă cu succes.'
    )

);