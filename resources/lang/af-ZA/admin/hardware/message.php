<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Bate bestaan ​​nie.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Hierdie bate word tans na \'n gebruiker nagegaan en kan nie uitgevee word nie. Gaan asseblief die bate eers in, en probeer dan weer uitvee.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Bate is nie geskep nie, probeer asseblief weer. :(',
        'success' 		=> 'Bate geskep suksesvol. :)',
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
        'error'   			=> 'Bate is nie opgedateer nie, probeer asseblief weer',
        'success' 			=> 'Bate is suksesvol opgedateer.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Geen velde is gekies nie, dus niks is opgedateer nie.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Bate is nie herstel nie, probeer asseblief weer',
        'success' 		=> 'Bate herstel suksesvol.',
        'bulk_success' 		=> 'Bate herstel suksesvol.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Bate oudit suksesvol aangemeld.',
    ],


    'deletefile' => [
        'error'   => 'Lêer nie verwyder nie. Probeer asseblief weer.',
        'success' => 'Lêer suksesvol uitgevee.',
    ],

    'upload' => [
        'error'   => 'Lêer (s) nie opgelaai nie. Probeer asseblief weer.',
        'success' => 'Lêer (s) suksesvol opgelaai.',
        'nofiles' => 'Jy het nie enige lêers vir oplaai gekies nie, of die lêer wat jy probeer oplaai is te groot',
        'invalidfiles' => 'Een of meer van jou lêers is te groot of is \'n filetipe wat nie toegelaat word nie. Toegelate filetipes is png, gif, jpg, doc, docx, pdf en txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Sommige items is nie korrek ingevoer nie.',
        'errorDetail'           => 'Die volgende items is nie ingevoer as gevolg van foute nie.',
        'success'               => 'Jou lêer is ingevoer',
        'file_delete_success'   => 'Jou lêer is suksesvol verwyder',
        'file_delete_error'      => 'Die lêer kon nie uitgevee word nie',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Is jy seker jy wil hierdie bate uitvee?',
        'error'   		=> 'Daar was \'n probleem met die verwydering van die bate. Probeer asseblief weer.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Geen bates is gekies nie, so niks is verwyder nie.',
        'success' 		=> 'Die bate is suksesvol verwyder.',
    ],

    'checkout' => [
        'error'   		=> 'Bate is nie nagegaan nie, probeer asseblief weer',
        'success' 		=> 'Die bate is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
        'not_available' => 'Die bate is nie beskikbaar vir die kassa nie!',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Bate is nie nagegaan nie, probeer asseblief weer',
        'success' 		=> 'Die bate is suksesvol nagegaan.',
        'user_does_not_exist' => 'Die gebruiker is ongeldig. Probeer asseblief weer.',
        'already_checked_in'  => 'Daardie bate is reeds nagegaan.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
