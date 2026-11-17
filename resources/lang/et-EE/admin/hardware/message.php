<?php

return [

    'undeployable' => 'Järgmisi vahendeid ei saa kasutusele võtta ja need eemaldati väljastusest: :asset_tags',
    'does_not_exist' => 'Vahend puudub.',
    'does_not_exist_var' => 'Vahendit inventarinumbriga :asset_tag ei leitud.',
    'no_tag' => 'Inventarinumber on määramata.',
    'does_not_exist_or_not_requestable' => 'Seda vahendit ei eksisteeri või see ei ole taotletav.',
    'assoc_users' => 'Seda vara kontrollitakse kasutajale praegu ja seda ei saa kustutada. Esmalt kontrollige varast ja proovige seejärel uuesti kustutada.',
    'warning_audit_date_mismatch' => 'Selle vahendi järgmise auditi kuupäev (:next_audit_date) on enne viimase auditi kuupäeva (:last_audit_date). Uuenda järgmise auditi kuupäeva.',
    'labels_generated' => 'Siltide loomine õnnestus.',
    'error_generating_labels' => 'Siltide loomisel tekkis viga.',
    'no_assets_selected' => 'Ühtegi vahendit pole valitud.',

    'create' => [
        'error' => 'Vahendit ei loodud, palun proovi uuesti. :(',
        'success' => 'Vahendi loomine õnnestus. :)',
        'success_no_checkout' => 'Vahendi loomine õnnestus, kuid seda ei väljastatud, sest sul puudub õigus vahendeid väljastada.',
        'checkout_skipped_no_permission' => 'Vahend loodi, kuid seda ei väljastatud taotletud saajale, sest sul puudub õigus vahendeid väljastada.',
        'success_linked' => 'Vahendi inventarinumbriga :tag loomine õnnestus. <strong><a href=":link" style="color: white;">Vaatamiseks klõpsa siin</a></strong>.',
        'multi_success_linked' => 'Vahendi inventarinumbriga :links loomine õnnestus.|:count vahendi loomine õnnestus. :links.',
        'partial_failure' => 'Vahendit ei saanud luua. Põhjus: :failures|:count vahendit ei saanud luua. Põhjused: :failures',
        'target_not_found' => [
            'user' => 'Määratud kasutajat ei leitud.',
            'asset' => 'Määratud vahendit ei leitud.',
            'location' => 'Määratud asukohta ei leitud.',
        ],
    ],

    'update' => [
        'error' => 'Vara ei värskendatud, proovige uuesti',
        'success' => 'Vara värskendati edukalt',
        'encrypted_warning' => 'Vahendi uuendamine õnnestus, kuid krüpteeritud kohandatud välju ei uuendatud puuduvate õiguste tõttu',
        'nothing_updated' => 'Pole ühtegi välju valitud, nii et midagi ei uuendatud.',
        'no_assets_selected' => 'Ühtegi vahendit ei valitud, muudatusi ei tehtud.',
        'assets_do_not_exist_or_are_invalid' => 'Valitud vahendeid ei saa uuendada.',
    ],

    'bulk_update' => [
        'success' => 'Vahendi uuendamine õnnestus.|:count vahendi uuendamine õnnestus.',
        'partial' => ':success vahendi uuendamine õnnestus, :failed ebaõnnestus. Lisateavet leiad tulemuste massiivist.',
        'error' => 'Ühtegi vahendit ei uuendatud. Lisateavet leiad tulemuste massiivist.',
    ],

    'restore' => [
        'error' => 'Vara ei taastatud, palun proovi uuesti',
        'success' => 'Varad on edukalt taastatud.',
        'bulk_success' => 'Varad on edukalt taastatud.',
        'nothing_updated' => 'Ühtegi vahendit polnud valitud, seega midagi ei taastatud.',
    ],

    'audit' => [
        'error' => 'Vahendi audit ebaõnnestus: :error ',
        'success' => 'Varakontrolli sisselogimisel.',
    ],

    'deletefile' => [
        'error' => 'Faili pole kustutatud. Palun proovi uuesti.',
        'success' => 'Fail edukalt kustutatud.',
    ],

    'upload' => [
        'error' => 'Faili (d) pole üles laaditud. Palun proovi uuesti.',
        'success' => 'Fail (id) edukalt üles laaditud.',
        'nofiles' => 'Te ei valinud üleslaadimiseks ühtegi faili või fail, mille üritate üles laadida, on liiga suur',
        'invalidfiles' => 'Üks või mitu teie faili on liiga suured või failitüüp pole lubatud. Lubatud failitüübid on png, gif, jpg, doc, docx, pdf ja txt.',
    ],

    'import' => [
        'import_button' => 'Töötle importi',
        'error' => 'Mõned üksused ei impordinud õigesti.',
        'errorDetail' => 'Järgmisi punkte ei imporditud vigade tõttu.',
        'success' => 'Teie fail on imporditud',
        'file_delete_success' => 'Teie fail on edukalt kustutatud',
        'file_delete_error' => 'Faili ei saanud kustutada',
        'file_missing' => 'Valitud fail puudub',
        'file_already_deleted' => 'Valitud fail on juba kustutatud',
        'file_missing_on_disk' => 'Selle impordi faili pole enam kettal. See võidi kustutada väljaspool Snipe-IT-d. Kustuta see kirje ja laadi fail uuesti üles.',
        'file_empty' => 'Selles failis pole andmeridu. Sellest ei saa midagi importida.',
        'already_processing' => 'Seda importi töötleb praegu teine kasutaja. Oota enne uuesti proovimist, kuni see lõpeb.',
        'header_row_missing' => 'Selle faili päiserida ei tuvastatud. Kustuta see kirje ja laadi fail uuesti üles.',
        'header_row_has_malformed_characters' => 'Päiserea üks või mitu atribuuti sisaldavad vigaseid UTF-8 märke',
        'content_row_has_malformed_characters' => 'Esimese andmerea üks või mitu atribuuti sisaldavad vigaseid UTF-8 märke',
        'transliterate_failure' => 'Translitereerimine kodeeringust :encoding UTF-8-sse ebaõnnestus sisendi vigaste märkide tõttu',
        'bulk_delete' => [
            'button' => 'Kustuta valitud (:count)',
            'confirm_title' => 'Kas kustutada valitud impordifailid?',
            'confirm_body' => 'Oled jäädavalt kustutamas :count impordifaili. Seda toimingut ei saa tagasi võtta.',
            'confirm_button' => 'Kustuta',
            'success' => 'Impordifaili kustutamine õnnestus.|:count impordifaili kustutamine õnnestus.',
            'skipped' => ':count faili jäeti vahele, sest sul puudub õigus neid kustutada.',
            'select_all' => 'Vali kõik selle lehe failid',
            'select_row' => 'Vali :file hulgikustutamiseks',
        ],
        'row_count' => '{0} Selles failis pole andmeridu|{1} :count imporditav andmerida|[2,*] :count imporditavat andmerida',
        'summary' => [
            'created' => 'Loodud',
            'updated' => 'Uuendatud',
            'skipped' => 'Duplikaatidena vahele jäetud',
            'errored' => 'Vigased',
            'no_changes' => 'Import lõppes, kuid midagi ei loodud ega uuendatud. Kõik read jäeti vahele, tavaliselt seetõttu, et vastavad kirjed olid juba olemas. Kontrolli allolevaid arve ning kohanda CSV-faili või impordi tüüpi, kui tulemus ei vasta ootustele.',
        ],
        'type_required' => 'Vali enne jätkamist impordi tüüp.',
        'processing' => 'Sinu importi töödeldakse. Oota enne lehe sulgemist, kuni see lõpeb.',
        'backup_running' => 'Enne importimist luuakse varukoopia. Suuremate failide puhul võib see aega võtta. Palun oota.',
        'backup_label' => 'Impordieelne varukoopia',
        'backup_complete' => 'Varundamine lõpetatud',
        'import_label' => 'Impordi',
        'required_fields_missing' => 'Järgmised kohustuslikud väljad on vastendamata: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(inventarinumber puudub)',
            'missing_asset_tag_message' => 'Rida jäeti vahele: inventarinumber puudub.',
            'asset_not_found_message' => 'Selle inventarinumbriga vahendit pole olemas. Impordi esmalt vahendid ja käivita seejärel ajaloo import uuesti.',
            'target_not_matched_message' => 'Nimele „:name” vastavat sihtmärki tüübiga :target_type ei leitud. Kasutajate puhul muuda esimeses etapis vastendamisvalikuid või loo esmalt kasutaja. Asukohtade puhul veendu, et CSV-faili asukohanimi vastaks täpselt olemasolevale asukohale.',
            'invalid_target_type_message' => 'Sihtmärgi tüüpi „:value” ei tuvastatud. Kasuta väärtust „user” või „location” või jäta veerg tühjaks, et kasutada vaikimisi kasutajat.',
        ],
        'wizard' => [
            'step_type' => 'Vali tüüp',
            'step_map' => 'Vastenda väljad',
            'step_preview' => 'Eelvaade',
            'back' => 'Tagasi',
            'next' => 'Järgmine',
            'preview_button' => 'Eelvaade',
            'process' => 'Töötle importi',
            'preview_intro' => 'Kuvatakse esimese :count rea eelvaade pärast vastenduse rakendamist. Kui pead enne importimist vastendatud atribuute muutma, kasuta nuppu Tagasi.',
        ],
    ],

    'delete' => [
        'confirm' => 'Kas olete kindel, et soovite selle vara kustutada?',
        'error' => 'Viga kustutas. Palun proovi uuesti.',
        'assigned_to_error' => '{1}Vahend inventarinumbriga :asset_tag on praegu väljastatud. Tagasta see seade enne kustutamist.|[2,*]Vahendid inventarinumbritega :asset_tag on praegu väljastatud. Tagasta need seadmed enne kustutamist.',
        'nothing_updated' => 'Varasid ei valitud, nii et midagi ei kustutatud.',
        'success' => 'Varasus kustutati edukalt.',
    ],

    'checkout' => [
        'error' => 'Varasid ei kontrollitud, proovige uuesti',
        'success' => 'Varad võeti edukalt välja.',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
        'not_available' => 'See vara pole kontrollimiseks saadaval!',
        'no_assets_selected' => 'Sa pead valima vähemalt ühe kirje nimekirjast',
    ],

    'multi-checkout' => [
        'error' => 'Vahendi väljastamine ebaõnnestus, proovi uuesti|Vahendite väljastamine ebaõnnestus, proovi uuesti',
        'success' => 'Vahendi väljastamine õnnestus.|Vahendite väljastamine õnnestus.',
    ],

    'multi-checkin' => [
        'error' => 'Vahendi tagastamine ebaõnnestus, proovi uuesti|Vahendite tagastamine ebaõnnestus, proovi uuesti',
        'success' => 'Vahendi tagastamine õnnestus.|Vahendite tagastamine õnnestus.',
        'no_assets_selected' => 'Sa pead valima vähemalt ühe kirje nimekirjast',
    ],

    'multi-audit' => [
        'success' => ':count vahendi audit õnnestus.|:count vahendi audit õnnestus.',
        'partial_error' => ':success vahendi audit õnnestus, :failed ebaõnnestus. Kontrolli allolevaid vigu ja proovi uuesti.|:success vahendi audit õnnestus, :failed ebaõnnestus. Kontrolli allolevaid vigu ja proovi uuesti.',
        'no_assets_selected' => 'Sa pead valima vähemalt ühe kirje nimekirjast',
    ],

    'checkin' => [
        'error' => 'Vara ei olnud märgitud, palun proovi uuesti',
        'success' => 'Vara on edukalt kontrollitud',
        'updated_without_checkin' => 'Vahend oli juba tagastatud, kuid vormil määratud väljad (olek, asukoht, nimi, märkused) rakendati sellegipoolest.',
        'user_does_not_exist' => 'See kasutaja on kehtetu. Palun proovi uuesti.',
        'already_checked_in' => 'See vara on juba sisse registreeritud.',
        'force_checkin_orphaned_success' => 'Vigase määrangu eemaldamine õnnestus.',
        'force_checkin_not_orphaned' => 'Kirjel pole vigast määrangut.',
        'force_checkin_error' => 'Vigase määrangu eemaldamine ebaõnnestus.',

    ],

    'requests' => [
        'error' => 'Taotlus ebaõnnestus, proovi uuesti.',
        'success' => 'Taotluse esitamine õnnestus.',
        'canceled' => 'Taotluse tühistamine õnnestus.',
        'cancel' => 'Tühista selle kirje taotlus',
        'duplicate' => 'Sul on selle kirje jaoks juba aktiivne taotlus.',
        'no_active' => 'Sul pole selle kirje jaoks aktiivset taotlust, mida tühistada.',
        'insufficient_stock' => 'Selle taotluse täitmiseks pole piisavalt laovaru. Täienda esmalt laoseisu.',
        'confirm_cancel_by_admin' => "Kas tühistada kasutaja :user taotlus kirjele :item?",
        'no_selection' => 'Täitmiseks pole ühtegi rida valitud.',
        'row_stale' => 'Taotlus nr :id pole enam ootel ja jäeti vahele.',
        'row_qty_invalid' => 'Taotluse nr :id kogus on vigane ja see jäeti vahele.',
        'row_user_missing' => 'Taotlusel nr :id pole kehtivat sihtkasutajat ja see jäeti vahele.',
        'row_asset_missing' => 'Taotlusel nr :id pole kehtivat sihtvahendit ja see jäeti vahele.',
        'row_asset_not_requesters' => 'Taotlus nr :id jäeti vahele: valitud vahend ei kuulu taotlejale.',
        'row_asset_not_available' => 'Taotlus nr :id jäeti vahele: valitud vahend pole selle mudeli vaba eksemplar.',
        'row_asset_taken' => 'Taotlus nr :id jäeti vahele: valitud vahend määrati enne esitamist kellelegi teisele.',
        'row_company_mismatch' => 'Taotlus nr :id jäeti vahele: :user ei saa selle ettevõtte kirjeid vastu võtta.',
        'row_over_allocated' => 'Taotlus nr :id jäeti vahele: esitamise hetkel polnud piisavalt laovaru.',
        'no_target_assets_for_user' => 'Kasutajal :user pole vahendeid, millesse paigaldada.',
        'no_available_units' => 'Selle mudeli vabu eksemplare pole väljastamiseks saadaval.',
        'bulk_summary' => 'Täideti :fulfilled taotlust :total-st.',
        'bulk_fulfill_notification_intro' => 'Igale märgitud taotlusele rakendub täitmisel järgmine:',
    ],

];
