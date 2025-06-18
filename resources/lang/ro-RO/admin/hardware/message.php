<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Activul nu exista.',
    'does_not_exist_var' => 'Asset with tag :asset_tag not found.',
    'no_tag' 	         => 'No asset tag provided.',
    'does_not_exist_or_not_requestable' => 'Acest activ nu există sau nu poate fi solicitat.',
    'assoc_users'	 	 => 'Acest activ este predat catre un utilizator si nu se poate sterge. Va rugam verificati activul, dupa care incercati sa-l stergeti iar. ',
    'warning_audit_date_mismatch' 	=> 'This asset\'s next audit date (:next_audit_date) is before the last audit date (:last_audit_date). Please update the next audit date.',
    'labels_generated'   => 'Labels were successfully generated.',
    'error_generating_labels' => 'Error while generating labels.',
    'no_assets_selected' => 'No assets selected.',

    'create' => [
        'error'   		=> 'Activul nu a fost creat, va rugam incercati iar. :(',
        'success' 		=> 'Activul a fost creat. :)',
        'success_linked' => 'Activul cu tag-ul :tag a fost creat cu succes. <strong><a href=":link" style="color: white;">Click aici pentru a vizualiza</a></strong>.',
        'multi_success_linked' => 'Asset with tag :links was created successfully.|:count assets were created succesfully. :links.',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Activul nu a fost actualizat, va rugam incercati iar',
        'success' 			=> 'Activul a fost actualizat.',
        'encrypted_warning' => 'Activă actualizată cu succes, dar câmpurile personalizate criptate nu s-au datorat permisiunilor',
        'nothing_updated'	=>  'Nu au fost selectate câmpuri, deci nimic nu a fost actualizat.',
        'no_assets_selected'  =>  'Nu au fost selectate active, deci nimic nu a fost actualizat.',
        'assets_do_not_exist_or_are_invalid' => 'Activele selectate nu pot fi actualizate.',
    ],

    'restore' => [
        'error'   		=> 'Asset nu a fost restaurat, încercați din nou',
        'success' 		=> 'Activul a fost restaurat cu succes.',
        'bulk_success' 		=> 'Activul a fost restaurat cu succes.',
        'nothing_updated'   => 'Nu au fost selectate active, deci nimic nu a fost restaurat.', 
    ],

    'audit' => [
        'error'   		=> 'Asset audit unsuccessful: :error ',
        'success' 		=> 'Analiza activelor a fost înregistrată cu succes.',
    ],


    'deletefile' => [
        'error'   => 'Fișierul nu a fost șters. Vă rugăm să încercați din nou.',
        'success' => 'Fișierul a fost șters cu succes.',
    ],

    'upload' => [
        'error'   => 'Fișierul nu a fost încărcat. Vă rugăm să încercați din nou.',
        'success' => 'Fișierul a fost încărcat cu succes.',
        'nofiles' => 'Nu ați selectat niciun fișier pentru încărcare sau fișierul pe care încercați să îl încărcați este prea mare',
        'invalidfiles' => 'Unul sau mai multe fișiere este prea mare sau este un tip de fișier care nu este permis. Tipurile de fișiere permise sunt png, gif, jpg, doc, docx, pdf și txt.',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'Unele elemente nu au importat corect.',
        'errorDetail'           => 'Următoarele elemente nu au fost importate din cauza erorilor.',
        'success'               => 'Fișierul dvs. a fost importat',
        'file_delete_success'   => 'Fișierul dvs. a fost șters cu succes',
        'file_delete_error'      => 'Fișierul nu a putut fi șters',
        'file_missing' => 'Fișierul selectat lipsește',
        'file_already_deleted' => 'The file selected was already deleted',
        'header_row_has_malformed_characters' => 'Unul sau mai multe atribute din rândul de antet conțin caractere UTF-8 incorecte',
        'content_row_has_malformed_characters' => 'Unul sau mai multe atribute din primul rând de conținut conțin caractere UTF-8 formatate incorect',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Sunteti sigur ca vreti sa stergeti acest activ?',
        'error'   		=> 'S-a intampinat o problema la stergerea activului. Va rugam incercati iar.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Nu au fost selectate active, deci nimic nu a fost șters.',
        'success' 		=> 'Activul a fost sters.',
    ],

    'checkout' => [
        'error'   		=> 'Activul nu a fost predat, va rugam incercati iar',
        'success' 		=> 'Activul a fost predat.',
        'user_does_not_exist' => 'Utilizatorul este invalid. Va rugam incercati iar.',
        'not_available' => 'Activul respectiv nu este disponibil pentru checkout!',
        'no_assets_selected' => 'Trebuie să selectați cel puțin un articol din lista',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'Activul nu a fost primit, va rugam incercati iar',
        'success' 		=> 'Activul a fost primit.',
        'user_does_not_exist' => 'Utilizatorul este invalid. Va rugam incercati iar.',
        'already_checked_in'  => 'Activul respectiv este deja înregistrat.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Anulează această cerere de articol',
    ],

];
