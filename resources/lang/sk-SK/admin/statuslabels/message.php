<?php

return [

    'does_not_exist' => 'Stav neexistuje.',
    'deleted_label' => 'Odstránený stav',
    'assoc_assets'	 => 'Tento stav je priradený minimálne jednému mejtku, preto nemôže byť odstránený. Prosím odstráňte referenciu na tento stav z príslušného majetku a skúste znovu. ',

    'create' => [
        'error'   => 'Stav nebol vytovrený, prosím skúste znovu.',
        'success' => 'Stav bol úspešne vytvorený.',
    ],

    'update' => [
        'error'   => 'Stav nebol upravený, prosím skuste znovu',
        'success' => 'Stav bol úspešne upravený.',
    ],

    'delete' => [
        'confirm'   => 'Ste si istý, že chcete odstrániť tento stav?',
        'error'   => 'Pri odstraňovaní stavu sa vyskytla chyba. Skúste prosím znovu.',
        'success' => 'Stav bol úspečne odstránený.',
    ],

    'help' => [
        'undeployable'   => 'Tieto položky majetku nemôžu byť nikomu priradené.',
        'deployable'   => 'Tieto majetky môžu byť priradené. Akonáhle sú priradené nadobudnú stav <i class="fas fa-circle text-blue"></i> <strong>Priradené</strong>.',
        'archived'   => 'Tieto majetky nemôžu byť priradené, budú zobrazené iba vo výpise Archovavné. Tento stav je vhodný, ak si chcete ponechať informácie o predchádzajúcom majetku pre historické účely alebo prípravu rozpočtu, ale zároveň ich nechcete mať zobrazené v prehľade aktuálneho majetku.',
        'pending'   => 'Tieto majetky nemôžu byť ešte nikomu priradené. Často sa používa na predmety, ktoré čakajú na opravu ale očakáva sa ich návrat do obehu.',
    ],

];
