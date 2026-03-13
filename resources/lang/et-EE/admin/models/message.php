<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Mudelit pole olemas.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'See mudel on seostus ühe või mitme vahendiga ja seda ei saa kustutada. Palun kustuta vahendid ja seejärel proovi uuesti kustutada. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Mudelit ei loodud, proovi uuesti.',
        'success' => 'Mudeli loomine õnnestus.',
        'duplicate_set' => 'Sellise nime, tootja ja mudeli numbriga mudel on juba olemas.',
    ),

    'update' => array(
        'error'   => 'Mudelit ei uuendatud, proovige uuesti',
        'success' => 'Mudeli uuendamine õnnestus.',
    ),

    'delete' => array(
        'confirm'   => 'Kas oled kindel, et soovid selle mudeli kustutada?',
        'error'   => 'Mudeli kustutamisel tekkis probleem. Palun proovi uuesti.',
        'success' => 'Mudeli kustutamine õnnestus.'
    ),

    'restore' => array(
        'error'   		=> 'Mudeli taastamine ei õnnestunud, proovi uuesti',
        'success' 		=> 'Mudeli taastamine õnnestus.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Ühtegi välja ei muudetud, uuendusi ei tehtud',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Mudeleid ei valitud, nii et midagi ei kustutatud.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count mudel(it) kustutati, kuid :fail_count ei õnnestunud kustutada kuna nendega on ikka veel vara seotud.'
    ),

);
