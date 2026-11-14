<?php

return [

    'undeployable' => 'Sledeća imovina ne može biti razmeštena i uklonjena je sa spiska za zaduživanje: :asset_tags',
    'does_not_exist' => 'Imovina ne postoji.',
    'does_not_exist_var' => 'Nije pronađena imovina za oznakom :asset_tag.',
    'no_tag' => 'Nije navedena oznaka imovine.',
    'does_not_exist_or_not_requestable' => 'Imovina ne postoji ili se ne može zatražiti.',
    'assoc_users' => 'Ovaj je resurs trenutno poveren korisniku i ne može se izbrisati. Najprije proverite resurs, a zatim ponovo pokušajte brisanje. ',
    'warning_audit_date_mismatch' => 'Naredni datum popisa ove imovine (:next_audit_date) je pre poslednjeg datuma popisa (:last_audit_date). Molim vas izmenite datum narednog popisa.',
    'labels_generated' => 'Oznake su uspešno generisane.',
    'error_generating_labels' => 'Greška prilikom generisanja oznaka.',
    'no_assets_selected' => 'Nijedna imovina nije izabrana.',

    'create' => [
        'error' => 'Imovina, resurs nije kreiran, pokušajte ponovo. :(',
        'success' => 'Imovina, resurs uspešno kreiran. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
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
        'error' => 'Imovina nije ažurirana, pokušajte ponovo',
        'success' => 'Imovina je uspešno ažurirana.',
        'encrypted_warning' => 'Imovina je uspešno izmenjena, ali enkriptovana prilagođena polja nisu zbog ovlašćenja',
        'nothing_updated' => 'Nije odabrano nijedno polje, tako da ništa nije ažurirano.',
        'no_assets_selected' => 'Nije odabrano nijedno polje, tako da ništa nije ažurirano.',
        'assets_do_not_exist_or_are_invalid' => 'Izabrana imovina ne može biti izmenjena.',
    ],

    'bulk_update' => [
        'success' => 'Imovina je uspešno izmenjena.|:count imovine su uspešno izmenjene.',
        'partial' => ':success imovine su uspešno izmenjene, :failed neuspešno. Pogledajte niz rezultata za više detalja.',
        'error' => 'Nijedna imovina nije izmenjena. Pogledajte niz rezultata za više detalja.',
    ],

    'restore' => [
        'error' => 'Imovina nije obnovljena, pokušajte ponovo',
        'success' => 'Imovina je uspešno obnovljena.',
        'bulk_success' => 'Imovina je uspešno vraćena.',
        'nothing_updated' => 'Nijedna imovina nije izabrana, zato ništa nije vraćeno.',
    ],

    'audit' => [
        'error' => 'Neuspešan popis imovine: :error ',
        'success' => 'Provera imovine uspešno je evidentirana.',
    ],

    'deletefile' => [
        'error' => 'Fajl nije izbrisan. Molim pokušajte ponovo.',
        'success' => 'Fajl uspešno obrisan.',
    ],

    'upload' => [
        'error' => 'Fajl(ovi) nisu preneseni. Pokušajte ponovo.',
        'success' => 'Fajl(ovi) uspešno preneseni. Pokušajte ponovo.',
        'nofiles' => 'Niste odabrali nijedan fajl za prenos ili je fajl prevelik',
        'invalidfiles' => 'Jedn ili više fajlova su preveliki ili je vrsta fajla koja nije dopuštena. Dopuštene vrste su png, gif, jpg, doc, docx, pdf i txt.',
    ],

    'import' => [
        'import_button' => 'Izvrši uvoz',
        'error' => 'Neke stavke nisu pravilno uvezene.',
        'errorDetail' => 'Sledeće stavke nisu uvezene zbog grešaka.',
        'success' => 'Vaš fajl je importovan',
        'file_delete_success' => 'Vaš je fajl uspešno izbrisan',
        'file_delete_error' => 'Fajl nime moguće izbrisati',
        'file_missing' => 'Nedostaje izabrana datoteka',
        'file_already_deleted' => 'Izabrana datoteka je već obrisana',
        'file_missing_on_disk' => 'Datoteka za uvaj uvoz više nije na disku. Možda je obrisana izvan Snipe-IT. Obrišite ovaj unos i otpremite datoteku ponovo da bi ste ponovo pokušali.',
        'file_empty' => 'Ova datoteka nema redove podataka. Ništa ne može biti uvezeo iz nje.',
        'already_processing' => 'Ovaj uvoz se trenutno obrađuje od strane drugog korisnika. Molim vas sačekajte završetak pre ponovnog pokušaja.',
        'header_row_missing' => 'Ova datoteka nema prepoznat red naslova. Obrišite ovaj unos i otpremite datoteku ponovo da bi ste ponovo pokušali.',
        'header_row_has_malformed_characters' => 'Jedan ili više atributa u redu zaglavlja sadrži loše formatirane UTF-8 karaktere',
        'content_row_has_malformed_characters' => 'Jedan ili više atributa u prvom redu sadržaja sadrži loše formatirane UTF-8 karaktere',
        'transliterate_failure' => 'Transliteracija iz :encoding u UTF8 nije uspela zbog neispravnih unetih karaktera',
        'bulk_delete' => [
            'button' => 'Izbrišite izabrano (:count)',
            'confirm_title' => 'Izbrisati izabrane datoteke uvoza?',
            'confirm_body' => 'Spremate se da trajno izbrišete :count datoteka uvoza. Ovo ne može biti poništeno.',
            'confirm_button' => 'Izbrisati',
            'success' => 'Datoteka uvoza je uspešno izbrisana.|:count datoteke uvoza su uspešno izbrisane.',
            'skipped' => ':count datoteka je preskočeno jer nemate ovlašćenja da ih izbrišete.',
            'select_all' => 'Izaberi sve datoteke na ovoj strani',
            'select_row' => 'Izaberi :file za masovno brisanje',
        ],
        'row_count' => '{0} Nema redova podataka u ovoj datoteci|{1} :count red podataka za uvoz|[2,*] :count redova podataka za oviz',
        'summary' => [
            'created' => 'Kreiran',
            'updated' => 'Izmenjeno',
            'skipped' => 'Preskočeno jer su duplikati',
            'errored' => 'Pogrešeno',
            'no_changes' => 'Uvoz je završen ali ništa nije napravljeno ili izmenjeno. Svaki red je preskočen, obično zato što odgovarajući zapisi već postoje. Proverite brojač ispod i prilagodite CSV ili tip uvoza ako to nije ono što ste očekivali.',
        ],
        'type_required' => 'Molim vas izaberite tip uvoza pre nego što nastavite.',
        'processing' => 'Obrađivanje vašeg uvoza. Molim vas sačekajte dok se to ne završi pre nego što zatvorite stranu.',
        'backup_running' => 'Pravljenje rezervne kopije pre uvoza. Ovo može da potraje za veće datoteke. Molim vas sačekajte.',
        'backup_label' => 'Rezervna kopija pre uvoza',
        'backup_complete' => 'Odrađeno je pravljenje kopije',
        'import_label' => 'Import',
        'required_fields_missing' => 'Sledeća obavezna polja nisu mapirana: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(nedostaje oznaka imovine)',
            'missing_asset_tag_message' => 'Red je preskočen: nije dostavljena oznaka imovine.',
            'asset_not_found_message' => 'Imovina sa ovom oznakom ne postoji. Prvo uvezite imovinu, pa ponovite uvoz istorije.',
            'target_not_matched_message' => 'Nema :target_type poklapanja ":name". Za korisnike, promenite opciju poklapanja u prvom koraku ili prvo napravite korisnika. Za lokacije, proverite da se naziv lokacije u CSV-u poklapa sa postojećom lokacijom.',
            'invalid_target_type_message' => 'Ciljni tip ":value" nije prepoznat. Koristite "korisnik" ili "lokacija", ili ostavite kolonu praznom podrazumevano korisnika.',
        ],
        'wizard' => [
            'step_type' => 'Izaberite tip',
            'step_map' => 'Mapirajte polja',
            'step_preview' => 'Pregled',
            'back' => 'Nazad',
            'next' => 'Sledeći',
            'preview_button' => 'Pregled',
            'process' => 'Izvrši uvoz',
            'preview_intro' => 'Pregled prvih :count redova nakon primene vaših mapiranja. Koristite dugme Nazad ako treba da izmenite mapirane atribute pre uvoza.',
        ],
    ],

    'delete' => [
        'confirm' => 'Jeste li sigurni da želite izbrisati ovaj resurs?',
        'error' => 'Došlo je do problema s brisanjem resursa. Molim pokušajte ponovo.',
        'assigned_to_error' => '{1}Oznaka imovine: :asset_tag je trenutno zadužena. Razduži ovaj uređaj pre brisanja.|[2,*]Oznake imovine: :asset_tag su trenutno zadužene. Razduži ove uređaje pre brisanja.',
        'nothing_updated' => 'Nijedna imovina nije odabrana, tako da ništa nije izbrisano.',
        'success' => 'Imovina je uspešno obrisana.',
    ],

    'checkout' => [
        'error' => 'Imovina nije odjavljena, pokušajte ponovo',
        'success' => 'Imovina je uspešno odjavljena.',
        'user_does_not_exist' => 'Korisnik je nevažeći. Molim pokušajte ponovo.',
        'not_available' => 'That asset is not available for checkout!',
        'no_assets_selected' => 'Morate odabrati barem jednu imovinu s popisa',
    ],

    'multi-checkout' => [
        'error' => 'Imovina nije zadužena, molim vas pokušajte ponovo|Imovine nisu zadužene, molim vas pokušajte ponovo',
        'success' => 'Imovina je uspešno zadužena.|Imovine su uspešno zadužene.',
    ],

    'multi-checkin' => [
        'error' => 'Imovina nije razdužena, molim vas pokušajte ponovo|Imovine nisu razdužene, molim vas pokušajte ponovo',
        'success' => 'Imovina je uspešno razdužena.|Imovine su uspešno razdužene.',
        'no_assets_selected' => 'Morate odabrati barem jednu imovinu s popisa',
    ],

    'multi-audit' => [
        'success' => ':count imovine je uspešno revidirano.|:count imovina je uspešno revidirano.',
        'partial_error' => ':success imovine revidirano, :failed nije uspelo. Pogledajte greške ispod i pokušajte ponovo.|:success imovina revidirano, :failed nije uspelo. Pogledajte greške ispod i pokušajte ponovo.',
        'no_assets_selected' => 'Morate odabrati barem jednu imovinu s popisa',
    ],

    'checkin' => [
        'error' => 'Imovina nije prijavljena. Pokušajte ponovo',
        'success' => 'Imovina je uspešno prijavljena.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Taj je korisnik nevažeći. Molim pokušajte ponovo.',
        'already_checked_in' => 'Imovina je već prijavljena.',
        'force_checkin_orphaned_success' => 'Neispravna dodela je uspešno očišćena.',
        'force_checkin_not_orphaned' => 'Stavka nije u statusu neispravne dodele.',
        'force_checkin_error' => 'Nije se mogla očistiti neispravna dodela.',

    ],

    'requests' => [
        'error' => 'Zahtev nije bio uspešan, pokušajte ponovo.',
        'success' => 'Zahtev je uspešno podnet.',
        'canceled' => 'Zahtev je uspešno poništen.',
        'cancel' => 'Otkažite zahtev za ovu stavku',
        'duplicate' => 'Već imate aktivan zahtev za ovu stavku.',
        'no_active' => 'Nemate aktivan zahtev za ovu stavku koji bi ste otkazali.',
        'insufficient_stock' => 'Nema dovoljno na raspolaganju da se izvrši ovaj zahtev. Prvo dopunite.',
        'confirm_cancel_by_admin' => "Otkazati :user zahtev za :item?",
        'no_selection' => 'Nijedan red nije izabran za ispunjavanje.',
        'row_stale' => 'Zahtev #:id više nije na čekanju i preskočen je.',
        'row_qty_invalid' => 'Zahtev #:id ima neispravnu količinu i preskočen je.',
        'row_user_missing' => 'Zahtev #:id nema ispravnog ciljnog korisnika i preskočen je.',
        'row_asset_missing' => 'Zahtev #:id nema ispravnu ciljnu imovinu i preskočen je.',
        'row_asset_not_requesters' => 'Zahtev #:id je preskočen: izabrana imovina ne pripada zahtevaču.',
        'row_asset_not_available' => 'Zahtev #:id je preskočen: izabrana imovina nije dostupna jedinica ovog modela.',
        'row_asset_taken' => 'Zahtev #:id je preskočen: izabrana imovina je dodeljena nekome drugom pre podnošenja.',
        'row_company_mismatch' => 'Zahtev #:id je preskočen: :user ne može da primi stavke iz ove kompanije.',
        'row_over_allocated' => 'Zahtev #:id je preskočen: nema dovoljno na raspolaganju u trenutku podnošenja.',
        'no_target_assets_for_user' => ':user nema imovine na koju instalirati.',
        'no_available_units' => 'Nema dostupnih jedinica ovog modela za izdavanje.',
        'bulk_summary' => 'Ispunjeno :fulfilled od :total zahteva.',
        'bulk_fulfill_notification_intro' => 'Sledeće će se primeniti na svaki tiket zahteva kada se ispuni:',
    ],

];
