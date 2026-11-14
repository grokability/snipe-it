<?php

return [

    'does_not_exist' => 'Lokalita neexistuje.',
    'assoc_users' => 'Túto lokalitu momentálne nie je možné odstrániť, pretože je použitá pre aspoň jednu položku alebo používateľa, má priradené majetky alebo je nadradenou lokalitou inej lokality. Aktualizujte svoje záznamy tak, aby už neodkazovali na túto lokalitu a skúste to znova.',
    'assoc_assets' => 'Táto lokalita je priradená minimálne jednému majetku, preto nemôže byť odstránená. Prosím odstráňte referenciu na túto lokalitu z príslušného majetku a skúste znovu. ',
    'assoc_child_loc' => 'Táto lokalita je nadradenou minimálne jednej podradenej lokalite, preto nemôže byť odstránená. Prosím odstráňte referenciu s príslušnej lokality a skúste znovu. ',
    'assigned_assets' => 'Priradené položky majetku',
    'current_location' => 'Aktuálna lokalita',
    'deleted_warning' => 'Táto lokalita bolo odstránené. Pred vykonaním akýchkoľvek zmien ju obnovte.',

    'create' => [
        'error' => 'Lokalita nebola vytvorená, skúste prosím znovu.',
        'success' => 'Lokalita bola úspešne vytovrená.',
    ],

    'update' => [
        'error' => 'Lokalita nebola aktualizovaná, skúste prosím znovu',
        'success' => 'Lokalita bola úspešne upravená.',
    ],

    'restore' => [
        'error' => 'Lokalita nebola obnovená, prosím skúste znovu',
        'success' => 'Lokalita bola úspešne obnovená.',
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete odstrániť túto lokalitu?',
        'error' => 'Pri odstraňovaní lokality nastala chyba. Skúste prosím znovu.',
        'success' => 'Lokalita bola úspešne odstránená.',
    ],

    'bulkedit' => [
        'error' => 'Neboli zmenené žiadne polia, preto nebolo nič aktualizované.',
        'success' => 'Lokalita bola úspešne aktualizovaná.|:count lokalít bolo úspešne aktualizovaných.',
        'warn' => 'Upravte polia nižšie, ak chcete aktualizovať túto lokalitu. Polia, ktoré necháte prázdne, sa v lokalite nezmenia.|Upravte polia nižšie, ak chcete aktualizovať vybrané lokality v počte :count. Polia, ktoré necháte prázdne, sa v žiadnej z nich nezmenia.',
        'show_selected' => '1 vybraná lokalita|:count vybraných lokalít',
        'company_scope_mismatch_partial' => 'Spoločnosť nebola zmenená pri 1 lokalite, pretože položky alebo používatelia v tejto lokalite patria rôznym spoločnostiam. Najprv ich aktualizujte alebo presuňte.|Spoločnosť nebola zmenená pri :count lokalitách, pretože položky alebo používatelia v týchto lokalitách patria rôznym spoločnostiam. Najprv ich aktualizujte alebo presuňte.',
        'company_scope_mismatch_all' => 'Žiadne lokality neboli preradené. Požadovaná spoločnosť nezodpovedá položkám ani používateľom vo vybranej lokalite.|Žiadne lokality neboli preradené. Požadovaná spoločnosť nezodpovedá položkám ani používateľom v žiadnej z :count vybraných lokalít.',
        'parent_company_mismatch_partial' => 'Nadradená lokalita ani spoločnosť nebola zmenená pri 1 lokalite, pretože by lokalita patrila inej spoločnosti ako jej nadradená lokalita.|Nadradená lokalita ani spoločnosť nebola zmenená pri :count lokalitách, pretože by tieto lokality patrili inej spoločnosti ako ich nadradená lokalita.',
        'parent_company_mismatch_all' => 'Neboli uložené žiadne zmeny. Pri požadovanej nadradenej lokalite alebo spoločnosti by lokalita patrila inej spoločnosti ako jej nadradená lokalita.|Neboli uložené žiadne zmeny. Pri požadovanej nadradenej lokalite alebo spoločnosti by každá z :count vybraných lokalít patrila inej spoločnosti ako jej nadradená lokalita.',
    ],

];
