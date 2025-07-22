<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Eiendel eksisterer ikke.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Eiendelen eksisterer ikke eller kan ikke forespørres.',
    'assoc_users'	 	 => 'Denne eiendelen er merket som utsjekket til en bruker og kan ikke slettes. Vennligst sjekk inn eiendelen først, og forsøk sletting på nytt. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Eiendelen ble ikke opprettet, prøv igjen :(',
        'success' 		=> 'Eiendelen ble opprettet :)',
        'success_linked' => 'Eiendelen med taggen :tag ble opprettet. <strong><a href=":link" style="color: white;">Klikk her for å vise</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Eiendelen ble ikke oppdatert, prøv igjen',
        'success' 			=> 'Oppdatering av eiendel vellykket.',
        'encrypted_warning' => 'Eiendel er oppdatert, men kryptert tilpassede felter var ikke grunnet tillatelser',
        'nothing_updated'	=>  'Ingen felter er valgt, så ingenting ble endret.',
        'no_assets_selected'  =>  'Ingen felter er valgt, så ingenting ble endret.',
        'assets_do_not_exist_or_are_invalid' => 'Valgte eiendeler kan ikke oppdateres.',
    ],

    'restore' => [
        'error'   		=> 'Eiendel ble ikke gjenopprettet. Prøv igjen',
        'success' 		=> 'Vellykket gjenoppretting av eiendel.',
        'bulk_success' 		=> 'Ressursen ble gjenopprettet.',
        'nothing_updated'   => 'Inger ressurser ble valgt, så ingenting ble gjenoprettet.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Asset audit ble logget.',
    ],


    'deletefile' => [
        'error'   => 'Fil ble ikke slettet. Prøv igjen.',
        'success' => 'Vellykket sletting av fil.',
    ],

    'upload' => [
        'error'   => 'Fil(er) ble ikke lastet opp. Prøv igjen.',
        'success' => 'Vellykket opplasting av fil(er).',
        'nofiles' => 'Ingen fil er valgt til opplasting, eller filen er for stor',
        'invalidfiles' => 'En eller flere av filene dine er for store eller av en ikke tillatt filtype. Tillatte filtyper er png, gif, jpg, doc, docx, pdf og txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Noen elementer ble ikke importert riktig.',
        'errorDetail'           => 'Følgende elementer ble ikke importert på grunn av feil.',
        'success'               => 'Filen har blitt importert',
        'file_delete_success'   => 'Filen har blitt slettet',
        'file_delete_error'      => 'Filen kunne ikke bli slettet',
        'file_missing' => 'Valgt fil mangler (fant ikke filen)',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'En eller flere attributter i overskriftsraden inneholder feilformede UTF-8 tegn',
        'content_row_has_malformed_characters' => 'En eller flere attributter i første rad i inneholdet inneholder feilformet UTF-8 tegn',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Er du sikker på at du vil slette eiendelen?',
        'error'   		=> 'Det oppstod et problem under sletting av eiendel. Vennligst prøv igjen.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Ingen assets ble valgt, så ingenting ble slettet.',
        'success' 		=> 'Vellykket sletting av eiendel.',
    ],

    'checkout' => [
        'error'   		=> 'Eiendel ble ikke sjekket ut. Prøv igjen',
        'success' 		=> 'Vellykket utsjekk av eiendel.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'not_available' => 'Den eiendelen er ikke tilgjengelig til å sjekkes ut!',
        'no_assets_selected' => 'Du må velge minst én enhet fra listen',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Eiendel ble ikke sjekket inn. Prøv igjen',
        'success' 		=> 'Vellykket innsjekk av eiendel.',
        'user_does_not_exist' => 'Denne brukeren er ugyldig. Vennligst prøv igjen.',
        'already_checked_in'  => 'Den eiendelen er allerede sjekket inn.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Avbryt forespørsel om element',
    ],

];
