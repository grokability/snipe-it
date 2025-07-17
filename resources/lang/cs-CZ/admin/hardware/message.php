<?php

return [

    'undeployable' 		 => 'Tyto položky nebylo možné přiřadit, proto byly odstraněny z výdeje: :asset_tags',
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
        'multi_success_linked' => 'Zařízení se štítkem :links bylo úspěšně vytvořeno.|:count zařízení bylo úspěšně vytvořeno. :links.
',
        'partial_failure' => 'Zařízení se nepodařilo vytvořit. Důvod: :failures|:count zařízení se nepodařilo vytvořit. Důvody: :failures',
        'target_not_found' => [
            'user' => 'Přidělený uživatel nebyl nalezen.',
            'asset' => 'Přidělené zařízení nebylo nalezeno.',
            'location' => 'Přiřazené umístění se nepodařilo najít.',
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
        'error'   		=> 'Audit zařízení byl neúspěšný: :error',
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
        'import_button'         => 'Import procesu',
        'error'                 => 'Některé položky nebyly správně importovány.',
        'errorDetail'           => 'Následující položky nebyly importovány kvůli chybám.',
        'success'               => 'Váš soubor byl importován',
        'file_delete_success'   => 'Váš soubor byl úspěšně odstraněn',
        'file_delete_error'      => 'Soubor nelze odstranit',
        'file_missing' => 'Vybraný soubor chybí',
        'file_already_deleted' => 'Vybraný soubor již byl odstraněn',
        'header_row_has_malformed_characters' => 'Jeden nebo více sloupců obsahuje v záhlaví poškozené UTF-8 znaky',
        'content_row_has_malformed_characters' => 'Jedna nebo více hodnot v prvním řádku obsahu obsahuje poškozené UTF-8 znaky',
        'transliterate_failure' => 'Přepis z :encoding do UTF-8 selhal kvůli neplatným znakům ve vstupu.'
    ],


    'delete' => [
        'confirm'   	=> 'Opravdu si přejete tento majetek odstranit?',
        'error'   		=> 'Nepodařilo se nám tento majetek odstranit. Zkuste to prosím znovu.',
        'assigned_to_error' => '{1}Zařízení s označením :asset_tag je právě zapůjčeno. Před odstraněním je nutné ho vrátit.|[2,*]Zařízení s označeními :asset_tag jsou právě zapůjčena. Před odstraněním je nutné je vrátit.',
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
        'error'   => 'Zařízení nebylo zapůjčeno, zkuste to prosím znovu|Zařízení nebyla zapůjčena, zkuste to prosím znovu

',
        'success' => 'Zařízení bylo úspěšně zapůjčeno.|Zařízení byla úspěšně zapůjčena.',
    ],

    'checkin' => [
        'error'   		=> 'Majetek nebyl převzat. Zkuste to prosím znovu',
        'success' 		=> 'Majetek byl v pořádku převzat.',
        'user_does_not_exist' => 'Tento uživatel je neplatný. Zkuste to prosím znovu.',
        'already_checked_in'  => 'Tento majetek je již předaný.',

    ],

    'requests' => [
        'error'   		=> 'Požadavek nebyl úspěšný, zkuste to prosím znovu.',
        'success' 		=> 'Žádost byla úspěšně odeslána.',
        'canceled'      => 'Žádost byla úspěšně zrušena.',
        'cancel'        => 'Zrušit tuto žádost o položku',
    ],

];
