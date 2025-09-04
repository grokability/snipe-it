<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Hantidu ma jirto.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Hantidaas ma jirto ama lama codsan karo.',
    'assoc_users'	 	 => 'Hantidan hadda waa la hubiyay isticmaale lamana tirtiri karo Fadlan marka hore hubi hantida, ka dibna isku day mar kale in aad tirtirto. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Hantida lama abuurin, fadlan isku day mar kale. :(',
        'success' 		=> 'Hantida loo sameeyay si guul leh :)',
        'success_linked' => 'Hanti leh sumad :tag si guul leh ayaa loo abuuray. <strong><a href=":link" style="color: white;">Riix halkan si aad u aragto</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Hantida lama cusboonaysiin, fadlan isku day mar kale',
        'success' 			=> 'Hantida si guul leh ayaa loo cusboonaysiiyay.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Goobo lama dooran, markaa waxba lama cusboonaysiin.',
        'no_assets_selected'  =>  'Wax hanti ah lama dooran, markaa waxba lama cusboonaysiin.',
        'assets_do_not_exist_or_are_invalid' => 'Hantida la xushay lama cusboonaysiin karo.',
    ],

    'restore' => [
        'error'   		=> 'Hantidii lama soo celin, fadlan isku day mar kale',
        'success' 		=> 'Hantida si guul leh ayaa loo soo celiyay.',
        'bulk_success' 		=> 'Hantida si guul leh ayaa loo soo celiyay.',
        'nothing_updated'   => 'Wax hanti ah lama dooran, markaa waxba lama soo celin.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Hantidhawrka hantida ayaa si guul leh loo diiwaan geliyay.',
    ],


    'deletefile' => [
        'error'   => 'Faylka lama tirtirin Fadlan isku day mar kale.',
        'success' => 'Faylka si guul leh waa la tirtiray.',
    ],

    'upload' => [
        'error'   => 'Faylka lama soo rarin Fadlan isku day mar kale.',
        'success' => 'Faylka(yada) si guul leh loo soo raray.',
        'nofiles' => 'Ma aadan dooran wax fayl ah oo la soo geliyo, ama faylka aad isku dayeyso inaad geliyaan waa mid aad u weyn',
        'invalidfiles' => 'Mid ama in ka badan oo faylashaada ah aad bay u weyn yihiin ama waa nooc faylal ah oo aan la oggolayn. Noocyada faylalka la oggol yahay waa png, gif, jpg, doc, docx, pdf, iyo txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Alaabta qaar si sax ah uma soo dejin.',
        'errorDetail'           => 'Alaabta soo socota looma soo dejin khaladaad dartood.',
        'success'               => 'Faylkaaga waa la soo dejiyay',
        'file_delete_success'   => 'Faylkaaga si guul leh ayaa loo tirtiray',
        'file_delete_error'      => 'Faylka waa la tirtiri waayay',
        'file_missing' => 'Faylka la doortay waa maqan yahay',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Hal ama in ka badan oo sifooyin ah oo ku jira safka madaxa waxa ku jira xarfaha UTF-8 oo khaldan',
        'content_row_has_malformed_characters' => 'Hal ama in ka badan oo sifooyin ah safka koowaad ee nuxurka waxa ku jira xarfo UTF-8 oo khaldan',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Ma hubtaa inaad rabto inaad tirtirto hantidan?',
        'error'   		=> 'Waxaa jirtay arrin la tirtiray hantida Fadlan isku day mar kale.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Wax hanti ah lama dooran, markaa waxba lama tirtirin.',
        'success' 		=> 'Hantida si guul leh ayaa loo tirtiray.',
    ],

    'checkout' => [
        'error'   		=> 'Hantida lama hubin, fadlan isku day mar kale',
        'success' 		=> 'Hantida si guul leh ayaa loo hubiyay.',
        'user_does_not_exist' => 'Isticmaalahaasi waa khalad Fadlan isku day mar kale.',
        'not_available' => 'Hantidaas looma hayo hubin!',
        'no_assets_selected' => 'Waa inaad liiska ka doorataa ugu yaraan hal hanti',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Hantida lama hubin, fadlan isku day mar kale',
        'success' 		=> 'Hantida si guul leh ayaa loo hubiyay.',
        'user_does_not_exist' => 'Isticmaalahaasi waa khalad Fadlan isku day mar kale.',
        'already_checked_in'  => 'Hantidaas mar horeba waa la hubiyay.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Jooji codsiga shaygan',
    ],

];
