<?php

return array(

    'deleted' => 'Eyða tegund eigna',
    'does_not_exist' => 'Tegund ekki til.',
    'no_association' => 'VIÐVÖRUN! Eignategund fyrir þennan hlut er ógilt eða vantar!',
    'no_association_fix' => 'Þetta mun brjóta hlutina á undarlegan og hræðilegan hátt. Breyttu þessari eign núna til að úthluta henni fyrirmynd.',
    'assoc_users'	 => 'Þessi tegund er sem stendur tengt einni eða fleiri eignum og ekki er hægt að eyða því. Vinsamlegast eyddu eignunum og reyndu síðan að eyða aftur. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Tegundin var ekki búið til, vinsamlegast reyndu aftur.',
        'success' => 'Tegund búin til.',
        'duplicate_set' => 'Eignategund með þessu nafni, framleiðanda og tegundarnúmeri er þegar til.',
    ),

    'update' => array(
        'error'   => 'Tegund var ekki uppfærð, vinsamlegast reyndu aftur',
        'success' => 'Tegund uppfærð.',
    ),

    'delete' => array(
        'confirm'   => 'Ertu viss um að þú viljir eyða þessu eignategund?',
        'error'   => 'Vandamál kom upp við að eyða tegundinni. Vinsamlegast reyndu aftur.',
        'success' => 'Tegund var eytt.'
    ),

    'restore' => array(
        'error'   		=> 'Tegund var ekki endurheimt, vinsamlegast reyndu aftur',
        'success' 		=> 'Tegund endurheimt.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Engum reitum var breytt, svo ekkert var uppfært.',
        'success' 		=> 'Tegund uppfært. |:model_count Tegundir uppfærð.',
        'warn'          => 'Þú ert að fara að uppfæra eiginleika eftirfarandi model:|Þú ert að fara að breyta eiginleikum eftirfarandi :model_count tegunda:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Engar tegundir voru valdar og því var engu eytt.',
        'success' 		    => 'Tegund eytt!|:success_count tegundum eytt!',
        'success_partial' 	=> ':success_count tegund(um) var eytt, hins vegar var ekki hægt að eyða :fail_count vegna þess að þau hafa enn eignir tengdar þeim.'
    ),

);
