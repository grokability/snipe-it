<?php

return array(

    'does_not_exist' => 'Hely nem létezik.',
    'assoc_users'    => 'Ez a helyszín jelenleg nem törölhető, mert legalább egy tétel vagy felhasználó alapértelmezett helyszíne, eszközök vannak hozzá rendelve, vagy egy másik helyszín szülőhelye. Kérjük, frissítse az adatait úgy, hogy már ne hivatkozzanak erre a helyszínre, majd próbálja újra ',
    'assoc_assets'	 => 'Ez a hely jelenleg legalább egy eszközhöz társítva, és nem törölhető. Frissítse eszközeit, hogy ne hivatkozzon erre a helyre, és próbálja újra.',
    'assoc_child_loc'	 => 'Ez a hely jelenleg legalább egy gyermek helye szülője, és nem törölhető. Frissítse tartózkodási helyeit, hogy ne hivatkozzon erre a helyre, és próbálja újra.',
    'assigned_assets' => 'Hozzárendelt eszközök',
    'current_location' => 'Jelenlegi hely',
    'open_map' => 'Megnyitás a :map_provider_icon térképen',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'A helyszín nem jött létre, próbálkozzon újra.',
        'success' => 'A helyszín sikeresen létrehozva.'
    ),

    'update' => array(
        'error'   => 'A helyszín nem frissült, próbálkozzon újra',
        'success' => 'A helyszín sikeresen frissült.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Biztosan törölni szeretné ezt a helyet?',
        'error'   => 'Hiba történt a helyszín törlése közben. Kérlek próbáld újra.',
        'success' => 'A helyszínt sikeresen törölték.'
    )

);
