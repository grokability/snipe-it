<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Model bestaan ​​nie.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Hierdie model word tans geassosieer met een of meer bates en kan nie verwyder word nie. Verwyder asseblief die bates en probeer dan weer uitvee.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Model is nie geskep nie, probeer asseblief weer.',
        'success' => 'Model suksesvol geskep.',
        'duplicate_set' => '\'N Bate-model met die naam, vervaardiger en modelnommer bestaan ​​reeds.',
    ),

    'update' => array(
        'error'   => 'Model is nie opgedateer nie, probeer asseblief weer',
        'success' => 'Model suksesvol opgedateer.',
    ),

    'delete' => array(
        'confirm'   => 'Is jy seker jy wil hierdie batemodel uitvee?',
        'error'   => 'Daar was \'n probleem met die verwydering van die model. Probeer asseblief weer.',
        'success' => 'Die model is suksesvol verwyder.'
    ),

    'restore' => array(
        'error'   		=> 'Model is nie herstel nie, probeer asseblief weer',
        'success' 		=> 'Model herstel suksesvol.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Geen velde is verander nie, so niks is opgedateer nie.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No models were selected, so nothing was deleted.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
