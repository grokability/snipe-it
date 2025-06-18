<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Kaore he tahua.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Kei te tirohia tenei taonga i tetahi kaiwhakamahi me te kore e taea te muku. Tirohia koa te taonga i te tuatahi, a ka ngana ki te muku ano.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Kaore i hangaia te tahua, tēnā whakamātau anō. :(',
        'success' 		=> 'Kua waihangatia te tahua. :)',
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
        'error'   			=> 'Kāore i te whakahouhia te tahua, tēnā whakamātau anō',
        'success' 			=> 'Kua whakahoutia te tahua.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Kaore i whiriwhiria he mahinga, na reira kaore i whakahoutia.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Kaore i whakahokia mai te tahua, tena koa ngana ano',
        'success' 		=> 'Kua hokihia te tahua.',
        'bulk_success' 		=> 'Kua hokihia te tahua.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Kua pai te takiuru o te kaute.',
    ],


    'deletefile' => [
        'error'   => 'Kāore te kōnae i mukua. Tena ngana ano.',
        'success' => 'Kua mukua te kōnae.',
    ],

    'upload' => [
        'error'   => 'Ko nga kōnae kāore i tukuna. Tena ngana ano.',
        'success' => 'Ko te (ngā) kōnae i tukuna paihia.',
        'nofiles' => 'Kaore i whiriwhiria e koe tetahi kōnae mo te tukuna, ko te kōnae e ngana ana koe ki te tuku he nui rawa',
        'invalidfiles' => 'Kotahi, nui atu ranei o ou kōnae he nui rawa atu, he waaahi ranei e kore e whakaaetia. Ko nga kōnae e whakaaetia ana he png, gif, jpg, doc, docx, pdf, me te txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Kāore i tika te kawemai o etahi o nga mea.',
        'errorDetail'           => 'Ko nga mea e whai ake nei kihai i kawemai no te mea he hapa.',
        'success'               => 'I kawemai to kōnae',
        'file_delete_success'   => 'Kua mukua pai to kōnae',
        'file_delete_error'      => 'Kāore i taea te mukua te kōnae',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Kei te hiahia koe ki te muku i tenei taonga?',
        'error'   		=> 'He raru kei te whakakore i te taonga. Tena ngana ano.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Kaore he rawa i tohua, na reira kaore i whakakorehia.',
        'success' 		=> 'Kua mukua te taonga.',
    ],

    'checkout' => [
        'error'   		=> 'Kaore i teahia te taketake, me ngana ano',
        'success' 		=> 'Kua tohua te tahua.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
        'not_available' => 'Kaore i te wātea te taonga mo te takitaki!',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Kaore i whakauruhia te taketake, me ngana ano',
        'success' 		=> 'Kua tohua te tahua.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
        'already_checked_in'  => 'Kua tohua taua taonga i roto i.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
