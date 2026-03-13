<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Isibonelo asikho.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Lo modeli okwamanje uhlotshaniswa nefa elilodwa noma ngaphezulu futhi alinakususwa. Sicela ususe amafa, bese uzama ukususa futhi.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Isibonelo asizange sidalwe, sicela uzame futhi.',
        'success' => 'Isibonelo sidalwe ngempumelelo.',
        'duplicate_set' => 'Imodeli yezimpahla ngelo gama, umkhiqizi kanye nenombolo yomodeli kakade ikhona.',
    ),

    'update' => array(
        'error'   => 'Isibonelo asibuyekezwanga, sicela uzame futhi',
        'success' => 'Isibonelo sibuyekezwe ngempumelelo.',
    ),

    'delete' => array(
        'confirm'   => 'Ingabe uqinisekile ukuthi ufisa ukususa le model?',
        'error'   => 'Kube nenkinga yokususa imodeli. Ngicela uzame futhi.',
        'success' => 'Imodeli isusiwe ngempumelelo.'
    ),

    'restore' => array(
        'error'   		=> 'Isibonelo asibuyisiwe, sicela uzame futhi',
        'success' 		=> 'Isibonelo sibuyiselwe ngempumelelo.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Azikho amasimu ashintshiwe, ngakho akukho lutho olubuyekeziwe.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No models were selected, so nothing was deleted.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
