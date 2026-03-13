<?php

return array(

    'deleted' => 'Panaikintas turto modelis',
    'does_not_exist' => 'Tokio modelio nėra.',
    'no_association' => 'ĮSPĖJIMAS! Tokio turto modelio nėra arba jis neteisingas!',
    'no_association_fix' => 'Tai sugadins dalykus keistais ir siaubingais būdais. Nedelsdami redaguokite šį turtą ir priskirkite jam modelį.',
    'assoc_users'	 => 'Šis modelis šiuo metu susietas su bent vienu turto vienetu, todėl negali būti panaikintas. Panaikinkite šį turtą, tuomet vėl bandykite panaikinti modelį. ',
    'invalid_category_type' => 'Kategorija turi būti turto kategorija.',

    'create' => array(
        'error'   => 'Modelis nebuvo panaikintas, bandykite dar kartą.',
        'success' => 'Modelis sukurtas sėkmingai.',
        'duplicate_set' => 'Turto modelis su tokiu pavadinimu, gamintoju ir modelio numeriu jau yra.',
    ),

    'update' => array(
        'error'   => 'Modelis nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Modelis atnaujintas sėkmingai.',
    ),

    'delete' => array(
        'confirm'   => 'Ar tikrai norite panaikinti šį turto modelį?',
        'error'   => 'Bandant panaikinti modelį įvyko klaida. Bandykite dar kartą.',
        'success' => 'Modelis panaikintas sėkmingai.'
    ),

    'restore' => array(
        'error'   		=> 'Modelis nebuvo atkurtas, bandykite dar kartą',
        'success' 		=> 'Modelis atkurtas sėkmingai.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'success' 		=> 'Modelis atnaujintas sėkmingai. |:model_count modeliai atnaujinti sėkmingai.',
        'warn'          => 'Ketinate atnaujinti šio modelio ypatybes:|Ketinate redaguoti šių :model_count modelių ypatybes:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Nebuvo pasirinktas joks modelis, todėl niekas nebuvo panaikinta.',
        'success' 		    => 'Modelis panaikintas! :success_count modeliai panaikinti!',
        'success_partial' 	=> 'Panaikinti modeliai – :success_count, tačiau dar :fail_count nepavyko panaikinti, nes vis dar yra su jais susieto turto.'
    ),

);
