<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Nid yw\'r ased yn bodoli.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Ar hyn o bryd mae\'r ased yma allan gan ddefnyddiwr ac ni ellir ei ddileu. Cofnodwch yr ased yn ol i fewn yn gyntaf, ac yna ceisiwch ei ddileu eto. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Ni crewyd yr ased, ceisiwch eto o. g. y. dd. :(',
        'success' 		=> 'Ased wedi creu yn llwyddiannus. :)',
        'success_linked' => 'Asset with tag :tag was created successfully. <strong><a href=":link" style="color: white;">Click here to view</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Ni diweddarwyd yr assed, ceisiwch eto o. g. y. dd',
        'success' 			=> 'Ased wedi diweddaru\'n llwyddiannus.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Dim newid mewn manylder, felly dim byd wedi\'i diweddaru.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Nid oedd yn bosib adfer yr ased, ceisiwch eto o. g. y. dd',
        'success' 		=> 'Ased wedi adfer yn llwyddiannus.',
        'bulk_success' 		=> 'Ased wedi adfer yn llwyddiannus.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Cofnodwyd archwiliad asedau yn llwyddiannus.',
    ],


    'deletefile' => [
        'error'   => 'Ffeil heb ei ddileu. Ceisiwch eto o.g.y.dd.',
        'success' => 'Ffeil wedi dileu yn llwyddiannus.',
    ],

    'upload' => [
        'error'   => 'Ffeil(iau) heb ei uwchlwytho. Ceisiwch eto o. g. y. dd.',
        'success' => 'Ffeil(iau) wedi uwchlwytho yn llwyddiannus.',
        'nofiles' => 'Ni wnaethoch chi ddewis unrhyw ffeiliau i\'w uwchlwytho, neu mae\'r ffeil rydych chi\'n ceisio ei huwchlwytho yn rhy fawr',
        'invalidfiles' => 'Mae un neu mwy o\'r ffeiliau unai yn rhy fawr neu ddim y math cywir. Derbynir png, gif, fjp, doc, docx, pdf a txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Rhai eitemau heb ei mewnforio\'n gywir.',
        'errorDetail'           => 'Ni fewnforiwyd yr eitemau canlynol oherwydd gwallau.',
        'success'               => 'Mae\'ch ffeil wedi\'i mewnforio',
        'file_delete_success'   => 'Mae eich ffeil wedi\'i dileu yn llwyddiannus',
        'file_delete_error'      => 'Nid oedd yn bosib dileu\'r ffeil',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Ydych chi\'n sicr eich bod eisiau dileu\'r ased yma?',
        'error'   		=> 'Roedd problem wrth ceisio dileu\'r ased. Ceisiwch eto o. g. y. dd.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Dim asedau wedi dewis, felly dim byd wedi\'i dileu.',
        'success' 		=> 'Ased wedi dileu\'n llwyddiannus.',
    ],

    'checkout' => [
        'error'   		=> 'Ased heb ei nodi fel allan, ceisiwch eto o. g. y. dd',
        'success' 		=> 'Ased wedi nodi fel allan yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o.g.y.dd.',
        'not_available' => 'Nid yw\'r ased yma ar gael i\'w defnyddio!',
        'no_assets_selected' => 'Rhaid i chi ddewis o leiaf un ased o\'r rhestr',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Ased heb ei nodi i mewn, ceisiwch eto o. g. y. dd',
        'success' 		=> 'Ased wedi nodi i mewn yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o. g. y. dd.',
        'already_checked_in'  => 'Ased wedi nodi i mewn yn gywir.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
