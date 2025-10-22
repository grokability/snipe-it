<?php

return [

    'undeployable' 		 => 'A következő eszközök nem telepíthetők, ezért eltávolításra kerültek a kölcsönzésből: :asset_tags',
    'does_not_exist' 	 => 'Eszköz nem létezik.',
    'does_not_exist_var' => 'A :asset_tag jelzésű eszköz nem található.',
    'no_tag' 	         => 'Nem lett megadva eszközcímke.',
    'does_not_exist_or_not_requestable' => 'Az eszköz nem létezik vagy nem igényelhető.',
    'assoc_users'	 	 => 'Ez az eszköz jelenleg ki van jelölve egy felhasználónak, és nem törölhető. Kérjük, először ellenőrizze az eszközt, majd próbálja meg újra törölni.',
    'warning_audit_date_mismatch' 	=> 'Ennek az eszköznek a következő leltározási dátuma (:next_audit_date) korábbi, mint az utolsó leltározás dátuma (:last_audit_date). Kérjük, frissítse a következő leltározás dátumát.',
    'labels_generated'   => 'A címkék sikeresen létre lettek hozva.',
    'error_generating_labels' => 'Hiba a címkék generálásakor.',
    'no_assets_selected' => 'Nincsenek kijelölt eszközök.',

    'create' => [
        'error'   		=> 'Az eszköz nem jött létre, próbálkozzon újra. :(',
        'success' 		=> 'Az eszköz sikeresen létrehozva. :)',
        'success_linked' => 'Eszköz a :tag azonosítóval sikeresen létrehozva. <strong><a href=":link" style="color: white;">Kattintson ide a megtekintéshez</a></strong>.',
        'multi_success_linked' => 'A :links eszköz sikeresen létrehozva.|:count eszköz sikeresen létrehozva: :links.',
        'partial_failure' => 'Egy eszköz létrehozása nem sikerült. Oka: :failures|:count eszköz létrehozása nem sikerült. Okok: :failures',
        'target_not_found' => [
            'user' => 'A kijelölt felhasználó nem található.',
            'asset' => 'A kijelölt eszköz nem található.',
            'location' => 'A kijelölt helyszín nem található.',
        ],
    ],

    'update' => [
        'error'   			=> 'Az eszköz nem frissült, próbálkozzon újra',
        'success' 			=> 'Az eszköz sikeresen frissült.',
        'encrypted_warning' => 'Az eszköz sikeresen frissítve, de a titkosított egyéni mezők a jogosultságok miatt nem frissültek',
        'nothing_updated'	=>  'Nem választottak ki mezőket, így semmi sem frissült.',
        'no_assets_selected'  =>  'Egyetlen eszköz sem volt kiválasztva, így semmi sem frissült.',
        'assets_do_not_exist_or_are_invalid' => 'A kiválasztott eszközök nem frissíthetők.',
    ],

    'restore' => [
        'error'   		=> 'Az eszköz nem állt helyre, kérjük, próbálkozzon újra',
        'success' 		=> 'Az eszköz sikeresen visszaállítva.',
        'bulk_success' 		=> 'Az eszköz sikeresen visszaállítva.',
        'nothing_updated'   => 'Nem voltak eszközök kiválasztva, így semmi sem lett visszállítva.', 
    ],

    'audit' => [
        'error'   		=> 'Az eszköz leltározása sikertelen: :error ',
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
        'import_button'         => 'Importálás feldolgozása',
        'error'                 => 'Egyes elemek nem importáltak helyesen.',
        'errorDetail'           => 'Az alábbi elemeket nem importálták hiba miatt.',
        'success'               => 'A fájlt importálta',
        'file_delete_success'   => 'A fájlt sikeresen törölték',
        'file_delete_error'      => 'A fájlt nem sikerült törölni',
        'file_missing' => 'A kijelölt fájl nem található',
        'file_already_deleted' => 'A kiválasztott fájl már törlésre került',
        'header_row_has_malformed_characters' => 'A fejlécsorban egy vagy több attribútum hibás formájú UTF-8 karaktereket tartalmaz',
        'content_row_has_malformed_characters' => 'A tartalom első sorában egy vagy több attribútum hibás formájú UTF-8 karaktereket tartalmaz',
        'transliterate_failure' => 'A :encoding kódolásból UTF-8-ra való átírás sikertelen az érvénytelen karakterek miatt'
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
        'error'   => 'Az eszköz nem lett kivéve, kérjük, próbálja újra|Az eszközök nem lettek kivéve, kérjük, próbálja újra',
        'success' => 'Az eszköz sikeresen kivéve.|Az eszközök sikeresen kivéve.',
    ],

    'checkin' => [
        'error'   		=> 'Az eszköz nem lett bejelölve, próbálkozzon újra',
        'success' 		=> 'Az Asset sikeresen ellenőrzött.',
        'user_does_not_exist' => 'Ez a felhasználó érvénytelen. Kérlek próbáld újra.',
        'already_checked_in'  => 'Ez az eszköz már be van jelölve.',

    ],

    'requests' => [
        'error'   		=> 'A kérés sikertelen volt, kérjük, próbálja újra.',
        'success' 		=> 'A kérés sikeresen elküldve.',
        'canceled'      => 'A kérés sikeresen törölve.',
        'cancel'        => 'Eszközigénylés visszavonása',
    ],

];
