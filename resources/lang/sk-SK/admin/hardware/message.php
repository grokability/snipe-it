<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Majetok neexistuje.',
    'does_not_exist_var' => 'Majetok s označením :asset_tag nebol nájdený.',
    'no_tag' 	         => 'Nebolo zadané žiadne označenie majetku.',
    'does_not_exist_or_not_requestable' => 'Tento majetok neexistuje alebo sa nedá vyžiadať.',
    'assoc_users'	 	 => 'Tento majetok je práve priradený používateľovi, preto nemôže byť odstránený. Prosim najprv odoberte majetok používateľovi, následne skúste znovu. ',
    'warning_audit_date_mismatch' 	=> 'Nastavený dátum nasledujúceho auditu (:next_audit_date) je skorší ako dátum posledného auditu (:last_audit_date). Prosím upravte dátum nasledujúceho auditu.',
    'labels_generated'   => 'Štítky boli úspešne vygenerované.',
    'error_generating_labels' => 'Pri generovaní štítkov nastala chyba.',
    'no_assets_selected' => 'Neboli zvolené žiadne položky majetku.',

    'create' => [
        'error'   		=> 'Majetok nebol vytvorený, prosím skúste znovu. :(',
        'success' 		=> 'Majetok bol úspešne vytvorený. :)',
        'success_linked' => 'Majetok s označením :tag bol úspešne pridaný. <strong><a href=":link" style="color: white;">Kliknite sem pre zobrazenie</a></strong>.',
        'multi_success_linked' => 'Majetok s označením :links bol úspešne pridaný.|:count majetkov bolo úspešne pridaných :links.',
        'partial_failure' => 'Majetok sa nepodarilo pridať. Dôvod: :failuers|:count majetkov nebolo možné pridať. Dôvody: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Majetok sa nepodarilo upraviť, skúste prosím znovu',
        'success' 			=> 'Majetok bol úspešne upravený.',
        'encrypted_warning' => 'Majetok bol úspešne upravený, avšak šifrované vlastné polia neboli upravené z dôvodu oprávnení',
        'nothing_updated'	=>  'Neboli vybrané žiadne položky, preto nebolo nič upravené.',
        'no_assets_selected'  =>  'Neboli vybrané žiadne majetky, preto nebolo nič upravené.',
        'assets_do_not_exist_or_are_invalid' => 'Zvolené položky majetku nemôžu byť upravené.',
    ],

    'restore' => [
        'error'   		=> 'Majetok nebol obnovený, prosím skúste znovu',
        'success' 		=> 'Majetok bol úspešne obnovený.',
        'bulk_success' 		=> 'Majetok bol úspešne obnovený.',
        'nothing_updated'   => 'Neboli zvolené žiadne položky majetku, preto nebolo nič obnovené.', 
    ],

    'audit' => [
        'error'   		=> 'Audit majetku nebol úspešný :error ',
        'success' 		=> 'Audit majetko bol úspešne zaznamenaný.',
    ],


    'deletefile' => [
        'error'   => 'Súbor nebol odstránený. Prosím skúste znovu.',
        'success' => 'Súbor bol úspešne odstránený.',
    ],

    'upload' => [
        'error'   => 'Súbor(y) sa nepodarilo nahrať. Skúste prosím znovu.',
        'success' => 'Súbor(y) boli úspešne uložené.',
        'nofiles' => 'Nevybrali ste žiadne súbory na nahranie alebo je súbor, ktorý sa pokúšate nahrať, príliš veľký',
        'invalidfiles' => 'Jeden alebo viac súborov je príliš veľký alebo ide o typ súboru, ktorý nie je povolený. Povolené typy súborov sú png, gif, jpg, doc, docx, pdf a txt.',
    ],

    'import' => [
        'import_button'         => 'Spracovať import',
        'error'                 => 'Niektoré položky neboli správne naimportované.',
        'errorDetail'           => 'Nasledujúce položky neboli kvôli chybám importované.',
        'success'               => 'Súbor bol naimportovaný',
        'file_delete_success'   => 'Súbor bol úspešné odstránený',
        'file_delete_error'      => 'Súbor sa nepodarilo odstrániť',
        'file_missing' => 'Vybraný súbor nebol nájdený',
        'file_already_deleted' => 'Vybraný súbor už bol odstránený',
        'header_row_has_malformed_characters' => 'Jeden alebo viacero stĺpcov obsahujú poškodené UTF-8 znaky',
        'content_row_has_malformed_characters' => 'Jeden alebo viacero atribútov v prvom riadku obsahu obsahuje poškodené UTF-8 znaky',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'Ste si istý, že chcete odstrániť tento majetok?',
        'error'   		=> 'Pri odstraňovaní majetku sa vyskytla chyba. Skúste prosím znovu.',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'Neboli zvolený žiadne položky majetku, preto nebolo nič odstránené.',
        'success' 		=> 'Majetok bol úspešne odstránený.',
    ],

    'checkout' => [
        'error'   		=> 'Majetok sa nepodarilo odovzdať, skúste prosím znovu',
        'success' 		=> 'Majetok bol úspešne odovzdaný.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'not_available' => 'Tento majetok nie je k dospozícii pre odovzdanie!',
        'no_assets_selected' => 'Musíte vybrať najmenej jednu položku majetku zo zoznamu',
    ],

    'multi-checkout' => [
        'error'   => 'Majetok nebol odovzdaný, prosím skúste znovu|Majetky neboli odovzdané, prosím skúste znovu',
        'success' => 'Majetok bol úspešne odovzdaný.|Majetky boli úspešne odovzdané.',
    ],

    'checkin' => [
        'error'   		=> 'Majetok sa nepodarilo prevziať, skúste prosím znovu',
        'success' 		=> 'Majetok bol úspešne prevzatý.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'already_checked_in'  => 'Tento majetok je už prevzatý.',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'Zrušiť túto žiadosť o položku',
    ],

];
