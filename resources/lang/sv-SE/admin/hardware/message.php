<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Tillgång existerar inte.',
    'does_not_exist_var' => 'Tillgång med taggen :asset_tag hittades inte.',
    'no_tag' 	         => 'Ingen tillgångstagg angiven.',
    'does_not_exist_or_not_requestable' => 'Den tillgången finns inte eller är inte tillgänglig.',
    'assoc_users'	 	 => 'Denna tillgång har checkats ut till en användare och kan inte raderas. Kontrollera tillgången först och försök sedan radera igen. ',
    'warning_audit_date_mismatch' 	=> 'Nästa inventeringsdatum för denna tillgång (:next_audit_date) är före det senaste inventeringsdatumet (:last_audit_date). Vänligen uppdatera nästa inventeringsdatum.',
    'labels_generated'   => 'Etiketter har genererats.',
    'error_generating_labels' => 'Ett fel uppstod vid generering av etiketter.',
    'no_assets_selected' => 'Inga tillgångar valda.',

    'create' => [
        'error'   		=> 'Tillgången skapades inte :( Försök igen.',
        'success' 		=> 'Tillgången skapades.',
        'success_linked' => 'Tillgången med taggen :tag har skapats. <strong><a href=":link" style="color: white;">Klicka här för att visa</a></strong>.',
        'multi_success_linked' => 'Tillgång med taggen :links skapades.|:count tillgångar skapades. :links.',
        'partial_failure' => 'En tillgång kunde inte skapas. Anledning: :failures|:count tillgångar kunde inte skapas. Anledning: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Tillgången kunde inte uppdateras, försök igen',
        'success' 			=> 'Tillgång uppdaterad.',
        'encrypted_warning' => 'Tillgången uppdaterades, men krypterade egenanpassade fält kunde inte uppdateras p.g.a. behörigheter',
        'nothing_updated'	=>  'Inga fält valdes. Ingenting uppdaterades.',
        'no_assets_selected'  =>  'Inga tillgångar valdes. Ingenting uppdaterades.',
        'assets_do_not_exist_or_are_invalid' => 'Valda tillgångar kan inte uppdateras.',
    ],

    'restore' => [
        'error'   		=> 'Tillgången återställdes inte, försök igen',
        'success' 		=> 'Tillgång återställd.',
        'bulk_success' 		=> 'Återställning av tillgången lyckades.',
        'nothing_updated'   => 'Inga tillgångar valda. Ingenting återställdes.', 
    ],

    'audit' => [
        'error'   		=> 'Tillgångsinventeringen misslyckades: :error ',
        'success' 		=> 'Inventeringen av tillgången har loggats.',
    ],


    'deletefile' => [
        'error'   => 'Filen kunde inte tas bort. Var god försök igen.',
        'success' => 'Filen har tagits bort.',
    ],

    'upload' => [
        'error'   => 'Fil(er) kunde inte laddas upp. Var god försök igen.',
        'success' => 'Fil(er) har laddats upp.',
        'nofiles' => 'Du valde inte några filer för uppladdning, eller så är filen du försöker ladda upp för stor',
        'invalidfiles' => 'En eller fler av dina filer är för stora eller är en filtyp som inte är tillåten. Tillåtna filtyper är png, gif, jpg, doc, docx, pdf och txt.',
    ],

    'import' => [
        'import_button'         => 'Bearbeta import',
        'error'                 => 'Vissa objekt importerades inte korrekt.',
        'errorDetail'           => 'Följande objekt importerades inte på grund av fel.',
        'success'               => 'Din fil har importerats',
        'file_delete_success'   => 'Din fil har tagits bort',
        'file_delete_error'      => 'Filen kunde inte raderas',
        'file_missing' => 'Den valda filen saknas',
        'file_already_deleted' => 'Den valda filen har redan tagits bort',
        'header_row_has_malformed_characters' => 'Ett eller flera attribut i rubrikraden innehåller felaktigt formatterade UTF-8-tecken',
        'content_row_has_malformed_characters' => 'Ett eller flera attribut i den första raden av innehållet innehåller felaktigt formatterade UTF-8-tecken',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Är du säker på att du vill radera den här tillgången?',
        'error'   		=> 'Det gick inte att ta bort tillgången. Var god försök igen.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Inga tillgångar valdes. Ingenting togs bort.',
        'success' 		=> 'Tillgång raderad.',
    ],

    'checkout' => [
        'error'   		=> 'Tillgången kunde inte checkas ut, försök igen',
        'success' 		=> 'Tillgången har checkats ut.',
        'user_does_not_exist' => 'Den användaren är ogiltig. Var god försök igen.',
        'not_available' => 'Den valda tillgången är inte tillgänglig för utcheckning.',
        'no_assets_selected' => 'Du måste välja minst en tillgång från listan',
    ],

    'multi-checkout' => [
        'error'   => 'Tillgången har inte checkats ut, försök igen|Tillgångarna har inte checkats ut, försök igen',
        'success' => 'Utcheckning av tillgången lyckades.|Utcheckning av tillgångarna lyckades.',
    ],

    'checkin' => [
        'error'   		=> 'Tillgången kunde inte checkas in, försök igen',
        'success' 		=> 'Tillgången har checkats in.',
        'user_does_not_exist' => 'Användaren är ogiltig. Var god försök igen.',
        'already_checked_in'  => 'Tillgången är redan incheckad.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Avbryt objektbegäran',
    ],

];
