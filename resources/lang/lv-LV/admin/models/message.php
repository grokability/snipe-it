<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Modelis nepastāv.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Šobrīd šis modelis ir saistīts ar vienu vai vairākiem aktīviem, un tos nevar izdzēst. Lūdzu, izdzēsiet aktīvus un pēc tam mēģiniet vēlreiz dzēst.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Modelis netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Modelis veiksmīgi izveidots.',
        'duplicate_set' => 'Aktīvu modelis ar šo nosaukumu, ražotāju un modeļa numuru jau pastāv.',
    ),

    'update' => array(
        'error'   => 'Modelis nav atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Modelis tika veiksmīgi atjaunināts.',
    ),

    'delete' => array(
        'confirm'   => 'Vai tiešām vēlaties dzēst šo aktīvu modeli?',
        'error'   => 'Radās problēma, izdzēšot modeli. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Modelis tika veiksmīgi dzēsts.'
    ),

    'restore' => array(
        'error'   		=> 'Modelis netika atjaunots, lūdzu, mēģiniet vēlreiz',
        'success' 		=> 'Veiksmīgi atjaunots modelis.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Neviens laukums netika mainīts, tāpēc nekas netika atjaunināts.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Nav atlasītu modeļu, tāpēc nekas netika izdzēsts.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count modeļi dzēsti, tomēr :fail_count nevarēja tik dzēsti, jo tiem ir piesaistītas aparatūras.'
    ),

);
