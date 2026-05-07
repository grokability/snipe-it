<?php

return [

    'deleted' => 'Panaikintas turto modelis',
    'does_not_exist' => 'Tokio modelio nėra.',
    'no_association' => 'ĮSPĖJIMAS! Tokio turto modelio nėra arba jis neteisingas!',
    'no_association_fix' => 'Tai sugadins dalykus keistais ir siaubingais būdais. Nedelsdami redaguokite šį turtą ir priskirkite jam modelį.',
    'assoc_users' => 'Šis modelis šiuo metu susietas su bent vienu turto vienetu, todėl negali būti panaikintas. Panaikinkite šį turtą, tuomet vėl bandykite panaikinti modelį. ',
    'invalid_category_type' => 'Kategorija turi būti turto kategorija.',

    'create' => [
        'error' => 'Modelis nebuvo panaikintas, bandykite dar kartą.',
        'success' => 'Modelis sėkmingai sukurtas.',
        'duplicate_set' => 'Turto modelis su tokiu pavadinimu, gamintoju ir modelio numeriu jau yra.',
    ],

    'update' => [
        'error' => 'Modelis nebuvo atnaujintas, bandykite dar kartą',
        'success' => 'Modelis sėkmingai atnaujintas.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite panaikinti šį turto modelį?',
        'error' => 'Bandant panaikinti modelį įvyko klaida. Bandykite dar kartą.',
        'success' => 'Modelis sėkmingai panaikintas.',
    ],

    'restore' => [
        'error' => 'Modelis nebuvo atkurtas, bandykite dar kartą',
        'success' => 'Modelis sėkmingai atkurtas.',
    ],

    'bulkedit' => [
        'error' => 'Jokie laukai nebuvo pakeisti, todėl niekas nebuvo atnaujinta.',
        'success' => 'Modelis sėkmingai atnaujintas. |:model_count modeliai sėkmingai atnaujinti.',
        'warn' => 'Ketinate atnaujinti šio modelio ypatybes:|Ketinate redaguoti šių :model_count modelių ypatybes:',

    ],

    'bulkdelete' => [
        'error' => 'Nebuvo pasirinktas joks modelis, todėl niekas nebuvo panaikinta.',
        'success' => 'Modelis panaikintas! :success_count modeliai panaikinti!',
        'success_partial' => 'Panaikinti modeliai – :success_count, tačiau dar :fail_count nepavyko panaikinti, nes vis dar yra su jais susieto turto.',
    ],

];
