<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Níl múnla ann.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Tá an tsamhail seo bainteach le sócmhainní amháin nó níos mó faoi láthair agus ní féidir é a scriosadh. Scrios na sócmhainní, agus ansin déan iarracht a scriosadh arís.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Níor cruthaíodh an tsamhail, déan iarracht arís.',
        'success' => 'Múnla cruthaithe go rathúil.',
        'duplicate_set' => 'Tá múnla sócmhainne leis an ainm sin, an monaróir agus an uimhir samhail ann cheana féin.',
    ),

    'update' => array(
        'error'   => 'Níor nuashonraíodh an tsamhail, déan iarracht arís',
        'success' => 'Modúl nuashonraithe go rathúil',
    ),

    'delete' => array(
        'confirm'   => 'An bhfuil tú cinnte gur mian leat an tsamhail sócmhainne seo a scriosadh?',
        'error'   => 'Bhí ceist ann a scriosadh an tsamhail. Arís, le d\'thoil.',
        'success' => 'Scriosadh an tsamhail go rathúil.'
    ),

    'restore' => array(
        'error'   		=> 'Níor athchóiríodh an tsamhail, déan iarracht arís',
        'success' 		=> 'Múnla curtha ar ais go rathúil.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Níor athraíodh aon réimsí, mar sin níor nuashonraíodh aon rud.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No models were selected, so nothing was deleted.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
