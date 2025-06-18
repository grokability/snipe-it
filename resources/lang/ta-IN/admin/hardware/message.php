<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'சொத்து இல்லை.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'இந்த சொத்து தற்போது ஒரு பயனர் வெளியே சோதிக்கப்பட்டது மற்றும் நீக்க முடியாது. முதலில் சொத்தை சரிபார்த்து, மீண்டும் நீக்கி முயற்சிக்கவும்.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'சொத்து உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும். :(',
        'success' 		=> 'சொத்து வெற்றிகரமாக உருவாக்கப்பட்டது. :)',
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
        'error'   			=> 'சொத்து புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' 			=> 'சொத்து வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'எந்த துறைகளும் தேர்ந்தெடுக்கப்படவில்லை, அதனால் எதுவும் புதுப்பிக்கப்படவில்லை.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'சொத்து மீட்டமைக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' 		=> 'சொத்து வெற்றிகரமாக மீட்டமைக்கப்பட்டது.',
        'bulk_success' 		=> 'சொத்து வெற்றிகரமாக மீட்டமைக்கப்பட்டது.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'சொத்து தணிக்கை வெற்றிகரமாக உள்நுழைந்தது.',
    ],


    'deletefile' => [
        'error'   => 'கோப்பு நீக்கப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு வெற்றிகரமாக நீக்கப்பட்டது.',
    ],

    'upload' => [
        'error'   => 'கோப்பு (கள்) பதிவேற்றப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு (கள்) வெற்றிகரமாக பதிவேற்றப்பட்டது.',
        'nofiles' => 'பதிவேற்றுவதற்கான எந்தவொரு கோப்பையும் நீங்கள் தேர்ந்தெடுக்கவில்லை அல்லது நீங்கள் பதிவேற்ற முயற்சிக்கும் கோப்பு மிகப்பெரியது',
        'invalidfiles' => 'உங்கள் கோப்புகளில் ஒன்று அல்லது அதற்கு மேற்பட்டவை மிக அதிகமாக உள்ளது அல்லது அனுமதிக்கப்படாத கோப்பு வகை உள்ளது. அனுமதிக்கப்பட்ட கோப்புரிமைகள் png, gif, jpg, doc, docx, pdf மற்றும் txt ஆகியவை.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'சில உருப்படிகளை சரியாக இறக்குமதி செய்யவில்லை.',
        'errorDetail'           => 'பிழைகள் காரணமாக பின்வரும் உருப்படிகளை இறக்குமதி செய்யப்படவில்லை.',
        'success'               => 'உங்கள் கோப்பு இறக்குமதி செய்யப்பட்டது',
        'file_delete_success'   => 'உங்கள் கோப்பு வெற்றிகரமாக நீக்கப்பட்டது',
        'file_delete_error'      => 'கோப்பை நீக்க முடியவில்லை',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'இந்த சொத்தை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error'   		=> 'சொத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'சொத்துகள் எதுவும் தேர்ந்தெடுக்கப்படவில்லை, எனவே எதுவும் நீக்கப்படவில்லை.',
        'success' 		=> 'சொத்து வெற்றிகரமாக நீக்கப்பட்டது.',
    ],

    'checkout' => [
        'error'   		=> 'சொத்து சரிபார்க்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' 		=> 'சொத்து வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'not_available' => 'புதுப்பித்துக்காக அந்த சொத்து கிடைக்கவில்லை!',
        'no_assets_selected' => 'You must select at least one asset from the list',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'சொத்து சரிபார்க்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' 		=> 'சொத்து வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'already_checked_in'  => 'அந்தச் சொத்து ஏற்கனவே சோதிக்கப்பட்டுள்ளது.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
