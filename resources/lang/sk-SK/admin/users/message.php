<?php

return array(

    'accepted'                  => 'Úspešne ste potvrdili prijatie majetku.',
    'declined'                  => 'Odmietnutie majetku bolo úspešné.',
    'bulk_manager_warn'	        => 'Používatelia boli úspešné aktualizovaný, avčak položka manažér nebola uložená, pretože zvolený manažér sa taktiež nachádzal v zoznam na úpravu a používatel nemôže byť sám sebe manazérom. Prosim zvoľte Vašich používateľov znovu s vynechaním manažéera.',
    'user_exists'               => 'Používateľ už existuje!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Používateľ neexistuje alebo nemáte oprávnenie na jeho zobrazenie.',
    'user_login_required'       => 'Prihlasovacie meno je povinné',
    'user_has_no_assets_assigned' => 'Momentálne nie je priradený používateľovi žiaden majetok.',
    'user_password_required'    => 'Heslo je povinné.',
    'insufficient_permissions'  => 'Nedostatočné oprávnenia.',
    'user_deleted_warning'      => 'Tento používateľ bol odstránený. Používateľa musíte obnoviť, ak ho chcete upraviť alebo mu priradiť majetok.',
    'ldap_not_configured'        => 'LDAP prepojenie nebolo nastavené pre túto inštaláciu.',
    'password_resets_sent'      => 'Vybraní používatelia sú aktivovaný. Na ich adresu bola zaslaný okaz na resetovanie hesla.',
    'password_reset_sent'       => 'Odkaz na obnovenie hesla bol zaslaný na emailovú adresu :email!',
    'user_has_no_email'         => 'Tento používateľ nemá zadanú emailovú adresu v profile.',
    'log_record_not_found'        => 'Pre tohto používateľa sa nepodarilo nájsť odpovedajúci záznam v logoch.',


    'success' => array(
        'create'    => 'Používateľ bol úspešne vytovrený.',
        'update'    => 'Používateľ bol úspešne upravený.',
        'update_bulk'    => 'Používatelia boli úspešne upravení!',
        'delete'    => 'Používateľ bol úspešne odstránený.',
        'ban'       => 'Používateľ bol úspešné zablokovaný.',
        'unban'     => 'Používateľ bol úspešne odblokovaný.',
        'suspend'   => 'Používateľ bol úspešne pozastavený.',
        'unsuspend' => 'Používateľ bol úspešne obnovený.',
        'restored'  => 'Používateľ bol úspešne obnovený.',
        'import'    => 'Používatelia boli úspešne importovaní.',
    ),

    'error' => array(
        'create' => 'Pri vytváraní používateľa sa vyskytla chby. Skúste prosím znovu.',
        'update' => 'Pri aktualizácií používateľa sa vyskytla chyba. Prosím skúste znovu.',
        'delete' => 'Pri odstraňovaní používateľa sa vyskytla chyba. Skúste prosím neskôr.',
        'delete_has_assets' => 'Tento používateľ ma priradené položky a nemôže byť odstránený.',
        'delete_has_assets_var' => 'Tento používateľ má stále priradený majetok. Prosím prevezmite najprv majetok.|Tento používateľ má stále priradených :count majetkov. Prosím prevezmite najprv všetok majetok.',
        'delete_has_licenses_var' => 'Tento používateľ má stále priradené licenčné sloty. Prosím prevezmite ich najprv.|Tento používateľ má stále priradených :count licenčných slotov. Prosím prevezmite ich najprv.',
        'delete_has_accessories_var' => 'Tento používateľ ma priradené príslušenstvo. Prosím prevezmite ho najprv.|Tento používateľ má stále priradených :count príslušenstiev. Prosím prevezmite ich najprv.',
        'delete_has_locations_var' => 'Tento používateľ je správcom lokalite. Prosím zvoľte najprv iného správcu.|Tento používateľ stále manažuje :count lokalít. Prosím zvoľte najprv iných správcov.',
        'delete_has_users_var' => 'Tento používateľ stále manažuje iného používateľa. Prosím nastavte najprv iného manažéra pre daného používateľa.|Tento používateľ stále manažuje :count používateľov. Prosím nastavte im najprv iného manažéra.',
        'unsuspend' => 'Pri pokuse o zrušenie pozastavenia používateľa nastala chyba. Skúste prosím znovu.',
        'import'    => 'Pri importovaní používateľov nastala chyba. Prosím skúste znovu.',
        'asset_already_accepted' => 'Tento majetok bol už prijatý.',
        'accept_or_decline' => 'Musíte prijať alebo odmietnuť tento majetok.',
        'cannot_delete_yourself' => 'Budeme veľmi smutní, ak zmažete samého seba. Prosím zvážte to.',
        'incorrect_user_accepted' => 'Majetok, ktorý sa pokúšate prijať, Vám nebol priradený.',
        'ldap_could_not_connect' => 'Nepodarilo sa pripojiť k LDAP serveru. Prosím skontrolujte nastavenia LDAP serveru v Admin nastavenia > LDAP/AD <br>Chyba LDAP serveru:',
        'ldap_could_not_bind' => 'Nepodarilo sa napojiť na LDAP server. Prosím skontrolujte nastavenia LDAP serveru v Admin nastavenia > LDAP/AD <br>Chyba LDAP serveru: ',
        'ldap_could_not_search' => 'Nepodarilo sa vyhladať v rámci LDAP serveru. Prosím skontrolujte nastavenia LDAP serveru v Admin nastavenia > LDAP/AD a všetky lokality ktoré môžu mať nastavené OU. <br>Chyba LDAP serveru:',
        'ldap_could_not_get_entries' => 'Nepodarilo sa získať záznamy z LDAP servera. Prosím skontrolujte nastavenia LDAP serveru v Admin nastavenia > LDAP/AD a všetky lokality ktoré môžu mať nastavené OU. <br>Chyba LDAP serveru:',
        'password_ldap' => 'Heslo pre tento účet je spravované cez LDAP/Active Directory. Pre zmneu hesla prosím kontaktujte Vaše IT oddelenie. ',
        'multi_company_items_assigned' => 'Tento používateľ má priradené položky vo vlastníctve inej spoločnosti. Prosím prevezmite ich alebo upravte spoločnosť.'
    ),

    'deletefile' => array(
        'error'   => 'Súbor nebol odstránený. Prosím skúste znovu.',
        'success' => 'Súbor bol úspešne odstránený.',
    ),

    'upload' => array(
        'error'   => 'Súbor(y) sa nepodarilo nahrať. Skúste prosím znovu.',
        'success' => 'Súbor(y) bol úspešne nahraté.',
        'nofiles' => 'Nevybrali ste žiadne súbre pre nahratie',
        'invalidfiles' => 'Jeden alebo viacero Vašich súborov je príliš veľkých alebo nie su podporované. Podporované typy súborov sú png, gif, jpg, doc, docx, pdf a txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Tento používateľ nemá nastavenú emailovú adresu.',
        'success' => 'Používateľ bol notifikovaný o aktuálne priradenom majetku.'
    )
);