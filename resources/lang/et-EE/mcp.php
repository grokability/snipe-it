<?php

return [

    // -----------------------------------------------------------------
    // Generic errors
    // -----------------------------------------------------------------
    'not_authenticated' => 'Autentimata',
    'unauthorized' => 'Õigused puuduvad',
    'scope_denied' => 'Sellel juurdepääsutõendil puudub toiminguks vajalik õiguste ulatus :scope',
    'scope_handshake_required' => 'Sellel juurdepääsutõendil pole MCP õiguste ulatusi. Autoriseeri ühendus uuesti ja anna enne MCP tööriistade kutsumist vähemalt üks mcp:* õiguste ulatus.',
    'scope_session_description' => 'Ava MCP seanss ja leia saadaolevad tööriistad. See EI anna õigust ühtegi tööriista kutsuda. Tööriistakutseteks on vajalikud vastavad võimekuste õiguste ulatused (lugemine, kirjutamine jne).',
    'scope_read_description' => 'Vaata vahendeid, kasutajaid, litsentse ja muid inventariandmeid',
    'scope_write_description' => 'Loo või muuda inventarikirjeid ja nende atribuute',
    'scope_delete_description' => 'Märgi kirjeid kustutatuks ja taasta kustutatud kirjeid',
    'scope_checkout_description' => 'Väljasta vahendeid, tarvikuid, komponente, kulumaterjale ja litsentse kasutajatele, asukohtadele või vahenditele',
    'scope_checkin_description' => 'Tagasta vahendeid, tarvikuid, komponente ja litsentse',
    'scope_superadmin_description' => 'Halda õigusrühmi. Vajalik rühmade loomiseks, uuendamiseks ja kustutamiseks ning rühmaloendi vaatamiseks',
    'id_or_name_required' => 'Määra kas id või name',
    'id_username_or_email_required' => 'Määra id, username või email',
    'provide_user_or_asset' => 'Määra kas assigned_to (kasutaja ID) või asset_id',

    // -----------------------------------------------------------------
    // "Not found" errors
    // -----------------------------------------------------------------
    'object_not_found' => 'Määratud kirjet tüübiga :type ei leitud',
    'asset_not_found' => 'Vahendi kirjet ei leitud',
    'user_not_found' => 'Kasutaja kirjet ei leitud',
    'accessory_not_found' => 'Tarviku kirjet ei leitud',
    'accessory_checkout_not_found' => 'Tarviku väljastuskirjet ei leitud',
    'component_not_found' => 'Komponendi kirjet ei leitud',
    'component_checkout_not_found' => 'Komponendi väljastuskirjet ei leitud',
    'consumable_not_found' => 'Kulumaterjali kirjet ei leitud',
    'license_not_found' => 'Litsentsi ei leitud',
    'license_seat_not_found' => 'Litsentsi kasutuskohta ei leitud',
    'department_not_found' => 'Osakonna kirjet ei leitud',
    'company_not_found' => 'Ettevõtte kirjet ei leitud',
    'category_not_found' => 'Kategooria kirjet ei leitud',
    'manufacturer_not_found' => 'Tootja kirjet ei leitud',
    'supplier_not_found' => 'Tarnija kirjet ei leitud',
    'status_label_not_found' => 'Olekusildi kirjet ei leitud',
    'location_not_found' => 'Asukoha kirjet ei leitud',
    'asset_model_not_found' => 'Vahendimudeli kirjet ei leitud',
    'depreciation_not_found' => 'Amortisatsiooni kirjet ei leitud',
    'group_not_found' => 'Rühma kirjet ei leitud',
    'checkout_target_not_found' => 'Määratud kirjet tüübiga :type ei leitud',

    // -----------------------------------------------------------------
    // Business rule errors
    // -----------------------------------------------------------------
    'asset_not_available' => 'Vahend :asset_tag pole väljastamiseks saadaval',
    'asset_not_checked_out' => 'Vahend :asset_tag pole praegu väljastatud',
    'asset_not_deleted' => 'Vahend pole kustutatud',
    'user_not_deleted' => 'Kasutaja pole kustutatud',
    'user_cannot_delete_self' => 'Sa ei saa oma kontot kustutada',
    'user_has_items' => 'Kasutajale on määratud kirjeid ning teda ei saa kustutada. Tagasta esmalt kõik kirjed.',
    'cannot_edit_auth_fields' => 'Sul puudub õigus muuta selle kasutaja autentimisvälju (username, email, password, activated)',
    'username_taken' => 'Kasutajanimi :username on juba kasutusel. Vali teine kasutajanimi.',
    'password_reset_sent' => 'Parooli lähtestamise link saadeti aadressile :email',
    'password_reset_user_inactive' => 'Kasutaja :username on mitteaktiivne ning talle ei saa parooli lähtestamise kirja saata',
    'password_reset_no_email' => 'Kasutajal :username puudub salvestatud e-posti aadress',
    'password_reset_ldap_user' => 'Kasutajat :username hallatakse LDAP-i kaudu ning ta ei saa parooli lähtestamist kasutada',
    'password_reset_send_failed' => 'Parooli lähtestamise kirja saatmine ebaõnnestus: :error',
    'invalid_permissions_format' => 'Õigused peavad olema kehtiv JSON-objekt',
    'invalid_permission_key' => 'Vigane õiguse võti: :key',
    'invalid_permission_value' => 'Õiguse :key väärtus peab olema 1 (luba) või -1 (keela)',
    'superadmin_required_for_groups' => 'Õigusrühmi saavad määrata ainult superadministraatorid',
    'no_available_seats' => 'Sellel litsentsil pole vabu kasutuskohti',
    'no_free_seat' => 'Sellele litsentsile ei leitud vaba kasutuskohta',
    'seat_not_checked_out' => 'See kasutuskoht pole praegu väljastatud',
    'no_units_available' => 'Selle tarviku eksemplare pole väljastamiseks saadaval',
    'no_units_remaining' => 'Ühtegi ühikut pole alles',
    'accessory_has_checkouts' => 'Tarviku eksemplare on väljastatud ning seda ei saa kustutada. Tagasta need esmalt.',
    'component_has_checkouts' => 'Komponendi ühikuid on väljastatud ning seda ei saa kustutada. Tagasta need esmalt.',
    'consumable_has_checkouts' => 'Kulumaterjalist on kirjeid väljastatud ning seda ei saa kustutada',
    'license_has_seats_assigned' => 'Litsentsi kasutuskohti on praegu määratud ning seda ei saa kustutada. Tagasta esmalt kõik kasutuskohad.',
    'department_has_users' => 'Osakonda on määratud kasutajaid ning seda ei saa kustutada. Määra esmalt kõik kasutajad ümber.',
    'location_has_child_locations' => 'Asukohal on alam-asukohti ning seda ei saa kustutada',
    'location_has_users' => 'Asukohta on määratud kasutajaid ning seda ei saa kustutada',
    'status_label_has_assets' => 'Olekusildile on määratud vahendeid ning seda ei saa kustutada',
    'model_has_assets' => 'Mudeliga on seotud vahendeid ning seda ei saa kustutada',
    'bulk_confirm_count_required' => 'Filtri järgi kustutamisel on confirm_count kohustuslik. Vaata esmalt partii eelvaadet parameetriga dry_run=true ning kutsu seejärel uuesti, määrates confirm_count väärtuseks täpse total_matched väärtuse.',
    'bulk_confirm_count_mismatch' => 'confirm_count :expected ei vasta tegelikule ridade arvule :actual. Filtri tulemus võib olla pärast eelvaadet muutunud. Käivita uuesti parameetriga dry_run=true ja proovi uuesti.',
    'bulk_batch_has_undeletable' => 'Partii sisaldab :count rida, mida ei saa kustutada (väljastatud, alamkirjetega jne). Nende vahelejätmiseks ja ülejäänute kustutamiseks määra skip_undeletable=true või kõrvalda takistused ja proovi uuesti.',
    'bulk_dry_run_summary' => 'Eelvaade: :total vastavat rida, kinnitamisel kustutatakse :deletable. Muudatusi pole tehtud.',
    'bulk_delete_completed' => 'Hulgikustutamine lõpetatud. Eemaldati :count rida.',
    'bulk_delete_failed' => 'Hulgikustutamine ebaõnnestus ja võeti tagasi. Põhjus: :failures',

    // -----------------------------------------------------------------
    // Operation failures
    // -----------------------------------------------------------------
    'create_failed' => 'Loomine ebaõnnestus: :error',
    'update_failed' => 'Uuendamine ebaõnnestus: :error',
    'delete_failed' => 'Kustutamine ebaõnnestus',
    'delete_failed_error' => 'Kustutamine ebaõnnestus: :error',
    'checkin_failed' => 'Tagastamine ebaõnnestus',
    'checkin_failed_error' => 'Tagastamine ebaõnnestus: :error',
    'checkout_failed' => 'Väljastamine ebaõnnestus',
    'audit_failed' => 'Audit ebaõnnestus: :error',
    'note_save_failed' => 'Märkuse salvestamine ebaõnnestus',

    // -----------------------------------------------------------------
    // Asset messages
    // -----------------------------------------------------------------
    'asset_found' => 'Vahend :asset_tag leitud',
    'asset_created' => 'Vahendi :asset_tag loomine õnnestus',
    'asset_updated' => 'Vahendi :asset_tag uuendamine õnnestus',
    'asset_deleted' => 'Vahendi :asset_tag kustutamine õnnestus',
    'asset_restored' => 'Vahendi :asset_tag taastamine õnnestus',
    'asset_checked_out' => 'Vahendi :asset_tag väljastamine õnnestus',
    'asset_checked_in' => 'Vahendi :asset_tag tagastamine õnnestus',
    'asset_audited' => 'Vahendi :asset_tag audit registreeritud',
    'note_added_to_asset' => 'Vahendile :asset_tag lisati märkus',
    'note_added_successfully' => 'Märkuse lisamine õnnestus',

    // -----------------------------------------------------------------
    // User messages
    // -----------------------------------------------------------------
    'current_user' => 'Praegune kasutaja: :username',
    'user_found' => 'Kasutaja :username leitud',
    'user_created' => 'Kasutaja :username loomine õnnestus',
    'user_updated' => 'Kasutaja :username uuendamine õnnestus',
    'user_deleted' => 'Kasutaja :username kustutamine õnnestus',
    'user_restored' => 'Kasutaja :username taastamine õnnestus',
    'two_factor_reset' => 'Kasutaja :username kaheastmeline autentimine lähtestati',
    'user_assets_found' => 'Kasutajale :username leiti :count vahendit',
    'user_permissions_found' => 'Kasutajal :username on :count kehtivat õigust',
    'profile_updated' => 'Profiili uuendamine õnnestus',

    // -----------------------------------------------------------------
    // Accessory messages
    // -----------------------------------------------------------------
    'accessory_created' => 'Tarviku :name loomine õnnestus',
    'accessory_updated' => 'Tarviku :name uuendamine õnnestus',
    'accessory_deleted' => 'Tarviku :name kustutamine õnnestus',
    'accessory_checked_out' => 'Tarviku :name väljastamine õnnestus',
    'accessory_checked_in' => 'Tarviku :name tagastamine õnnestus',

    // -----------------------------------------------------------------
    // Component messages
    // -----------------------------------------------------------------
    'component_created' => 'Komponendi :name loomine õnnestus',
    'component_updated' => 'Komponendi :name uuendamine õnnestus',
    'component_deleted' => 'Komponendi :name kustutamine õnnestus',
    'component_checked_out' => 'Komponent :name väljastati vahendile :asset_tag',
    'component_checked_in' => 'Komponendi :name tagastamine õnnestus',

    // -----------------------------------------------------------------
    // Consumable messages
    // -----------------------------------------------------------------
    'consumable_found' => 'Kulumaterjal :name leitud',
    'consumable_created' => 'Kulumaterjali :name loomine õnnestus',
    'consumable_updated' => 'Kulumaterjali :name uuendamine õnnestus',
    'consumable_deleted' => 'Kulumaterjali :name kustutamine õnnestus',
    'consumable_checked_out' => 'Kulumaterjal :name väljastati kasutajale :username',

    // -----------------------------------------------------------------
    // License messages
    // -----------------------------------------------------------------
    'license_found' => 'Litsents :name leitud',
    'license_created' => 'Litsentsi :name loomine õnnestus',
    'license_updated' => 'Litsentsi :name uuendamine õnnestus',
    'license_deleted' => 'Litsentsi :name kustutamine õnnestus',
    'license_seat_checked_out_user' => 'Litsentsi kasutuskoht väljastati kasutajale :username',
    'license_seat_checked_out_asset' => 'Litsentsi kasutuskoht väljastati vahendile :asset_tag',
    'license_seat_checked_in' => 'Litsentsi kasutuskoha :id tagastamine õnnestus',

    // -----------------------------------------------------------------
    // Department messages
    // -----------------------------------------------------------------
    'department_created' => 'Osakonna :name loomine õnnestus',
    'department_updated' => 'Osakonna :name uuendamine õnnestus',
    'department_deleted' => 'Osakonna :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Company messages
    // -----------------------------------------------------------------
    'company_found' => 'Ettevõte :name leitud',
    'company_created' => 'Ettevõtte :name loomine õnnestus',
    'company_updated' => 'Ettevõtte :name uuendamine õnnestus',
    'company_deleted' => 'Ettevõtte :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Category messages
    // -----------------------------------------------------------------
    'category_found' => 'Kategooria :name leitud',
    'category_created' => 'Kategooria :name loomine õnnestus',
    'category_updated' => 'Kategooria :name uuendamine õnnestus',
    'category_deleted' => 'Kategooria :name kustutamine õnnestus',
    'category_delete_failed' => 'Kategooriat ei saa kustutada: :error',

    // -----------------------------------------------------------------
    // Manufacturer messages
    // -----------------------------------------------------------------
    'manufacturer_found' => 'Tootja :name leitud',
    'manufacturer_created' => 'Tootja :name loomine õnnestus',
    'manufacturer_updated' => 'Tootja :name uuendamine õnnestus',
    'manufacturer_deleted' => 'Tootja :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Supplier messages
    // -----------------------------------------------------------------
    'supplier_found' => 'Tarnija :name leitud',
    'supplier_created' => 'Tarnija :name loomine õnnestus',
    'supplier_updated' => 'Tarnija :name uuendamine õnnestus',
    'supplier_deleted' => 'Tarnija :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Status label messages
    // -----------------------------------------------------------------
    'status_label_found' => 'Olekusilt :name leitud',
    'status_label_created' => 'Olekusildi :name loomine õnnestus',
    'status_label_updated' => 'Olekusildi :name uuendamine õnnestus',
    'status_label_deleted' => 'Olekusildi :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Location messages
    // -----------------------------------------------------------------
    'location_found' => 'Asukoht :name leitud',
    'location_created' => 'Asukoha :name loomine õnnestus',
    'location_updated' => 'Asukoha :name uuendamine õnnestus',
    'location_deleted' => 'Asukoha :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Asset model messages
    // -----------------------------------------------------------------
    'asset_model_found' => 'Vahendimudel :name leitud',
    'asset_model_created' => 'Vahendimudeli :name loomine õnnestus',
    'asset_model_updated' => 'Vahendimudeli :name uuendamine õnnestus',
    'asset_model_deleted' => 'Vahendimudeli :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Depreciation messages
    // -----------------------------------------------------------------
    'depreciation_found' => 'Amortisatsioon :name leitud',
    'depreciation_created' => 'Amortisatsiooni :name loomine õnnestus',
    'depreciation_updated' => 'Amortisatsiooni :name uuendamine õnnestus',
    'depreciation_deleted' => 'Amortisatsiooni :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Group messages
    // -----------------------------------------------------------------
    'group_found' => 'Rühm :name leitud',
    'group_created' => 'Rühma :name loomine õnnestus',
    'group_updated' => 'Rühma :name uuendamine õnnestus',
    'group_deleted' => 'Rühma :name kustutamine õnnestus',

    // -----------------------------------------------------------------
    // Maintenance messages
    // -----------------------------------------------------------------
    'maintenance_created' => 'Hoolduse :name loomine õnnestus',

    // -----------------------------------------------------------------
    // List messages
    // -----------------------------------------------------------------
    'list_assets' => 'Leiti :total vahendit, tagastatakse :count',
    'list_users' => 'Leiti :total kasutajat, tagastatakse :count',
    'list_consumables' => 'Leiti :total kulumaterjali, tagastatakse :count',
    'list_licenses' => 'Leiti :total litsentsi, tagastatakse :count',
    'list_companies' => 'Leiti :total ettevõtet, tagastatakse :count',
    'list_categories' => 'Leiti :total kategooriat, tagastatakse :count',
    'list_manufacturers' => 'Leiti :total tootjat, tagastatakse :count',
    'list_suppliers' => 'Leiti :total tarnijat, tagastatakse :count',
    'list_status_labels' => 'Leiti :total olekusilti, tagastatakse :count',
    'list_locations' => 'Leiti :total asukohta, tagastatakse :count',
    'list_asset_models' => 'Leiti :total vahendimudelit, tagastatakse :count',
    'list_depreciations' => 'Leiti :total amortisatsiooni, tagastatakse :count',
    'list_groups' => 'Leiti :total rühma, tagastatakse :count',
    'list_maintenances' => 'Leiti :total hooldust, tagastatakse :count',
    'list_activity' => 'Leiti :total tegevuslogi kirjet, tagastatakse :count',
    'list_accessories' => 'Leiti :total tarvikut, tagastatakse :count',
    'list_maintenance_types' => 'Leiti :total hoolduse tüüpi, tagastatakse :count',
    'maintenance_type_created' => 'Hoolduse tüüp :name loodud.',
    'find_similar_models_identifier_required' => 'Lähtemudeli AssetModel tuvastamiseks määra model_id või model_name.',
    'find_similar_models_summary' => 'Hinnati :total mudeliga :source sarnast kandidaati, tagastatakse :count parimat.',

    // -----------------------------------------------------------------
    // Upload tools
    // -----------------------------------------------------------------
    'id_or_asset_tag_required' => 'Vahendi tuvastamiseks on kohustuslik id või asset_tag.',
    'upload_image_decode_failed' => 'Esitatud image_base64 dekodeerimine ebaõnnestus. Kontrolli, kas string on kehtiv base64.',
    'upload_image_tempfile_failed' => 'Üleslaaditud pildi salvestamine serveri ajutisse faili ebaõnnestus.',
    'upload_image_save_failed' => 'Pildi lisamine ebaõnnestus. Põhjus: :error',
    'upload_image_success' => 'Pilt lisati vahendile :asset_tag.',
    'list_asset_notes' => 'Vahendile :asset_tag leiti :total märkust, tagastatakse :count',
    'list_uploads' => 'Kirjele tüübiga :type leiti :total üleslaaditud faili, tagastatakse :count',
    'list_history' => 'Kirjele tüübiga :type leiti :total ajalookirjet, tagastatakse :count',

    // -----------------------------------------------------------------
    // Offboard user tool
    // -----------------------------------------------------------------
    'offboard_identifier_required' => 'Lahkuva kasutaja tuvastamiseks määra username või first_name koos last_name väärtusega.',
    'offboard_ambiguous_user' => 'Sisestatud nimele vastas mitu kasutajat. Küsi administraatorilt, millise kasutaja lahkumine vormistada, ning kutsu tööriist uuesti tema kasutajanimega.',
    'offboard_dry_run_message' => 'Ainult eelvaade. Muudatusi pole tehtud. Toimingu tegemiseks kutsu tööriist uuesti parameetriga dry_run=false.',
    'offboard_dry_run_summary' => 'Lahkumise eelvaade kasutajale :username: vabastataks :assets vahendit, :accessories tarvikut ja :seats litsentsi kasutuskohta.',
    'offboard_completed_message' => 'Kasutaja :username lahkumine vormistatud. Kasutajakonto märgiti kustutatuks.',
    'offboard_completed_summary' => 'Lahkumine vormistatud kasutajale :username: vabastati :assets vahendit, :accessories tarvikut ja :seats litsentsi kasutuskohta ning kasutaja märgiti kustutatuks.',
    'offboard_failed' => 'Lahkumise vormistamine ebaõnnestus ja võeti tagasi. Põhjus: :failures',
    'offboard_cross_company_blocked' => 'Kasutaja :username lahkumist ei saa vormistada, sest ta kuulub ühte või mitmesse ettevõttesse, millele sul puudub juurdepääs. Ettevõtetevaheliste määrangute orvuks jäämise vältimiseks peab selle kasutaja lahkumise vormistama superkasutaja.',
    'fk_not_found' => 'Kirjet tingimusega :field = „:value” ei leitud. Määra õige nimi või kasuta välja :field _id varianti arvulise ID-ga.',
    'fk_ambiguous' => 'Tingimusele :field = „:value” vastab mitu kirjet. Eristamiseks määra selle asemel arvuline :id_field.',

    // -----------------------------------------------------------------
    // Transfer user assets tool
    // -----------------------------------------------------------------
    'transfer_identifier_required' => 'Poole :side kasutaja jaoks määra username või first_name koos last_name väärtusega.',
    'transfer_user_not_found' => 'Poole :side kasutaja esitatud identifikaatoritele vastavat kasutajat ei leitud.',
    'transfer_ambiguous_user' => 'Poole :side identifikaatoritele vastas mitu kasutajat. Küsi administraatorilt, millist kasutajat kasutada, ning kutsu tööriist uuesti tema kasutajanimega.',
    'transfer_same_user' => 'Lähtekasutaja ja saaja peavad olema erinevad.',
    'transfer_dry_run_message' => 'Ainult eelvaade. Muudatusi pole tehtud. Toimingu tegemiseks kutsu tööriist uuesti parameetriga dry_run=false.',
    'transfer_dry_run_summary' => 'Üleandmise eelvaade kasutajalt :from kasutajale :to: üle antaks :assets vahendit, :accessories tarvikut ja :seats litsentsi kasutuskohta.',
    'transfer_completed_message' => 'Üleandmine kasutajalt :from kasutajale :to lõpetatud.',
    'transfer_completed_summary' => 'Üleandmine kasutajalt :from kasutajale :to: üle anti :assets vahendit, :accessories tarvikut ja :seats litsentsi kasutuskohta.',
    'transfer_failed' => 'Üleandmine ebaõnnestus ja võeti tagasi. Põhjus: :failures',

];
