<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Vahend puudub.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Seda vahendit ei eksisteeri või see ei ole taotletav.',
    'assoc_users'	 	 => 'Seda vara kontrollitakse kasutajale praegu ja seda ei saa kustutada. Esmalt kontrollige varast ja proovige seejärel uuesti kustutada.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Vahendit ei loodud, palun proovi uuesti. :(',
        'success' 		=> 'Vahendi loomine õnnestus. :)',
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
        'error'   			=> 'Vara ei värskendatud, proovige uuesti',
        'success' 			=> 'Vara värskendati edukalt',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Pole ühtegi välju valitud, nii et midagi ei uuendatud.',
        'no_assets_selected'  =>  'Ühtegi vahendit ei valitud, muudatusi ei tehtud.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Vara ei taastatud, palun proovi uuesti',
        'success' 		=> 'Varad on edukalt taastatud.',
        'bulk_success' 		=> 'Varad on edukalt taastatud.',
        'nothing_updated'   => 'No assets were selected, so nothing was restored.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Varakontrolli sisselogimisel.',
    ],


    'deletefile' => [
        'error'   => 'Faili pole kustutatud. Palun proovi uuesti.',
        'success' => 'Fail edukalt kustutatud.',
    ],

    'upload' => [
        'error'   => 'Faili (d) pole üles laaditud. Palun proovi uuesti.',
        'success' => 'Fail (id) edukalt üles laaditud.',
        'nofiles' => 'Te ei valinud üleslaadimiseks ühtegi faili või fail, mille üritate üles laadida, on liiga suur',
        'invalidfiles' => 'Üks või mitu teie faili on liiga suured või failitüüp pole lubatud. Lubatud failitüübid on png, gif, jpg, doc, docx, pdf ja txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Mõned üksused ei impordinud õigesti.',
        'errorDetail'           => 'Järgmisi punkte ei imporditud vigade tõttu.',
        'success'               => 'Teie fail on imporditud',
        'file_delete_success'   => 'Teie fail on edukalt kustutatud',
        'file_delete_error'      => 'Faili ei saanud kustutada',
        'file_missing' => 'The file selected is missing',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Kas olete kindel, et soovite selle vara kustutada?',
        'error'   		=> 'Viga kustutas. Palun proovi uuesti.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Varasid ei valitud, nii et midagi ei kustutatud.',
        'success' 		=> 'Varasus kustutati edukalt.',
    ],

    'checkout' => [
        'error'   		=> 'Varasid ei kontrollitud, proovige uuesti',
        'success' 		=> 'Varad võeti edukalt välja.',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
        'not_available' => 'See vara pole kontrollimiseks saadaval!',
        'no_assets_selected' => 'Sa pead valima vähemalt ühe kirje nimekirjast',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Vara ei olnud märgitud, palun proovi uuesti',
        'success' 		=> 'Vara on edukalt kontrollitud',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
        'already_checked_in'  => 'See vara on juba sisse registreeritud.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Cancel this item request',
    ],

];
