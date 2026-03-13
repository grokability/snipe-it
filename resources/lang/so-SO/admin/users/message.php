<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Isticmaalayaashaada si guul leh ayaa loo cusboonaysiiyay, si kastaba ha ahaatee gelitaanka maamulahaaga lama kaydin sababtoo ah maareeyaha aad dooratay sidoo kale waxa uu ku jiray liiska isticmaalayaasha ee la tafatiray, isticmaalayaashuna waxa laga yaabaa in aanay noqon maamulahooda. Fadlan dooro isticmaalayaashaada mar labaad, marka laga reebo maamulaha.',
    'user_exists'               => 'Isticmaale ayaa hore u jiray!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Goobta galitaanka ayaa loo baahan yahay',
    'user_has_no_assets_assigned' => 'Ma jirto hanti hadda loo qoondeeyay isticmaalaha.',
    'user_password_required'    => 'Furaha sirta ah ayaa loo baahan yahay.',
    'insufficient_permissions'  => 'Ogolaanshaha aan ku filnayn.',
    'user_deleted_warning'      => 'Isticmaalahan waa la tirtiray Waa inaad soo celisaa isticmaalahan si aad wax uga beddesho ama ugu meelayso hanti cusub.',
    'ldap_not_configured'        => 'Isku dhafka LDAP looma habeynin rakibaaddan.',
    'password_resets_sent'      => 'Isticmaalayaasha la doortay ee shaqaysiiyay oo wata ciwaanno iimayl sax ah ayaa loo diray isku xidhka dib u dejinta erayga sirta ah.',
    'password_reset_sent'       => 'Isku xirka dib u dejinta erayga sirta ah ayaa loo diray :email!',
    'user_has_no_email'         => 'Isticmaalahaan kuma laha ciwaanka iimaylka profile kooda.',
    'log_record_not_found'        => 'Diiwaanka diiwaanka u dhigma ee isticmaalaha waa la heli waayay.',


    'success' => array(
        'create'    => 'Isticmaalaha si guul leh ayaa loo abuuray.',
        'update'    => 'Isticmaalaha si guul leh ayaa loo cusboonaysiiyay.',
        'update_bulk'    => 'Isticmaalayaasha si guul leh ayaa loo cusboonaysiiyay!',
        'delete'    => 'Isticmaalaha si guul leh ayaa loo tirtiray.',
        'ban'       => 'Isticmaalaha si guul leh waa la mamnuucay.',
        'unban'     => 'Isticmaalaha si guul leh ayaa laga saaray.',
        'suspend'   => 'Isticmaalaha si guul leh ayaa loo hakiyay.',
        'unsuspend' => 'Isticmaalaha si guul leh ayaa loo hakiyay.',
        'restored'  => 'Isticmaalaha si guul leh ayaa loo soo celiyay.',
        'import'    => 'Isticmaalayaasha si guul leh ayaa loo soo dejiyay.',
    ),

    'error' => array(
        'create' => 'Waxaa jirtay arin abuurista isticmaalaha Fadlan isku day mar kale.',
        'update' => 'Waxaa jirtay arrin la cusboonaysiiyay isticmaaluhu. Fadlan isku day mar kale.',
        'delete' => 'Waxaa jirtay arrin la tirtiray isticmaaluhu. Fadlan isku day mar kale.',
        'delete_has_assets' => 'Isticmaalahaan wuxuu leeyahay walxo loo qoondeeyay lamana tirtiri karo.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Waxaa jirtay arrin aan la hakin isticmaaluhu. Fadlan isku day mar kale.',
        'import'    => 'Waxaa jirtay arin soo dejinta isticmaalayaasha Fadlan isku day mar kale.',
        'asset_already_accepted' => 'Hantidan mar hore waa la aqbalay.',
        'accept_or_decline' => 'Waa inaad aqbashaa ama diiddaa hantidan.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Hantida aad isku dayday inaad aqbasho adiga laguma hubin.',
        'ldap_could_not_connect' => 'Waa lagu xidhi kari waayay serfarka LDAP Fadlan ka hubi server-kaaga LDAP ee ku jira faylka habaynta LDAP. Khalad ka yimid Server LDAP:',
        'ldap_could_not_bind' => 'Laguma xidhi karo serfarka LDAP Fadlan ka hubi server-kaaga LDAP ee ku jira faylka habaynta LDAP. <br>Khalad ka yimid Server LDAP: ',
        'ldap_could_not_search' => 'Ma baadhi karin server-ka LDAP Fadlan ka hubi server-kaaga LDAP ee ku jira faylka habaynta LDAP. <br>Khalad ka yimid Server LDAP:',
        'ldap_could_not_get_entries' => 'Waa lagu xidhi kari waayay serfarka LDAP Fadlan ka hubi server-kaaga LDAP ee ku jira faylka habaynta LDAP. Khalad ka yimid Server LDAP:',
        'password_ldap' => 'Furaha koontada waxaa maamula LDAP/Hagaha Firfircoon. Fadlan la xidhiidh waaxda IT-ga si aad u bedesho eraygaaga sirta ah. ',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Faylka lama tirtirin Fadlan isku day mar kale.',
        'success' => 'Faylka si guul leh waa la tirtiray.',
    ),

    'upload' => array(
        'error'   => 'Faylka lama soo rarin Fadlan isku day mar kale.',
        'success' => 'Faylka(yada) si guul leh loo soo raray.',
        'nofiles' => 'Ma aadan dooran wax fayl ah oo la soo geliyo',
        'invalidfiles' => 'Mid ama in ka badan oo faylashaada ah aad bay u weyn yihiin ama waa nooc faylal ah oo aan la oggolayn. Noocyada faylalka la oggol yahay waa png, gif, jpg, doc, docx, pdf, iyo txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Isticmaalahaan ma heysto Email loo abuuray.',
        'success' => 'Isticmaalaha waa la ogeysiiyay wax ku saabsan alaabtooda hadda.'
    )
);