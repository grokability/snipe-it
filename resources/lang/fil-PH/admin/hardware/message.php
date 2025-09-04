<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Hindi umiiral ang asset.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Ang asset na ito ay kasalukuyang nai-check out sa isang user at hindi na maaaring mai-delete. Mangyaring suriin muna ang asset, at pagkatapos subukang i-delete muli. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Ang asset ay hindi naisagawa, mangyaring subukang muli. :(',
        'success' 		=> 'Ang asset ay matagumpay na naisagawa. :)',
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
        'error'   			=> 'Ang asset ay hindi nai-update, mangyaring subukang muli',
        'success' 			=> 'Ang asset ay matagumpay na nai-update.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Walang napiling mga fields, kaya walang nai-update.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Ang asset ay hindi naibalik sa dati, mangyaring subukang muli',
        'success' 		=> 'Ang asset ay matagumpay nang naibalik sa dati.',
        'bulk_success' 		=> 'Ang asset ay matagumpay nang naibalik sa dati.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Matagumpay na nai-log ang audit ng asset.',
    ],


    'deletefile' => [
        'error'   => 'Ang file ay hindi nai-delete. Mangyaring subukang muli.',
        'success' => 'Ang file ay matagumpay nang nai-delete.',
    ],

    'upload' => [
        'error'   => 'Ang file(s) ay hindi nai-upload. Mangyaring subukang muli.',
        'success' => 'Ang file(s) ay matagumpay na nai-upload.',
        'nofiles' => 'Hindi ka pumili ng maga files para sa i-upload, o ang file na gusto mong i-upload ay masyadong malaki',
        'invalidfiles' => 'Ang isa o higit sa iyong mga file ay masyadong malaki o isang uri ng file na hindi pinapayagan. Ang mga pinapayagang mga file ay ang png, gif, jpg, doc, docx, pdf, at txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Ang iilang mga aytem ay hindi nai-import ng tama.',
        'errorDetail'           => 'Ang mga sumusunod na mga Aytem ay hindi na-import dahil sa mga error.',
        'success'               => 'Ang iyong file ay na-import na',
        'file_delete_success'   => 'Ang iyong file ay matagumpay nang nai-upload',
        'file_delete_error'      => 'Ang file ay hindi mai-delete',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Sigurado kaba na gusto mong i-delete ang asset na ito?',
        'error'   		=> 'Mayroong isyu sa pag-delete ng asset. Mangyaring subukang muli.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Walang napiling mga asset, kaya walang nai-delete.',
        'success' 		=> 'Matagumpay na nai-delete ang asset.',
    ],

    'checkout' => [
        'error'   		=> 'Ang asset ay hindi nai-check out, mangyaring subukang muli',
        'success' 		=> 'Matagumpay na nai-check out ang asset.',
        'user_does_not_exist' => 'Ang user na iyon ay hindi balido. Mangyaring subukang muli.',
        'not_available' => 'Ang asset ay hindi pwedeng mai-checkout!',
        'no_assets_selected' => 'Dapat kang pumili ng kahit isang asset mula sa listahan',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Ang asset ay hindi nai-check in, mangyaring subukang muli',
        'success' 		=> 'Ang asset ay matagumpay na nai-check in.',
        'user_does_not_exist' => 'Ang user na iyon ay hindi balido. Mangyaring subukang muli.',
        'already_checked_in'  => 'Ang asset ay nai-check in na.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
