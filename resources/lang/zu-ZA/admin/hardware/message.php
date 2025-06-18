<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Impahla ayikho.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Leli fayela okwamanje lihlolwe kumsebenzisi futhi alikwazi ukususwa. Sicela uhlole ifa ekuqaleni, bese uzama ukususa futhi.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Impahla ayidalwanga, sicela uzame futhi. :(',
        'success' 		=> 'Ifa lidalwe ngempumelelo. :)',
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
        'error'   			=> 'Ifa alizange libuyekezwe, sicela uzame futhi',
        'success' 			=> 'Ifa libuyekezwe ngempumelelo.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Awekho amasimu akhethiwe, ngakho-ke akukho lutho olubuyekeziwe.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Ifa alizange libuyiselwe, sicela uzame futhi',
        'success' 		=> 'Ifa libuyiselwe ngempumelelo.',
        'bulk_success' 		=> 'Ifa libuyiselwe ngempumelelo.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'I-akhawunti yokuthengisa ilandelwe ngempumelelo.',
    ],


    'deletefile' => [
        'error'   => 'Ifayela alisusiwe. Ngicela uzame futhi.',
        'success' => 'Ifayili isusiwe ngempumelelo.',
    ],

    'upload' => [
        'error'   => 'Amafayela (ama) awalayishiwe. Ngicela uzame futhi.',
        'success' => 'Amafayela (ama) alayishwe ngempumelelo.',
        'nofiles' => 'Awukakhethi noma yimaphi amafayela okulayishwa, noma ifayela ozama ukulilayisha likhulu kakhulu',
        'invalidfiles' => 'Ifayela elilodwa noma ngaphezulu likhulu kakhulu noma ifayelathi engavumelekile. Amafayela afakiwe avunyelwe i-png, i-gif, i-jpg, i-doc, i-docx, i-pdf, ne-txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Ezinye izinto azange zingenise ngendlela efanele.',
        'errorDetail'           => 'Izinto ezilandelayo azange zingeniswe ngenxa yamaphutha.',
        'success'               => 'Ifayela lakho lifakiwe',
        'file_delete_success'   => 'Ifayela lakho lisusiwe ngempumelelo',
        'file_delete_error'      => 'Ifayela alikwazanga ukususwa',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Uqinisekile ukuthi ufisa ukususa le mali?',
        'error'   		=> 'Kube nenkinga yokususa le mali. Ngicela uzame futhi.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Azikho izimpahla ezikhethiwe, ngakho akukho lutho olususwe.',
        'success' 		=> 'Impahla isusiwe ngempumelelo.',
    ],

    'checkout' => [
        'error'   		=> 'Ifa alizange lihlolwe, sicela uzame futhi',
        'success' 		=> 'Ifa likhiphe ngempumelelo.',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
        'not_available' => 'Le mali ayitholakali ukuhlolwa!',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Ifa alizange lihlolwe, sicela uzame futhi',
        'success' 		=> 'Ifa lihlolwe ngempumelelo.',
        'user_does_not_exist' => 'Lo msebenzisi awuvumelekile. Ngicela uzame futhi.',
        'already_checked_in'  => 'Le mali isivele ihlolwe.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
