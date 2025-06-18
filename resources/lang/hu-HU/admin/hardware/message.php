<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Eszköz nem létezik.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Az eszköz nem létezik vagy nem igényelhető.',
    'assoc_users'	 	 => 'Ez az eszköz jelenleg ki van jelölve egy felhasználónak, és nem törölhető. Kérjük, először ellenőrizze az eszközt, majd próbálja meg újra törölni.',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Az eszköz nem jött létre, próbálkozzon újra. :(',
        'success' 		=> 'Az eszköz sikeresen létrehozva. :)',
        'success_linked' => 'Eszköz a :tag azonosítóval sikeresen létrehozva. <strong><a href=":link" style="color: white;">Kattintson ide a megtekintéshez</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Az eszköz nem frissült, próbálkozzon újra',
        'success' 			=> 'Az eszköz sikeresen frissült.',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'Nem választottak ki mezőket, így semmi sem frissült.',
        'no_assets_selected'  =>  'Egyetlen eszköz sem volt kiválasztva, így semmi sem frissült.',
        'assets_do_not_exist_or_are_invalid' => 'Selected assets cannot be updated.',
    ],

    'restore' => [
        'error'   		=> 'Az eszköz nem állt helyre, kérjük, próbálkozzon újra',
        'success' 		=> 'Az eszköz sikeresen visszaállítva.',
        'bulk_success' 		=> 'Az eszköz sikeresen visszaállítva.',
        'nothing_updated'   => 'Nem voltak eszközök kiválasztva, így semmi sem lett visszállítva.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Az eszközellenőrzés sikeresen be van jelentkezve.',
    ],


    'deletefile' => [
        'error'   => 'A fájl nem törölve. Kérlek próbáld újra.',
        'success' => 'A fájl sikeresen törölve.',
    ],

    'upload' => [
        'error'   => 'Fel nem töltött fájl (ok). Kérlek próbáld újra.',
        'success' => 'Fájl (ok) sikeresen feltöltve.',
        'nofiles' => 'Nem választottál fel fájlokat a feltöltéshez, vagy a fájl, amelyet feltölteni próbálsz, túl nagy',
        'invalidfiles' => 'Egy vagy több fájl túl nagy vagy egy filetype, amely nem megengedett. Az engedélyezett fájltípusok png, gif, jpg, doc, docx, pdf és txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Egyes elemek nem importáltak helyesen.',
        'errorDetail'           => 'Az alábbi elemeket nem importálták hiba miatt.',
        'success'               => 'A fájlt importálta',
        'file_delete_success'   => 'A fájlt sikeresen törölték',
        'file_delete_error'      => 'A fájlt nem sikerült törölni',
        'file_missing' => 'A kijelölt fájl nem található',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'A fejlécsorban egy vagy több attribútum hibás formájú UTF-8 karaktereket tartalmaz',
        'content_row_has_malformed_characters' => 'A tartalom első sorában egy vagy több attribútum hibás formájú UTF-8 karaktereket tartalmaz',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Biztos benne, hogy törli ezt az elemet?',
        'error'   		=> 'Hiba történt az eszköz törlése közben. Kérlek próbáld újra.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Nincsenek eszközök kijelölve, így semmit sem töröltek.',
        'success' 		=> 'Az eszköz sikeresen törölve lett.',
    ],

    'checkout' => [
        'error'   		=> 'Az eszköz nem lett kijelölve, próbáld újra',
        'success' 		=> 'A készlet sikeresen ki lett állítva.',
        'user_does_not_exist' => 'Ez a felhasználó érvénytelen. Kérlek próbáld újra.',
        'not_available' => 'Ez az eszköz nem áll rendelkezésre pénztárnál!',
        'no_assets_selected' => 'Ki kell választania legalább egy elemet a listából',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Az eszköz nem lett bejelölve, próbálkozzon újra',
        'success' 		=> 'Az Asset sikeresen ellenőrzött.',
        'user_does_not_exist' => 'Ez a felhasználó érvénytelen. Kérlek próbáld újra.',
        'already_checked_in'  => 'Ez az eszköz már be van jelölve.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Eszközigénylés visszavonása',
    ],

];
