<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Majetek nenalezen.',
    'does_not_exist_var' => 'Majetek se štítkem :asset_tag nebyl nalezen.',
    'no_tag' 	         => 'Nebyl zadán žádný štítek',
    'does_not_exist_or_not_requestable' => 'Tento majetek neexistuje nebo jej nelze vyskladnit.',
    'assoc_users'	 	 => 'Majetek je předán svému uživateli a nelze jej odstranit. Před odstraněním jej nejprve převezměte. ',
    'warning_audit_date_mismatch' 	=> 'Příští datum auditu tohoto majetku (:next_audit_date) je před posledním datem auditu (:last_audit_date). Aktualizujte prosím následující datum auditu.',
    'labels_generated'   => 'Popisky byly úspěšně vygenerovány.',
    'error_generating_labels' => 'Chyba při generování popisků.',
    'no_assets_selected' => 'Žadná zařízení vybrána.',

    'create' => [
        'error'   		=> 'Majetek se nepodařilo vytvořit, zkuste to prosím znovu.',
        'success' 		=> 'Majetek byl v pořádku vytvořen.',
        'success_linked' => 'Zařízení se štítkem :tag byl úspěšně vytvořen. <strong><a href=":link" style="color: white;">Klidni zde pro zobrazení</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Majetek se nepodařilo upravit, zkuste to prosím znovu',
        'success' 			=> 'Majetek úspěšně aktualizován.',
        'encrypted_warning' => 'Majetek byl úspěšně aktualizován, ale šifrovaná vlastní pole nebyla způsobena oprávněním',
        'nothing_updated'	=>  'Nebyla zvolena žádná pole, nic se tedy neupravilo.',
        'no_assets_selected'  =>  'Nebyl zvolen žádný majetek, nic se tedy neupravilo.',
        'assets_do_not_exist_or_are_invalid' => 'Vybrané položky nelze aktualizovat.',
    ],

    'restore' => [
        'error'   		=> 'Majetek se nepodařilo obnovit, zkuste to prosím později',
        'success' 		=> 'Majetek byl v pořádku obnoven.',
        'bulk_success' 		=> 'Majetek byl v pořádku obnoven.',
        'nothing_updated'   => 'Nevybrali jste žádné položky, nic tedy nebylo obnoveno.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Audit aktiv byl úspěšně zaznamenáván.',
    ],


    'deletefile' => [
        'error'   => 'Soubor se nesmazal, prosím zkuste to znovu.',
        'success' => 'Soubor byl úspěšně smazán.',
    ],

    'upload' => [
        'error'   => 'Soubor(y) se nepodařilo nahrát, zkuste to prosím znovu.',
        'success' => 'Soubor(y) byly v pořádku nahrány.',
        'nofiles' => 'K nahrání jste nevybrali žádný, nebo příliš velký soubor',
        'invalidfiles' => 'Jeden nebo více označených souborů je příliš velkých nebo nejsou podporované. Povolenými příponami jsou png, gif, pdf a txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Některé položky nebyly správně importovány.',
        'errorDetail'           => 'Následující položky nebyly importovány kvůli chybám.',
        'success'               => 'Váš soubor byl importován',
        'file_delete_success'   => 'Váš soubor byl úspěšně odstraněn',
        'file_delete_error'      => 'Soubor nelze odstranit',
        'file_missing' => 'Vybraný soubor chybí',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Jeden nebo více sloupců obsahuje v záhlaví poškozené UTF-8 znaky',
        'content_row_has_malformed_characters' => 'Jedna nebo více hodnot v prvním řádku obsahu obsahuje poškozené UTF-8 znaky',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Opravdu si přejete tento majetek odstranit?',
        'error'   		=> 'Nepodařilo se nám tento majetek odstranit. Zkuste to prosím znovu.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Žádný majetek nebyl vybrán, takže nic nebylo odstraněno.',
        'success' 		=> 'Majetek byl úspěšně smazán.',
    ],

    'checkout' => [
        'error'   		=> 'Majetek nebyl předán, zkuste to prosím znovu',
        'success' 		=> 'Majetek byl v pořádku předán.',
        'user_does_not_exist' => 'Tento uživatel je neplatný. Zkuste to prosím znovu.',
        'not_available' => 'Tento majetek není k dispozici pro výdej!',
        'no_assets_selected' => 'Je třeba vybrat ze seznamu alespoň jeden majetek',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Majetek nebyl převzat. Zkuste to prosím znovu',
        'success' 		=> 'Majetek byl v pořádku převzat.',
        'user_does_not_exist' => 'Tento uživatel je neplatný. Zkuste to prosím znovu.',
        'already_checked_in'  => 'Tento majetek je již předaný.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Zrušit tuto žádost o položku',
    ],

];
