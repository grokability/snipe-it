<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Kāore te tauira i te tīariari.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Kei te hono tenei tauira ki te kotahi, neke atu ranei nga rawa, kaore e taea te muku. Nganahia nga rawa, ka ngana ki te muku ano.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Kāore i hangaia te tauira, tēnā whakamātau anō.',
        'success' => 'I waihangahia te tauira i pai.',
        'duplicate_set' => 'Ko te tauira o te taonga me te ingoa, te kaiwhakanao me te tau tauira kei te noho tonu.',
    ),

    'update' => array(
        'error'   => 'Kāore i te whakahouhia te tauira, na me ngana ano',
        'success' => 'He pai te whakahoutanga o te tauira.',
    ),

    'delete' => array(
        'confirm'   => 'Kei te hiahia koe ki te muku i tenei tauira taonga?',
        'error'   => 'I puta he take e whakakore ana i te tauira. Tena ngana ano.',
        'success' => 'Kua mukua te tauira.'
    ),

    'restore' => array(
        'error'   		=> 'Kaore ano kia whakahokia mai te tauira, na me ngana ano',
        'success' 		=> 'He tauira kua whakahokia mai.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Kaore i whakarereke nga mara, naore i whakahoutia.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No models were selected, so nothing was deleted.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
