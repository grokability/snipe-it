<?php

return [

    'undeployable' => 'Šis turtas negali būti išduotas, todėl buvo pašalintas iš išduodamų sąrašo: :asset_tags',
    'does_not_exist' => 'Tokio turto nėra.',
    'does_not_exist_var' => 'Turtas su numeriu :asset_tag nerastas.',
    'no_tag' => 'Nenurodytas inventorinis numeris.',
    'does_not_exist_or_not_requestable' => 'Tokio turto nėra arba jo negalima užsakyti.',
    'assoc_users' => 'Šis turtas šiuo metu yra išduotas naudotojui ir negali būti panaikintas. Pirmiausia paimkite turtą ir tuomet vėl bandykite jį panaikinti. ',
    'warning_audit_date_mismatch' => 'Šio turto kito audito data (:next_audit_date) yra ankstesnė už paskutinio audito datą (:last_audit_date). Atnaujinkite kito audito datą.',
    'labels_generated' => 'Etiketės sėkmingai sugeneruotos.',
    'error_generating_labels' => 'Generuojant etiketes įvyko klaida.',
    'no_assets_selected' => 'Nepasirinktas joks turtas.',

    'create' => [
        'error' => 'Turto sukurti nepavyko, bandykite dar kartą.',
        'success' => 'Turtas sėkmingai sukurtas. :)',
        'success_no_checkout' => 'Turtas sėkmingai sukurtas, tačiau nebuvo išduotas, nes neturite teisės išduoti turto.',
        'checkout_skipped_no_permission' => 'Turtas buvo sukurtas, tačiau nebuvo išduotas į nurodytą paskirties vietą, nes neturite teisės išduoti turto.',
        'success_linked' => 'Turtas su žyma :tag sėkmingai sukurtas. <strong><a href=":link" style="color: white;">Spustelėkite čia, kad peržiūrėtumėte</a></strong>.',
        'multi_success_linked' => 'Turtas su inventoriniu numeriu :links sėkmingai sukurtas.|Turto vienetai (:count) sėkmingai sukurti. :links.',
        'partial_failure' => 'Nepavyko sukurti turto. Priežastis: :failures|Turto vienetų (:count) nepavyko sukurti. Priežastys: :failures',
        'target_not_found' => [
            'user' => 'Priskirto naudotojo rasti nepavyko.',
            'asset' => 'Priskirto turto rasti nepavyko.',
            'location' => 'Priskirtos vietos rasti nepavyko.',
        ],
    ],

    'update' => [
        'error' => 'Turto atnaujinti nepavyko, bandykite dar kartą',
        'success' => 'Turtas sėkmingai atnaujintas.',
        'encrypted_warning' => 'Turtas buvo sėkmingai atnaujintas, tačiau dėl nepakankamų teisių, užšifruoti pasirinktiniai laukai nebuvo atnaujinti',
        'nothing_updated' => 'Nebuvo pasirinktas nei vienas laukas, todėl niekas nebuvo atnaujinta.',
        'no_assets_selected' => 'Nebuvo pasirinkta jokio turto, todėl nieko nebuvo atnaujinta.',
        'assets_do_not_exist_or_are_invalid' => 'Pasirinktas turtas negali būti atnaujintas.',
    ],

    'bulk_update' => [
        'success' => 'Turtas sėkmingai atnaujintas.|Turto vienetai (:count) sėkmingai atnaujinti.',
        'partial' => ':success turtas sėkmingai atnaujintas, :failed nepavyko. Daugiau informacijos rasite rezultatų masyve.',
        'error' => 'Nebuvo atnaujintas joks turtas. Išsamesnės informacijos ieškokite rezultatų masyve.',
    ],

    'restore' => [
        'error' => 'Turto atkurti nepavyko, bandykite dar kartą',
        'success' => 'Turtas sėkmingai atkurtas.',
        'bulk_success' => 'Turtas sėkmingai atkurtas.',
        'nothing_updated' => 'Nebuvo pasirinkta jokio turto, todėl nieko nebuvo atkurta.',
    ],

    'audit' => [
        'error' => 'Turto auditas nesėkmingas: :error ',
        'success' => 'Turto auditas sėkmingai užregistruotas.',
    ],

    'deletefile' => [
        'error' => 'Failas neištrintas. Bandykite dar kartą.',
        'success' => 'Failas sėkmingai ištrintas.',
    ],

    'upload' => [
        'error' => 'Failo (-ų) įkelti nepavyko. Bandykite dar kartą.',
        'success' => 'Failas(-ai) sėkmingai įkelti.',
        'nofiles' => 'Nepasirinkote jokio failo įkėlimui arba failas, kurį bandote įkelti, yra per didelis',
        'invalidfiles' => 'Vienas ar keli failai yra per dideli arba neleistinas šis failų formatas. Leidžiami failų tipai yra: png, gif, jpg, doc, docx, pdf ir txt.',
    ],

    'import' => [
        'import_button' => 'Vykdyti importavimą',
        'error' => 'Kai kurie elementai nebuvo tinkamai importuoti.',
        'errorDetail' => 'Šie elementai nebuvo importuoti dėl klaidų.',
        'success' => 'Jūsų failas buvo importuotas',
        'file_delete_success' => 'Jūsų failas buvo sėkmingai ištrintas',
        'file_delete_error' => 'Šio failo ištrinti nepavyko',
        'file_missing' => 'Pažymėtas failas nerastas',
        'file_already_deleted' => 'Pasirinktas failas jau buvo panaikintas',
        'file_missing_on_disk' => 'Šio importo failo diske nebėra. Jis galėjo būti ištrintas ne „Snipe-IT“ programoje. Ištrinkite šį įrašą ir iš naujo įkelkite failą, kad galėtumėte pabandyti dar kartą.',
        'file_empty' => 'Šiame faile nėra duomenų eilučių. Iš jo negalima nieko importuoti.',
        'already_processing' => 'Šį importavimą šiuo metu apdoroja kitas naudotojas. Palaukite, kol jis bus baigtas, ir tada bandykite dar kartą.',
        'header_row_missing' => 'Šiame faile nėra atpažįstamos antraštės eilutės. Ištrinkite šį įrašą ir vėl įkelkite failą, kad pabandytumėte dar kartą.',
        'header_row_has_malformed_characters' => 'Vienas ar keli antraštinės eilutės atributai turi netinkamai suformuotų UTF-8 simbolių',
        'content_row_has_malformed_characters' => 'Vienas ar keli pirmosios eilutės atributai turi netinkamai suformuotų UTF-8 simbolių',
        'transliterate_failure' => 'Transliteracija iš :encoding į UTF-8 nepavyko dėl netinkamų įvesties simbolių',
        'bulk_delete' => [
            'button' => 'Ištrinti pasirinktus (:count)',
            'confirm_title' => 'Ištrinti pasirinktus importavimo failus?',
            'confirm_body' => 'Jūs ketinate visam laikui ištrinti :count importo failą (-us). Šio veiksmo negalima atšaukti.',
            'confirm_button' => 'Panaikinti',
            'success' => 'Importavimo failas sėkmingai ištrintas.|Importavimo failai (:count) sėkmingai ištrinti.',
            'skipped' => 'Failai (:count) buvo praleisti, nes neturite teisės jų ištrinti.',
            'select_all' => 'Pažymėti visus šiame puslapyje esančius failus',
            'select_row' => 'Pasirinkite :file masiniam trynimui',
        ],
        'row_count' => '{0} Šiame faile nėra duomenų eilučių|{1} importuotinų duomenų eilučių skaičius (:count)|[2,*] importuotinų duomenų eilučių skaičius (:count)',
        'summary' => [
            'created' => 'Sukurta',
            'updated' => 'Atnaujinta',
            'skipped' => 'Praleisti kaip dublikatai',
            'errored' => 'Klaida',
            'no_changes' => 'Importavimas baigtas, tačiau nieko nebuvo sukurta ar atnaujinta. Visos eilutės buvo praleistos, paprastai dėl to, kad atitinkami įrašai jau egzistavo. Patikrinkite žemiau pateiktus skaičius ir, jei rezultatas neatitinka jūsų lūkesčių, pakoreguokite CSV failą arba importavimo tipą.',
        ],
        'type_required' => 'Prieš tęsdami pasirinkite importavimo tipą.',
        'processing' => 'Jūsų importavimas apdorojamas. Sulaukite kol jis pasibaigs, prieš uždarydami puslapį.',
        'backup_running' => 'Kuriama atsarginė kopiją prieš importavimą. Dideliems failams tai gali užtrukti ilgiau. Prašome palaukti.',
        'backup_label' => 'Atsarginė kopija prieš importavimą',
        'backup_complete' => 'Atsarginės kopijos kūrimas baigtas',
        'import_label' => 'Importavimas',
        'required_fields_missing' => 'Šie privalomi laukai nėra susieti: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(trūksta inventorinio numerio)',
            'missing_asset_tag_message' => 'Eilutė praleista: nenurodytas inventorinis numeris.',
            'asset_not_found_message' => 'Turto su tokiu inventoriniu numeriu nėra. Pirmiausia importuokite turtą, tada dar kartą paleiskite istorijos importavimą.',
            'target_not_matched_message' => 'Nerasta jokio „:target_type“, atitinkančio „:name“. Jei tai naudotojai, 1 žingsnyje pakeiskite susiejimo parametrus arba pirmiausia sukurkite naudotoją. Jei tai vieta, įsitikinkite, kad CSV faile nurodytas vietos pavadinimas tiksliai atitinka esamą vietą.',
            'invalid_target_type_message' => 'Tikslo tipas „:value“ neatpažintas. Naudokite „naudotojas“ arba „vieta“, arba palikite stulpelį tuščią – tuomet bus naudojamas numatytasis tipas „naudotojas“.',
        ],
        'wizard' => [
            'step_type' => 'Pasirinkite tipą',
            'step_map' => 'Susiekite laukus',
            'step_preview' => 'Peržiūra',
            'back' => 'Grįžti',
            'next' => 'Sekantis',
            'preview_button' => 'Peržiūra',
            'process' => 'Vykdyti importavimą',
            'preview_intro' => 'Atvaizduojamos pirmos eilutės (:count) pritaikius jūsų susiejimą. Jei prieš importavimą reikia pakoreguoti susietus atributus, naudokite mygtuką „Atgal“.',
        ],
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite panaikinti šį turtą?',
        'error' => 'Bandant panaikinti turtą įvyko klaida. Bandykite dar kartą.',
        'assigned_to_error' => '{1}Inventorinis numeris: :asset_tag šiuo metu yra išduotas. Paimkite šį įrenginį prieš panaikindami.|[2,*]Inventoriniai numeriai: :asset_tag šiuo metu yra išduoti. Paimkite šiuos įrenginius prieš panaikindami.',
        'nothing_updated' => 'Nebuvo pasirinkta jokio turto, todėl nieko nebuvo panaikinta.',
        'success' => 'Turtas sėkmingai panaikintas.',
    ],

    'checkout' => [
        'error' => 'Turtas nebuvo išduotas, bandykite dar kartą',
        'success' => 'Turtas sėkmingai išduotas.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
        'not_available' => 'Šis turtas negali būti išduodamas!',
        'no_assets_selected' => 'Turite pasirinkti bent vieną turto vienetą iš sąrašo',
    ],

    'multi-checkout' => [
        'error' => 'Turtas nebuvo išduotas, bandykite dar kartą|Turtas nebuvo išduotas, bandykite dar kartą',
        'success' => 'Turtas sėkmingai išduotas.|Turtas sėkmingai išduotas.',
    ],

    'multi-checkin' => [
        'error' => 'Turtas nebuvo paimtas, bandykite dar kartą|Turtas nebuvo paimtas, bandykite dar kartą',
        'success' => 'Turtas sėkmingai paimtas.|Turtas sėkmingai paimtas.',
        'no_assets_selected' => 'Turite pasirinkti bent vieną turto vienetą iš sąrašo',
    ],

    'multi-audit' => [
        'success' => 'Turto vienetai (:count) sėkmingai audituoti.|Turto vienetai (:count) sėkmingai audituoti.',
        'partial_error' => 'Audituota turto – :success, nepavyko – :failed. Peržiūrėkite žemiau nurodytas klaidas ir pabandykite dar kartą.|Audituota turto – :success, nepavyko – :failed. Peržiūrėkite žemiau nurodytas klaidas ir pabandykite dar kartą.',
        'no_assets_selected' => 'Turite pasirinkti bent vieną turto vienetą iš sąrašo',
    ],

    'checkin' => [
        'error' => 'Turtas nebuvo paimtas, bandykite dar kartą',
        'success' => 'Turtas sėkmingai paimtas.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Neteisingas naudotojas. Bandykite dar kartą.',
        'already_checked_in' => 'Šis turtas jau yra paimtas.',
        'force_checkin_orphaned_success' => 'Neteisingas priskyrimas sėkmingai ištrintas.',
        'force_checkin_not_orphaned' => 'Daiktas nėra netinkamo priskyrimo būsenoje.',
        'force_checkin_error' => 'Nepavyko ištrinti neteisingo priskyrimo.',

    ],

    'requests' => [
        'error' => 'Prašymas buvo nesėkmingas, bandykite dar kartą.',
        'success' => 'Prašymas sėkmingai pateiktas.',
        'canceled' => 'Prašymas sėkmingai atšauktas.',
        'cancel' => 'Atšaukti šio daikto užklausą',
        'duplicate' => 'Jūs jau turite galiojantį šio daikto užsakymą.',
        'no_active' => 'Neturite galiojančių šio daikto užsakymų, kuriuos galėtumėte atšaukti.',
        'insufficient_stock' => 'Turimų atsargų nepakanka šiam užsakymui įvykdyti. Pirmiausia papildykite atsargas.',
        'confirm_cancel_by_admin' => "Atšaukti :user daikto :item užsakymą?",
        'no_selection' => 'Nepasirinkta jokių eilučių vykdymui.',
        'row_stale' => 'Užsakymas Nr. :id nebėra aktyvus, todėl buvo praleistas.',
        'row_qty_invalid' => 'Užsakymas Nr. :id praleistas: jame nurodytas neteisingas kiekis, todėl buvo praleistas.',
        'row_user_missing' => 'Užsakymas Nr. :id neturi galiojančio tikslinio naudotojo, todėl buvo praleistas.',
        'row_asset_missing' => 'Užsakymas Nr. :id neturi galiojančio tikslinio turto, todėl buvo praleistas.',
        'row_asset_not_requesters' => 'Užsakymas Nr. :id praleistas: pasirinktas turtas nepriklauso užsakiusiajam.',
        'row_asset_not_available' => 'Užsakymas Nr. :id praleistas: pasirinktas turtas nėra šio modelio vienetas.',
        'row_asset_taken' => 'Užsakymas Nr. :id praleistas: pasirinktas turtas dar prieš pateikiant buvo išduotas kitam asmeniui.',
        'row_company_mismatch' => 'Užsakymas Nr. :id praleistas: :user negali gauti daiktų iš šios įmonės.',
        'row_over_allocated' => 'Užsakymas Nr. :id praleistas: nebuvo pakankamai atsargų pateikimo metu.',
        'no_target_assets_for_user' => ':user neturi jokio turto, į kurį būtų galima įdiegti.',
        'no_available_units' => 'Nėra šio modelio vienetų, kuriuos būtų galima išduoti.',
        'bulk_summary' => 'Įvykdyti užsakymai: :fulfilled iš :total.',
        'bulk_fulfill_notification_intro' => 'Tai bus pritaikyta kiekvienam pažymėtam užsakymui, kai jis bus įvykdytas:',
    ],

];
