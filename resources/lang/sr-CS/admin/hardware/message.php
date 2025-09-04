<?php

return [

    'undeployable' 		 => 'Sledeća imovina ne može biti razmeštena i uklonjena je sa spiska za zaduživanje: :asset_tags',
    'does_not_exist' 	 => 'Imovina ne postoji.',
    'does_not_exist_var' => 'Nije pronađena imovina za oznakom :asset_tag.',
    'no_tag' 	         => 'Nije navedena oznaka imovine.',
    'does_not_exist_or_not_requestable' => 'Imovina ne postoji ili se ne može zatražiti.',
    'assoc_users'	 	 => 'Ovaj je resurs trenutno poveren korisniku i ne može se izbrisati. Najprije proverite resurs, a zatim ponovo pokušajte brisanje. ',
    'warning_audit_date_mismatch' 	=> 'Naredni datum popisa ove imovine (:next_audit_date) je pre poslednjeg datuma popisa (:last_audit_date). Molim vas izmenite datum narednog popisa.',
    'labels_generated'   => 'Oznake su uspešno generisane.',
    'error_generating_labels' => 'Greška prilikom generisanja oznaka.',
    'no_assets_selected' => 'Nijedna imovina nije izabrana.',

    'create' => [
        'error'   		=> 'Imovina, resurs nije kreiran, pokušajte ponovo. :(',
        'success' 		=> 'Imovina, resurs uspešno kreiran. :)',
        'success_linked' => 'Imovina sa oznakom :tag je uspešno napravljena. <strong><a href=":link" style="color: white;">Kliknite ovde za pregled</a></strong>.',
        'multi_success_linked' => 'Imovina sa oznakom :links je uspešno dodata.|:count imovine je uspešno dodato. :links.',
        'partial_failure' => 'Imovina nije mogla biti dodata. Razlog: :failures|:count imovine nisu mogle biti dodate. Razlozi: :failures',
        'target_not_found' => [
            'user' => 'Dodeljeni korisnik nije mogao biti pronađen.',
            'asset' => 'Dodeljena imovina nije mogla biti pronađena.',
            'location' => 'Dodeljena lokacija nije mogla biti pronađena.',
        ],
    ],

    'update' => [
        'error'   			=> 'Imovina nije ažurirana, pokušajte ponovo',
        'success' 			=> 'Imovina je uspešno ažurirana.',
        'encrypted_warning' => 'Imovina je uspešno izmenjena, ali enkriptovana prilagođena polja nisu zbog ovlašćenja',
        'nothing_updated'	=>  'Nije odabrano nijedno polje, tako da ništa nije ažurirano.',
        'no_assets_selected'  =>  'Nije odabrano nijedno polje, tako da ništa nije ažurirano.',
        'assets_do_not_exist_or_are_invalid' => 'Izabrana imovina ne može biti izmenjena.',
    ],

    'restore' => [
        'error'   		=> 'Imovina nije obnovljena, pokušajte ponovo',
        'success' 		=> 'Imovina je uspešno obnovljena.',
        'bulk_success' 		=> 'Imovina je uspešno vraćena.',
        'nothing_updated'   => 'Nijedna imovina nije izabrana, zato ništa nije vraćeno.', 
    ],

    'audit' => [
        'error'   		=> 'Neuspešan popis imovine: :error ',
        'success' 		=> 'Provera imovine uspešno je evidentirana.',
    ],


    'deletefile' => [
        'error'   => 'Fajl nije izbrisan. Molim pokušajte ponovo.',
        'success' => 'Fajl uspešno obrisan.',
    ],

    'upload' => [
        'error'   => 'Fajl(ovi) nisu preneseni. Pokušajte ponovo.',
        'success' => 'Fajl(ovi) uspešno preneseni. Pokušajte ponovo.',
        'nofiles' => 'Niste odabrali nijedan fajl za prenos ili je fajl prevelik',
        'invalidfiles' => 'Jedn ili više fajlova su preveliki ili je vrsta fajla koja nije dopuštena. Dopuštene vrste su png, gif, jpg, doc, docx, pdf i txt.',
    ],

    'import' => [
        'import_button'         => 'Izvrši uvoz',
        'error'                 => 'Neke stavke nisu pravilno uvezene.',
        'errorDetail'           => 'Sledeće stavke nisu uvezene zbog grešaka.',
        'success'               => 'Vaš fajl je importovan',
        'file_delete_success'   => 'Vaš je fajl uspešno izbrisan',
        'file_delete_error'      => 'Fajl nime moguće izbrisati',
        'file_missing' => 'Nedostaje izabrana datoteka',
        'file_already_deleted' => 'Izabrana datoteka je već obrisana',
        'header_row_has_malformed_characters' => 'Jedan ili više atributa u redu zaglavlja sadrži loše formatirane UTF-8 karaktere',
        'content_row_has_malformed_characters' => 'Jedan ili više atributa u prvom redu sadržaja sadrži loše formatirane UTF-8 karaktere',
        'transliterate_failure' => 'Transliteracija iz :encoding u UTF8 nije uspela zbog neispravnih unetih karaktera'
    ],


    'delete' => [
        'confirm'   	=> 'Jeste li sigurni da želite izbrisati ovaj resurs?',
        'error'   		=> 'Došlo je do problema s brisanjem resursa. Molim pokušajte ponovo.',
        'assigned_to_error' => '{1}Oznaka imovine: :asset_tag je trenutno zadužena. Razduži ovaj uređaj pre brisanja.|[2,*]Oznake imovine: :asset_tag su trenutno zadužene. Razduži ove uređaje pre brisanja.',
        'nothing_updated'   => 'Nijedna imovina nije odabrana, tako da ništa nije izbrisano.',
        'success' 		=> 'Imovina je uspešno obrisana.',
    ],

    'checkout' => [
        'error'   		=> 'Imovina nije odjavljena, pokušajte ponovo',
        'success' 		=> 'Imovina je uspešno odjavljena.',
        'user_does_not_exist' => 'Korisnik je nevažeći. Molim pokušajte ponovo.',
        'not_available' => 'That asset is not available for checkout!',
        'no_assets_selected' => 'Morate odabrati barem jednu imovinu s popisa',
    ],

    'multi-checkout' => [
        'error'   => 'Imovina nije zadužena, molim vas pokušajte ponovo|Imovine nisu zadužene, molim vas pokušajte ponovo',
        'success' => 'Imovina je uspešno zadužena.|Imovine su uspešno zadužene.',
    ],

    'checkin' => [
        'error'   		=> 'Imovina nije prijavljena. Pokušajte ponovo',
        'success' 		=> 'Imovina je uspešno prijavljena.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim pokušajte ponovo.',
        'already_checked_in'  => 'Imovina je već prijavljena.',

    ],

    'requests' => [
        'error'   		=> 'Zahtev nije bio uspešan, pokušajte ponovo.',
        'success' 		=> 'Zahtev je uspešno podnet.',
        'canceled'      => 'Zahtev je uspešno poništen.',
        'cancel'        => 'Otkažite zahtev za ovu stavku',
    ],

];
