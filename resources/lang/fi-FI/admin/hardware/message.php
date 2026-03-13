<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Laitetta ei löydy.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Tätä laitetta ei ole tai se ei ole pyydettävissä.',
    'assoc_users'	 	 => 'Tämä laite on luovutettu käyttäjälle joten sitä ei voida poistaa. Palauta laite ensin käyttäjältä ja yritä uudelleen. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Laitetta ei luotu, yritä uudelleen. :(',
        'success' 		=> 'Laite luotiin onnistuneesti. :)',
        'success_linked' => 'Laite tunnisteella :tag luotiin onnistuneesti. <strong><a href=":link" style="color: white;">Klikkaa tästä nähdäksesi</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Laitetta ei päivitetty, yritä uudelleen',
        'success' 			=> 'Laite päivitetty onnistuneesti.',
        'encrypted_warning' => 'Laite päivitettiin onnistuneesti, mutta salatut mukautetut kentät eivät johtuneet käyttöoikeuksista',
        'nothing_updated'	=>  'Mitään kenttiä ei valittu, joten mitään ei päivitetty.',
        'no_assets_selected'  =>  'Laitetta ei ollut valittuna, joten mitään ei muutettu.',
        'assets_do_not_exist_or_are_invalid' => 'Valittuja sisältöjä ei voi päivittää.',
    ],

    'restore' => [
        'error'   		=> 'Laitetta ei palautettu, ole hyvä ja yritä uudelleen',
        'success' 		=> 'Laite palautettiin onnistuneesti.',
        'bulk_success' 		=> 'Laite palautettiin onnistuneesti.',
        'nothing_updated'   => 'Laitetteita ei ollut valittuna, joten mitään ei palautettu.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Laitteen tarkastus kirjattu.',
    ],


    'deletefile' => [
        'error'   => 'Tiedostoa ei poistettu. Ole hyvä ja yritä uudelleen.',
        'success' => 'Tiedosto poistettiin onnistuneesti.',
    ],

    'upload' => [
        'error'   => 'Tiedostoja ei lähetetty. Ole hyvä ja yritä uudelleen.',
        'success' => 'Tiedostot lähetettiin onnistuneesti.',
        'nofiles' => 'Et ole valinnut lähetettäviä tiedostoja tai lataamasi tiedosto on liian suuri',
        'invalidfiles' => 'Yksi tai useampia tiedostoja on liian iso tai sen tiedostotyyppi ei ole sallittu. Sallitut tiedostotyypit ovat png, gif, jpg, doc, docx, pdf, ja txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Joitakin nimikkeitä ei tuotu oikein.',
        'errorDetail'           => 'Seuraavia nimikkeitä ei tuotu virheiden vuoksi.',
        'success'               => 'Tiedostosi on tuotu',
        'file_delete_success'   => 'Tiedosto on poistettu onnistuneesti',
        'file_delete_error'      => 'Tiedostoa ei voitu poistaa',
        'file_missing' => 'Valittu tiedosto puuttuu',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Yksi tai useampi otsikkorivin attribuutti sisältää epämuodostuneita UTF-8 merkkejä',
        'content_row_has_malformed_characters' => 'Yksi tai useampi ensimmäisen sisältörivin attribuutti sisältää epämuodostuneita UTF-8 merkkejä',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Oletko varma että haluat poistaa tämän laitteen?',
        'error'   		=> 'Laitteen poistamisessa tapahtui virhe. Yritä uudelleen.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Laitetta ei ollut valittuna, joten mitään ei poistettu.',
        'success' 		=> 'Laite poistettu onnistuneesti.',
    ],

    'checkout' => [
        'error'   		=> 'Laitteen luovutus epäonnistui, yritä uudelleen',
        'success' 		=> 'Laite luovutettu onnistuneesti.',
        'user_does_not_exist' => 'Käyttäjä on virheellinen. Yritä uudelleen.',
        'not_available' => 'Laite ei ole luovutettavissa!',
        'no_assets_selected' => 'Valitse ainakin yksi laite listasta',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Laitteen palautus epäonnistui, yritä uudelleen',
        'success' 		=> 'Laite palautettu onnistuneesti.',
        'user_does_not_exist' => 'Käyttäjä on virheellinen. Yritä uudelleen.',
        'already_checked_in'  => 'Tämä laite on jo palautettu.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Peruuta tämä pyyntö',
    ],

];
