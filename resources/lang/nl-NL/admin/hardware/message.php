<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Dit asset bestaat niet.',
    'does_not_exist_var' => 'Asset met tag :asset_tag niet gevonden.',
    'no_tag' 	         => 'Geen asset tag opgegeven.',
    'does_not_exist_or_not_requestable' => 'Die asset bestaat niet of is niet aanvraagbaar.',
    'assoc_users'	 	 => 'Dit asset is momenteel toegewezen aan een gebruiker en kan niet worden verwijderd. Controleer het asset eerst en probeer het opnieuw. ',
    'warning_audit_date_mismatch' 	=> 'De volgende auditdatum van dit asset (:next_audit_date) ligt vóór de laatste auditdatum (:last_audit_date). Gelieve de volgende auditdatum bij te werken.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Asset is niet aangemaakt, probeer het opnieuw :(',
        'success' 		=> 'Asset is succesvol aangemaakt. :)',
        'success_linked' => 'Asset met tag :tag is succesvol gemaakt. <strong><a href=":link" style="color: white;">Klik hier om te bekijken</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Asset is niet gewijzigd, probeer het opnieuw',
        'success' 			=> 'Asset is succesvol bijgewerkt.',
        'encrypted_warning' => 'Asset is succesvol bijgewerkt, maar gecodeerde aangepaste velden hadden geen toegang tot machtigingen',
        'nothing_updated'	=>  'Geen veld is geselecteerd, er is dus niks gewijzigd.',
        'no_assets_selected'  =>  'Er zijn geen assets geselecteerd, er is dus niets bijgewerkt.',
        'assets_do_not_exist_or_are_invalid' => 'Geselecteerde assets kunnen niet worden bijgewerkt.',
    ],

    'restore' => [
        'error'   		=> 'Asset is niet hersteld, probeer het opnieuw',
        'success' 		=> 'Asset is succesvol hersteld.',
        'bulk_success' 		=> 'Asset is succesvol hersteld.',
        'nothing_updated'   => 'Er zijn geen assets geselecteerd, er is dus niets hersteld.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit mislukt: :error ',
        'success' 		=> 'Asset audit succesvol geregistreerd.',
    ],


    'deletefile' => [
        'error'   => 'Bestand is niet verwijderd. Probeer het opnieuw.',
        'success' => 'Bestand is met succes verwijderd.',
    ],

    'upload' => [
        'error'   => 'Bestand(en) zijn niet geüpload. Probeer het opnieuw.',
        'success' => 'Bestand(en) zijn met succes geüpload.',
        'nofiles' => 'Je hebt geen bestanden geselecteerd om te uploaden, of het bestand wat je probeert te uploaden is te groot',
        'invalidfiles' => 'Een of meer van uw bestanden is te groot of is een bestandstype dat niet is toegestaan. Toegestaande bestandstypen png, gif, jpg, doc, docx, pdf en txt.',
    ],

    'import' => [
        'import_button'         => 'Import verwerken',
        'error'                 => 'Sommige items zijn niet goed geïmporteerd.',
        'errorDetail'           => 'De volgende items zijn niet geïmporteerd vanwege fouten.',
        'success'               => 'Je bestand is geïmporteerd',
        'file_delete_success'   => 'Je bestand is succesvol verwijderd',
        'file_delete_error'      => 'Het bestand kon niet worden verwijderd',
        'file_missing' => 'Het geselecteerde bestand ontbreekt',
        'file_already_deleted' => 'Het geselecteerde bestand is al verwijderd',
        'header_row_has_malformed_characters' => 'Een of meer attributen in de kopregel bevatten ongeldige UTF-8-tekens',
        'content_row_has_malformed_characters' => 'Een of meer attributen in de eerste rij inhoud bevat ongeldige UTF-8 tekens',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Weet je zeker dat je dit asset wilt verwijderen?',
        'error'   		=> 'Er was een probleem tijdens het verwijderen van het asset. Probeer het opnieuw.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Er zijn geen assets geselecteerd, er is dus niets verwijderd.',
        'success' 		=> 'Het asset is succesvol verwijderd.',
    ],

    'checkout' => [
        'error'   		=> 'Asset is niet uitgecheckt, probeer het opnieuw',
        'success' 		=> 'Asset is met succes uitgecheckt.',
        'user_does_not_exist' => 'De gebruiker is ongeldig. Probeer het opnieuw.',
        'not_available' => 'Dat asset is niet beschikbaar voor check-out!',
        'no_assets_selected' => 'U moet minstens één asset selecteren uit de lijst',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Asset is niet ingecheckt, probeer het opnieuw',
        'success' 		=> 'Asset is met succes ingecheckt.',
        'user_does_not_exist' => 'De gebruiker is ongeldig. Probeer het opnieuw.',
        'already_checked_in'  => 'Dat asset is al ingecheckt.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Annuleer deze aanvraag',
    ],

];
