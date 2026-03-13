<?php

return [

    'undeployable' 		 => 'Naslednjih sredstev ni mogoče namestiti in so bila odstranjena iz blagajne: :asset_tags',
    'does_not_exist' 	 => 'Sredstvo ne obstaja.',
    'does_not_exist_var' => 'Sredstvo z oznako :oznaka_sredstva ni bilo najdeno.',
    'no_tag' 	         => 'Oznaka sredstva ni podana.',
    'does_not_exist_or_not_requestable' => 'To sredstvo ne obstaja ali ga ni mogoče zahtevati.',
    'assoc_users'	 	 => 'To sredstvo je trenutno izdano uporabniku in ga ni mogoče izbrisati. Najprej preverite sredstvo in poskusite znova izbrisati. ',
    'warning_audit_date_mismatch' 	=> 'Naslednji datum revizije tega sredstva (:next_audit_date) je pred zadnjim datumom revizije (:last_audit_date). Prosimo, posodobite naslednji datum revizije.',
    'labels_generated'   => 'Oznake so bile uspešno ustvarjene.',
    'error_generating_labels' => 'Napaka pri ustvarjanju oznak.',
    'no_assets_selected' => 'Ni izbranih sredstev.',

    'create' => [
        'error'   		=> 'Sredstvo ni bilo ustvarjeno, poskusite znova. :(',
        'success' 		=> 'Sredstvo je uspešno ustvarjeno. :)',
        'success_linked' => 'Sredstvo z oznako :tag je bilo uspešno ustvarjeno. <strong><a href=":link" style="color: white;">Kliknite tukaj za ogled</a></strong>.',
        'multi_success_linked' => 'Sredstvo z oznako :links je bilo uspešno ustvarjeno.|:count sredstev je bilo uspešno ustvarjenih. :links.',
        'partial_failure' => 'Sredstva ni bilo mogoče ustvariti. Razlog: :failures|:count sredstev ni bilo mogoče ustvariti. Razlogi: :failures',
        'target_not_found' => [
            'user' => 'Dodeljenega uporabnika ni bilo mogoče najti.',
            'asset' => 'Dodeljenega sredstva ni bilo mogoče najti.',
            'location' => 'Dodeljene lokacije ni bilo mogoče najti.',
        ],
    ],

    'update' => [
        'error'   			=> 'Sredstvo ni bilo posodobljeno, poskusite znova',
        'success' 			=> 'Sredstvo je uspešno posodobljeno.',
        'encrypted_warning' => 'Sredstvo je bilo uspešno posodobljeno, vendar šifrirana polja po meri niso bila zaradi dovoljenj',
        'nothing_updated'	=>  'Nobeno polje ni bilo izbrana, zato nebo nič posodobljeno.',
        'no_assets_selected'  =>  'Nobena sredstva niso bila izbrana, zato ni bilo nič izbrisanih.',
        'assets_do_not_exist_or_are_invalid' => 'Izbrana sredstva ni mogoče posodobiti.',
    ],

    'restore' => [
        'error'   		=> 'Sredstvo ni bilo obnovljeno, poskusite znova',
        'success' 		=> 'Sredstvo je bilo uspešno obnovljeno.',
        'bulk_success' 		=> 'Sredstvo je bilo uspešno obnovljeno.',
        'nothing_updated'   => 'Nobeno sredstvo ni bilo izbran, zato nebo nič obnovljeno.', 
    ],

    'audit' => [
        'error'   		=> 'Revizija sredstev ni bila uspešna: :error ',
        'success' 		=> 'Revizija sredstva je uspešno zabeležena.',
    ],


    'deletefile' => [
        'error'   => 'Datoteka ni izbrisana. Prosim poskusite ponovno.',
        'success' => 'Datoteka je uspešno izbrisana.',
    ],

    'upload' => [
        'error'   => 'Datoteka(e) niso naložene. Prosim poskusite ponovno.',
        'success' => 'Datoteka(e) so bile uspešno naložene.',
        'nofiles' => 'Niste izbrali nobenih datotek za nalaganje, ali je datoteka ki jo poskušate naložiti prevelika',
        'invalidfiles' => 'Ena ali več vaših datotek je prevelika ali pa je tip datoteke, ki ni dovoljen. Dovoljeni tipi datotek so png, gif, jpg, doc, docx, pdf in txt.',
    ],

    'import' => [
        'import_button'         => 'Uvoz postopka',
        'error'                 => 'Nekateri elementi niso bili pravilno uvoženi.',
        'errorDetail'           => 'Naslednji elementi niso bili uvoženi zaradi napak.',
        'success'               => 'Vaša datoteka je bila uvožena',
        'file_delete_success'   => 'Vaša datoteka je bila uspešno izbrisana',
        'file_delete_error'      => 'Datoteke ni bilo mogoče izbrisati',
        'file_missing' => 'Izbrana datoteka manjka',
        'file_already_deleted' => 'Izbrana datoteka je bila že izbrisana',
        'header_row_has_malformed_characters' => 'Eden ali več atributov v vrstici glave vsebuje napačno oblikovane znake UTF-8',
        'content_row_has_malformed_characters' => 'Eden ali več atributov v prvi vrstici vsebine vsebuje napačno oblikovane znake UTF-8',
        'transliterate_failure' => 'Prečrkovanje iz :encoding v UTF-8 ni uspelo zaradi neveljavnih znakov v vnosu'
    ],


    'delete' => [
        'confirm'   	=> 'Ali ste prepričani, da želite izbrisati to sredstvo?',
        'error'   		=> 'Prišlo je do težave z izbrisom sredstva. Prosim poskusite ponovno.',
        'assigned_to_error' => '{1}Oznaka sredstva: :asset_tag je trenutno rezervirana. Pred brisanjem preverite to napravo.|[2,*]Oznake sredstev: :asset_tag so trenutno rezervirane. Pred brisanjem preverite te naprave.',
        'nothing_updated'   => 'Nobena sredstva niso bila izbrana, zato ni bilo nič izbrisanih.',
        'success' 		=> 'Sredstvo je bilo uspešno izbrisano.',
    ],

    'checkout' => [
        'error'   		=> 'Sredstvo ni bila izdano, poskusite znova',
        'success' 		=> 'Sredstvo je bilo uspešno izdano.',
        'user_does_not_exist' => 'Ta uporabnik ni veljaven. Prosim poskusite ponovno.',
        'not_available' => 'To sredstvo ni na voljo za izdajo!',
        'no_assets_selected' => 'Na seznamu morate izbrati vsaj eno sredstev',
    ],

    'multi-checkout' => [
        'error'   => 'Sredstvo ni bilo rezervirano, poskusite znova|Sredstva niso bila rezervirana, poskusite znova',
        'success' => 'Sredstvo uspešno rezervirano.|Sredstva uspešno rezervirana.',
    ],

    'checkin' => [
        'error'   		=> 'Sredstev ni bilo prevzeto, poskusite znova',
        'success' 		=> 'Sredstev je bilo uspešno prevzeta.',
        'user_does_not_exist' => 'Ta uporabnik je neveljaven. Prosim poskusite ponovno.',
        'already_checked_in'  => 'Ta sredstev je že izdana.',

    ],

    'requests' => [
        'error'   		=> 'Zahteva ni bila uspešna, poskusite znova.',
        'success' 		=> 'Zahteva uspešno poslana.',
        'canceled'      => 'Zahteva je bila uspešno preklicana.',
        'cancel'        => 'Prekliči to zahtevo za predmet',
    ],

];
