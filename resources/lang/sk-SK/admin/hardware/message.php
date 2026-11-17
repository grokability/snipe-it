<?php

return [

    'undeployable' => 'Nasledujúce majetky nie je možné odovzdať, preto boli odstránené z odovzdávania: :asset_tags',
    'does_not_exist' => 'Majetok neexistuje.',
    'does_not_exist_var' => 'Majetok s označením :asset_tag nebol nájdený.',
    'no_tag' => 'Nebolo zadané žiadne označenie majetku.',
    'does_not_exist_or_not_requestable' => 'Tento majetok neexistuje alebo sa nedá vyžiadať.',
    'assoc_users' => 'Tento majetok je práve priradený používateľovi, preto nemôže byť odstránený. Prosim najprv odoberte majetok používateľovi, následne skúste znovu. ',
    'warning_audit_date_mismatch' => 'Nastavený dátum nasledujúceho auditu (:next_audit_date) je skorší ako dátum posledného auditu (:last_audit_date). Prosím upravte dátum nasledujúceho auditu.',
    'labels_generated' => 'Štítky boli úspešne vygenerované.',
    'error_generating_labels' => 'Pri generovaní štítkov nastala chyba.',
    'no_assets_selected' => 'Neboli zvolené žiadne položky majetku.',

    'create' => [
        'error' => 'Majetok nebol vytvorený, prosím skúste znovu. :(',
        'success' => 'Majetok bol úspešne vytvorený. :)',
        'success_no_checkout' => 'Majetok bol úspešne vytvorený, ale nebol odovzdaný, pretože nemáte oprávnenie odovzdávať majetok.',
        'checkout_skipped_no_permission' => 'Majetok bol vytvorený, ale nebol odovzdaný požadovanému cieľu, pretože nemáte oprávnenie odovzdávať majetok.',
        'success_linked' => 'Majetok s označením :tag bol úspešne pridaný. <strong><a href=":link" style="color: white;">Kliknite sem pre zobrazenie</a></strong>.',
        'multi_success_linked' => 'Majetok s označením :links bol úspešne pridaný.|:count majetkov bolo úspešne pridaných :links.',
        'partial_failure' => 'Majetok sa nepodarilo pridať. Dôvod: :failuers|:count majetkov nebolo možné pridať. Dôvody: :failures',
        'target_not_found' => [
            'user' => 'Priradeného používateľa sa nepodarilo nájsť.',
            'asset' => 'Priradený majetok sa nepodarilo nájsť.',
            'location' => 'Priradenú lokalitu sa nepodarilo nájsť.',
        ],
    ],

    'update' => [
        'error' => 'Majetok sa nepodarilo upraviť, skúste prosím znovu',
        'success' => 'Majetok bol úspešne upravený.',
        'encrypted_warning' => 'Majetok bol úspešne upravený, avšak šifrované vlastné polia neboli upravené z dôvodu oprávnení',
        'nothing_updated' => 'Neboli vybrané žiadne položky, preto nebolo nič upravené.',
        'no_assets_selected' => 'Neboli vybrané žiadne majetky, preto nebolo nič upravené.',
        'assets_do_not_exist_or_are_invalid' => 'Zvolené položky majetku nemôžu byť upravené.',
    ],

    'bulk_update' => [
        'success' => 'Majetok bol úspešne aktualizovaný.|:count položiek majetku bolo úspešne aktualizovaných.',
        'partial' => ':success položiek majetku bolo úspešne aktualizovaných, :failed zlyhalo. Podrobnosti nájdete v poli výsledkov (results).',
        'error' => 'Neboli aktualizované žiadne položky majetku. Podrobnosti nájdete v poli výsledkov.',
    ],

    'restore' => [
        'error' => 'Majetok nebol obnovený, prosím skúste znovu',
        'success' => 'Majetok bol úspešne obnovený.',
        'bulk_success' => 'Majetok bol úspešne obnovený.',
        'nothing_updated' => 'Neboli zvolené žiadne položky majetku, preto nebolo nič obnovené.',
    ],

    'audit' => [
        'error' => 'Audit majetku nebol úspešný :error ',
        'success' => 'Audit majetko bol úspešne zaznamenaný.',
    ],

    'deletefile' => [
        'error' => 'Súbor nebol odstránený. Prosím skúste znovu.',
        'success' => 'Súbor bol úspešne odstránený.',
    ],

    'upload' => [
        'error' => 'Súbor(y) sa nepodarilo nahrať. Skúste prosím znovu.',
        'success' => 'Súbor(y) boli úspešne uložené.',
        'nofiles' => 'Nevybrali ste žiadne súbory na nahranie alebo je súbor, ktorý sa pokúšate nahrať, príliš veľký',
        'invalidfiles' => 'Jeden alebo viac súborov je príliš veľký alebo ide o typ súboru, ktorý nie je povolený. Povolené typy súborov sú png, gif, jpg, doc, docx, pdf a txt.',
    ],

    'import' => [
        'import_button' => 'Spracovať import',
        'error' => 'Niektoré položky neboli správne naimportované.',
        'errorDetail' => 'Nasledujúce položky neboli kvôli chybám importované.',
        'success' => 'Súbor bol naimportovaný',
        'file_delete_success' => 'Súbor bol úspešné odstránený',
        'file_delete_error' => 'Súbor sa nepodarilo odstrániť',
        'file_missing' => 'Vybraný súbor nebol nájdený',
        'file_already_deleted' => 'Vybraný súbor už bol odstránený',
        'file_missing_on_disk' => 'Súbor pre tento import sa už na disku nenachádza. Mohol byť odstránený mimo Snipe-IT. Odstráňte tento záznam a nahrajte súbor znova.',
        'file_empty' => 'Tento súbor neobsahuje žiadne dátové riadky. Nie je z neho možné nič importovať.',
        'already_processing' => 'Tento import práve spracúva iný používateľ. Pred ďalším pokusom počkajte, kým sa dokončí.',
        'header_row_missing' => 'Tento súbor nemá rozpoznateľný riadok s hlavičkou. Odstráňte tento záznam a nahrajte súbor znova.',
        'header_row_has_malformed_characters' => 'Jeden alebo viacero stĺpcov obsahujú poškodené UTF-8 znaky',
        'content_row_has_malformed_characters' => 'Jeden alebo viacero atribútov v prvom riadku obsahu obsahuje poškodené UTF-8 znaky',
        'transliterate_failure' => 'Prepis z kódovania :encoding do UTF-8 zlyhal kvôli neplatným znakom vo vstupe',
        'bulk_delete' => [
            'button' => 'Odstrániť vybrané (:count)',
            'confirm_title' => 'Odstrániť vybrané importované súbory?',
            'confirm_body' => 'Chystáte sa natrvalo odstrániť importované súbory (:count). Túto akciu nie je možné vrátiť späť.',
            'confirm_button' => 'Odstrániť',
            'success' => 'Importovaný súbor bol úspešne odstránený.|:count importovaných súborov bolo úspešne odstránených.',
            'skipped' => ':count súborov bolo preskočených (nemáte oprávnenie ich odstrániť).',
            'select_all' => 'Vybrať všetky súbory na tejto stránke',
            'select_row' => 'Vybrať :file na hromadné odstránenie',
        ],
        'row_count' => '{0} V tomto súbore nie sú žiadne dátové riadky|{1} :count dátový riadok na import|[2,*] :count dátových riadkov na import',
        'summary' => [
            'created' => 'Vytvorený',
            'updated' => 'Aktualizované',
            'skipped' => 'Preskočené ako duplikáty',
            'errored' => 'S chybou',
            'no_changes' => 'Import sa dokončil, ale nič nebolo vytvorené ani aktualizované. Všetky riadky boli preskočené, zvyčajne preto, že príslušné záznamy už existovali. Skontrolujte počty nižšie a ak to nie je to, čo ste očakávali, upravte CSV alebo typ importu.',
        ],
        'type_required' => 'Pred pokračovaním vyberte typ importu.',
        'processing' => 'Import sa spracúva. Pred zatvorením stránky počkajte, kým sa dokončí.',
        'backup_running' => 'Pred importom prebieha záloha. Pri väčších súboroch to môže chvíľu trvať. Počkajte, prosím.',
        'backup_label' => 'Záloha pred importom',
        'backup_complete' => 'Záloha dokončená',
        'import_label' => 'Import',
        'required_fields_missing' => 'Nasledujúce povinné polia nie sú namapované: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(chýba označenie majetku)',
            'missing_asset_tag_message' => 'Riadok preskočený: nebolo zadané označenie majetku.',
            'asset_not_found_message' => 'Majetok s týmto označením neexistuje. Najprv importujte majetok a potom znova spustite import histórie.',
            'target_not_matched_message' => 'Pre :target_type sa nenašla zhoda s ":name". Pri používateľoch prepnite možnosti párovania v kroku 1 alebo najprv vytvorte používateľa. Pri lokalitách sa uistite, že názov lokality v CSV sa presne zhoduje s existujúcou lokalitou.',
            'invalid_target_type_message' => 'Typ cieľa ":value" nebol rozpoznaný. Použite "user" alebo "location" alebo nechajte stĺpec prázdny, aby sa predvolene použil používateľ.',
        ],
        'wizard' => [
            'step_type' => 'Vyberte typ',
            'step_map' => 'Namapujte polia',
            'step_preview' => 'Náhľad',
            'back' => 'Späť',
            'next' => 'Ďalší',
            'preview_button' => 'Náhľad',
            'process' => 'Spracujte import',
            'preview_intro' => 'Ukážka prvých riadkov (:count) po použití vášho mapovania. Ak potrebujete pred importom upraviť namapované atribúty, použite tlačidlo Späť.',
        ],
    ],

    'delete' => [
        'confirm' => 'Ste si istý, že chcete odstrániť tento majetok?',
        'error' => 'Pri odstraňovaní majetku sa vyskytla chyba. Skúste prosím znovu.',
        'assigned_to_error' => '{1}Majetok: :asset_tag je odovzdaný. Prevezmite majetok pred zmazaním.|[2,*]Majetky : :asset_tag sú odovzdané. Prevezmite tieto majetky pred odmazaním.',
        'nothing_updated' => 'Neboli zvolený žiadne položky majetku, preto nebolo nič odstránené.',
        'success' => 'Majetok bol úspešne odstránený.',
    ],

    'checkout' => [
        'error' => 'Majetok sa nepodarilo odovzdať, skúste prosím znovu',
        'success' => 'Majetok bol úspešne odovzdaný.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'not_available' => 'Tento majetok nie je k dospozícii pre odovzdanie!',
        'no_assets_selected' => 'Musíte vybrať najmenej jednu položku majetku zo zoznamu',
    ],

    'multi-checkout' => [
        'error' => 'Majetok nebol odovzdaný, prosím skúste znovu|Majetky neboli odovzdané, prosím skúste znovu',
        'success' => 'Majetok bol úspešne odovzdaný.|Majetky boli úspešne odovzdané.',
    ],

    'multi-checkin' => [
        'error' => 'Majetok nebol prevzatý, skúste to znova|Položky majetku neboli prevzaté, skúste to znova',
        'success' => 'Majetok bol úspešne prevzatý.|Položky majetku boli úspešne prevzaté.',
        'no_assets_selected' => 'Musíte vybrať najmenej jednu položku majetku zo zoznamu',
    ],

    'multi-audit' => [
        'success' => ':count položka majetku bola úspešne auditovaná.|:count položiek majetku bolo úspešne auditovaných.',
        'partial_error' => ':success položka majetku bola auditovaná, :failed zlyhalo. Skontrolujte chyby nižšie a skúste to znova.|:success položiek majetku bolo auditovaných, :failed zlyhalo. Skontrolujte chyby nižšie a skúste to znova.',
        'no_assets_selected' => 'Musíte vybrať najmenej jednu položku majetku zo zoznamu',
    ],

    'checkin' => [
        'error' => 'Majetok sa nepodarilo prevziať, skúste prosím znovu',
        'success' => 'Majetok bol úspešne prevzatý.',
        'updated_without_checkin' => 'Majetok už bol prevzatý, ale polia formulára, ktoré ste nastavili (stav, lokalita, názov, poznámky), boli aj tak použité.',
        'user_does_not_exist' => 'Tento užívateľ nie je platný. Prosím skúste znovu.',
        'already_checked_in' => 'Tento majetok je už prevzatý.',
        'force_checkin_orphaned_success' => 'Neplatné priradenie bolo úspešne vymazané.',
        'force_checkin_not_orphaned' => 'Položka nie je v stave neplatného priradenia.',
        'force_checkin_error' => 'Neplatné priradenie sa nepodarilo vymazať.',

    ],

    'requests' => [
        'error' => 'Požiadavka nebola úspešná, skúste to znova.',
        'success' => 'Žiadosť bola úspešne odoslaná.',
        'canceled' => 'Žiadosť bola úspešne zrušená.',
        'cancel' => 'Zrušiť túto žiadosť o položku',
        'duplicate' => 'Pre túto položku už máte aktívnu žiadosť.',
        'no_active' => 'Pre túto položku nemáte žiadnu aktívnu žiadosť na zrušenie.',
        'insufficient_stock' => 'Na sklade nie je dostatok kusov na vybavenie tejto žiadosti. Najprv doplňte zásoby.',
        'confirm_cancel_by_admin' => "Zrušiť žiadosť používateľa :user o :item?",
        'no_selection' => 'Na vybavenie neboli vybrané žiadne riadky.',
        'row_stale' => 'Žiadosť #:id už nečaká na vybavenie a bola preskočená.',
        'row_qty_invalid' => 'Žiadosť #:id má neplatné množstvo a bola preskočená.',
        'row_user_missing' => 'Žiadosť #:id nemá platného cieľového používateľa a bola preskočená.',
        'row_asset_missing' => 'Žiadosť #:id nemá platný cieľový majetok a bola preskočená.',
        'row_asset_not_requesters' => 'Žiadosť #:id bola preskočená: vybraný majetok nepatrí žiadateľovi.',
        'row_asset_not_available' => 'Žiadosť #:id bola preskočená: vybraný majetok nie je dostupným kusom tohto modelu.',
        'row_asset_taken' => 'Žiadosť #:id bola preskočená: vybraný majetok bol pred odoslaním priradený niekomu inému.',
        'row_company_mismatch' => 'Žiadosť #:id bola preskočená: :user nemôže prijímať položky z tejto spoločnosti.',
        'row_over_allocated' => 'Žiadosť #:id bola preskočená: v čase odoslania nebol na sklade dostatok kusov.',
        'no_target_assets_for_user' => ':user nemá žiadny majetok, do ktorého by bolo možné inštalovať.',
        'no_available_units' => 'Nie sú k dispozícii žiadne voľné kusy tohto modelu na vydanie.',
        'bulk_summary' => 'Vybavených :fulfilled z :total žiadostí.',
        'bulk_fulfill_notification_intro' => 'Pri vybavení sa na každú označenú žiadosť použije nasledujúce:',
    ],

];
