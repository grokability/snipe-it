<?php

return [

    'does_not_exist' => 'A státusz-címke nem létezik.',
    'deleted_label' => 'Törölt státusz-címke',
    'assoc_assets'	 => 'Ez az Állapotjelző jelenleg legalább egy Assethez társítva, és nem törölhető. Kérjük, frissítse eszközeit, hogy ne hivatkozzon erre az állapotra, és próbálja újra.',

    'create' => [
        'error'   => 'A státusz-címke nem jött létre, próbálkozzon újra.',
        'success' => 'A státusz címke sikeresen létrehozva.',
    ],

    'update' => [
        'error'   => 'A státusz-címke nem frissült, próbálkozzon újra',
        'success' => 'Az állapotjelző sikeresen frissült.',
    ],

    'delete' => [
        'confirm'   => 'Biztos benne, hogy törölni szeretné ezt az állapotjelzőt?',
        'error'   => 'Hiba történt az Állapotjelző törlésével. Kérlek próbáld újra.',
        'success' => 'Az Állapotjelző sikeresen törölve lett.',
    ],

    'help' => [
        'undeployable'   => 'Ezeket az eszközöket senkihez nem lehet hozzárendelni.',
        'deployable'   => 'Ezek az eszközök kiadásra készek. Ha kiadásra kerülnek, akkor a <i class="fas fa-circle text-blue"></i> <strong>Kiadva</strong> állapotot veszik fel.',
        'archived'   => 'Ezeket az eszközöket nem lehet kijelölni, és csak az Archivált nézetben jelenhetnek meg. Ez hasznos lehet az eszközökkel kapcsolatos információk megőrzésére költségvetés / történelmi célokra, de a napi eszközlista megtartásával.',
        'pending'   => 'Ezeket az eszközöket még nem lehet bárkihez hozzárendelni, gyakran azokat a tételeket használják, amelyek ki vannak javítva, de várhatóan visszatérnek a forgalomba.',
    ],

];
