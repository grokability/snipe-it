<?php

return array(

    'deleted' => 'Izbrisan model sredstva',
    'does_not_exist' => 'Model ne obstaja.',
    'no_association' => 'OPOZORILO! Model sredstva za ta element je neveljaven ali manjka!',
    'no_association_fix' => 'To bo zrušilo stvari na čudne in grozljive načine. Uredite to sredstvo zdaj in mu dodelite model.',
    'assoc_users'	 => 'Ta model je trenutno povezan z enim ali več sredstvi in ​​ga ni mogoče izbrisati. Prosimo, izbrišite sredstva in poskusite zbrisati znova. ',
    'invalid_category_type' => 'Ta kategorija mora biti kategorija sredstva.',

    'create' => array(
        'error'   => 'Model ni bil ustvarjen, poskusite znova.',
        'success' => 'Model je bil uspešno ustvarjen.',
        'duplicate_set' => 'Model sredstva s tem imenom, proizvajalcem in številko modela že obstaja.',
    ),

    'update' => array(
        'error'   => 'Model ni bil posodobljen, poskusite znova',
        'success' => 'Model je bil uspešno posodobljen.',
    ),

    'delete' => array(
        'confirm'   => 'Ali ste prepričani, da želite izbrisati ta model sredstva?',
        'error'   => 'Prišlo je do težave pri brisanju modela. Prosim poskusite ponovno.',
        'success' => 'Model je bil uspešno izbrisan.'
    ),

    'restore' => array(
        'error'   		=> 'Model ni bil obnovljen, poskusite znova',
        'success' 		=> 'Model je bil uspešno obnovljen.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Polja niso bila spremenjena, nič ni posodobljeno.',
        'success' 		=> 'Model je bil uspešno posodobljen. |:model_count modeli uspešno posodobljeni.',
        'warn'          => 'Posodobili boste lastnosti naslednjega modela:|Urejali boste lastnosti naslednjega :model_count modeli:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Modeli niso bili izbrani, nič ni izbrisano.',
        'success' 		    => 'Model izbrisan!|:success_count modeli izbrisani!',
        'success_partial' 	=> ': modeli so bili izbrisani, vendar: fail_count ni bilo mogoče izbrisati, ker so še vedno sredstva, povezana z njimi.'
    ),

);
