<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Aktīvs neeksistē.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'That asset does not exist or is not requestable.',
    'assoc_users'	 	 => 'Šobrīd šis īpašums tiek pārbaudīts lietotājam un to nevar izdzēst. Vispirms pārbaudiet aktīvu, un pēc tam mēģiniet vēlreiz izdzēst.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Iezīmes tika sekmīgi izveidotas.',
    'error_generating_labels' => 'Kļūda iezīmju veidošanas laikā.',
    'no_assets_selected' => 'Nav atlasīts neviens līdzeklis.',

    'create' => [
        'error'   		=> 'Īpašums netika izveidots, lūdzu, mēģiniet vēlreiz. :(',
        'success' 		=> 'Īpašums veiksmīgi izveidots. :)',
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
        'error'   			=> 'Īpašums netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' 			=> 'Īpašums ir veiksmīgi atjaunināts.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Lauki nav atlasīti, tāpēc nekas netika atjaunināts.',
        'no_assets_selected'  =>  'No assets were selected, so nothing was updated.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Īpašums netika atjaunots, lūdzu, mēģiniet vēlreiz',
        'success' 		=> 'Aktīvs veiksmīgi atjaunots.',
        'bulk_success' 		=> 'Aktīvs veiksmīgi atjaunots.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Aktīvu audits ir veiksmīgi reģistrēts.',
    ],


    'deletefile' => [
        'error'   => 'Fails nav izdzēsts. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Fails veiksmīgi izdzēsts.',
    ],

    'upload' => [
        'error'   => 'Faili nav augšupielādēti. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Faili (-i) ir veiksmīgi augšupielādēti.',
        'nofiles' => 'Jūs neesat atlasījis augšupielādējamos failus, vai arī fails, kuru mēģināt augšupielādēt, ir pārāk liels',
        'invalidfiles' => 'Viens vai vairāki jūsu faili ir pārāk lieli vai nav atļauto faila tipu. Atļautie failu tipi ir png, gif, jpg, doc, docx, pdf un txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Daži vienumi netika pareizi importēti.',
        'errorDetail'           => 'Tālāk minētie vienumi netika importēti kļūdu dēļ.',
        'success'               => 'Jūsu fails ir importēts',
        'file_delete_success'   => 'Jūsu fails ir veiksmīgi izdzēsts',
        'file_delete_error'      => 'Failu nevarēja dzēst',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Vai tiešām vēlaties dzēst šo īpašumu?',
        'error'   		=> 'Radās problēma, noņemot aktīvu. Lūdzu mēģiniet vēlreiz.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Netika atlasīti neviens aktīvs, tāpēc nekas netika izdzēsts.',
        'success' 		=> 'Aktīvs tika veiksmīgi dzēsts.',
    ],

    'checkout' => [
        'error'   		=> 'Īpašums netika pārbaudīts, lūdzu, mēģiniet vēlreiz',
        'success' 		=> 'Aktīvs pārbaudīts veiksmīgi.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
        'not_available' => 'Šis īpašums nav pieejams izrakstīšanai!',
        'no_assets_selected' => 'Jums jāizvēlas vismaz vienu aparatūru',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Īpašums netika reģistrēts, lūdzu, mēģiniet vēlreiz',
        'success' 		=> 'Aktīvs ir pārbaudīts veiksmīgi.',
        'user_does_not_exist' => 'Šis lietotājs ir nederīgs. Lūdzu mēģiniet vēlreiz.',
        'already_checked_in'  => 'Šis aktīvs jau ir reģistrēts.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
