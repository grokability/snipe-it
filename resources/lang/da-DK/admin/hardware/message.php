<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Asset eksisterer ikke.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Dette aktiv findes ikke eller er ikke påkrævet.',
    'assoc_users'	 	 => 'Dette aktiv er i øjeblikket tjekket ud til en bruger og kan ikke slettes. Kontroller aktivet først, og prøv derefter at slette igen.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Akten blev ikke oprettet, prøv igen. :(',
        'success' 		=> 'Aktivet blev oprettet med succes. :)',
        'success_linked' => 'Aktiv med tag :tag blev oprettet. <strong><a href=":link" style="color: white;">Klik her for at se</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Akten blev ikke opdateret, prøv igen',
        'success' 			=> 'Asset opdateret med succes.',
        'encrypted_warning' => 'Asset opdateret med succes, men krypterede brugerdefinerede felter skyldtes ikke tilladelser',
        'nothing_updated'	=>  'Ingen felter blev valgt, så intet blev opdateret.',
        'no_assets_selected'  =>  'Ingen aktiver blev valgt, så intet blev opdateret.',
        'assets_do_not_exist_or_are_invalid' => 'Valgte aktiver kan ikke opdateres.',
    ],

    'restore' => [
        'error'   		=> 'Akten blev ikke gendannet, prøv igen',
        'success' 		=> 'Asset restaureret med succes.',
        'bulk_success' 		=> 'Asset restaureret med succes.',
        'nothing_updated'   => 'Ingen aktiver blev valgt, så intet blev gendannet.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Asset audit succesfuldt logget.',
    ],


    'deletefile' => [
        'error'   => 'Filen er ikke slettet. Prøv igen.',
        'success' => 'Filen er slettet korrekt.',
    ],

    'upload' => [
        'error'   => 'Fil (er) ikke uploadet. Prøv igen.',
        'success' => 'Fil (er), der blev uploadet korrekt.',
        'nofiles' => 'Du valgte ikke nogen filer til upload, eller filen du forsøger at uploade er for stor',
        'invalidfiles' => 'En eller flere af dine filer er for store eller er en filtype, der ikke er tilladt. Tilladte filtyper er png, gif, jpg, doc, docx, pdf og txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Nogle elementer importerede ikke korrekt.',
        'errorDetail'           => 'Følgende elementer blev ikke importeret på grund af fejl.',
        'success'               => 'Din fil er blevet importeret',
        'file_delete_success'   => 'Din fil er blevet slettet korrekt',
        'file_delete_error'      => 'Filen kunne ikke slettes',
        'file_missing' => 'Den valgte fil mangler',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'En eller flere attributter i overskriftsrækken indeholder misdannede UTF-8 tegn',
        'content_row_has_malformed_characters' => 'En eller flere attributter i den første række indhold indeholder misdannede UTF-8 tegn',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Er du sikker på, at du vil slette dette aktiv?',
        'error'   		=> 'Der opstod et problem ved at slette aktivet. Prøv igen.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Ingen aktiver blev valgt, så intet blev slettet.',
        'success' 		=> 'Aktivet blev slettet med succes.',
    ],

    'checkout' => [
        'error'   		=> 'Akten blev ikke tjekket ud, prøv igen',
        'success' 		=> 'Asset tjekket ud med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
        'not_available' => 'Det aktiv er ikke tilgængeligt for kassen!',
        'no_assets_selected' => 'Du skal vælge mindst ét aktiv fra listen',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Akten blev ikke tjekket ind, prøv igen',
        'success' 		=> 'Asset tjekket ind med succes.',
        'user_does_not_exist' => 'Denne bruger er ugyldig. Prøv igen.',
        'already_checked_in'  => 'Det aktiv er allerede kontrolleret.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Annuller denne vareanmodning',
    ],

];
