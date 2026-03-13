<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Nid yw\'r model yn bodoli.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'Mae\'r model yma wedi perthnasu hefo un neu mwy o asedau. Fydd rhaid dileu\'r asedau ac yna trio eto. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'Ni crewyd y model, ceisiwch eto o.g.y.dd.',
        'success' => 'Model wedi creu yn llwyddiannus.',
        'duplicate_set' => 'Mae model ased hefo\'r enw, gwneuthyrwr a rhif model yn bodoli yn barod.',
    ),

    'update' => array(
        'error'   => 'Ni diweddarwyd y model, ceisiwch eto o.g.y.dd',
        'success' => 'Model wedi diweddaru\'n llwyddiannus.',
    ),

    'delete' => array(
        'confirm'   => 'Ydych chi\'n sicr eich bod eisiau dileu\'r model ased yma?',
        'error'   => 'Nid oedd yn bosib dileu\'r model. Ceisiwch eto o.g.y.dd.',
        'success' => 'Model wedi dileu\'n llwyddiannus.'
    ),

    'restore' => array(
        'error'   		=> 'Nid oedd yn bosib adfer y model, ceisiwch eto o.g.y.dd',
        'success' 		=> 'Model wedi adfer yn llwyddiannus.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Dim newid mewn manylder, felly dim byd i diweddaru.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Dim modelau wedi dewis, felly dim byd i\'w ddileu.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(au) wedi\'i dileu, :fail_count heb eu ddileu gan bod asedau wedi perthnasu iddo.'
    ),

);
