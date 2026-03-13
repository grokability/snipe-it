<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Abasebenzisi bakho babuyekezwe ngempumelelo, kodwa ukungena kwakho kwemenenja akulondoloziwe ngoba umphathi oyikhethile naye ohlwini lomsebenzisi oluzohlelwa, futhi abasebenzisi bangase bangabi umphathi wabo. Sicela ukhethe abasebenzisi bakho futhi, ngaphandle kwamenenja.',
    'user_exists'               => 'Umsebenzisi usuvele ukhona!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Insimu yokungena ngemvume iyadingeka',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Iphasiwedi iyadingeka.',
    'insufficient_permissions'  => 'Izimvume ezinganele.',
    'user_deleted_warning'      => 'Lo msebenzisi ususiwe. Kuzodingeka ubuyisele lo msebenzisi ukuwahlela noma ubanikeze amafa amasha.',
    'ldap_not_configured'        => 'Ukuhlanganiswa kwe-LDAP akulungiselelwe lokhu kufakwa.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Umsebenzisi udale ngempumelelo.',
        'update'    => 'Umsebenzisi ubuyekezwe ngempumelelo.',
        'update_bulk'    => 'Abasebenzisi baphinde babuyekezwe ngempumelelo!',
        'delete'    => 'Umsebenzisi ususwe ngempumelelo.',
        'ban'       => 'Umsebenzisi uvinjelwe ngempumelelo.',
        'unban'     => 'Umsebenzisi uvinjelwe ngempumelelo.',
        'suspend'   => 'Umsebenzisi umiswe ngempumelelo.',
        'unsuspend' => 'Umsebenzisi uphumelelwanga ngempumelelo.',
        'restored'  => 'Umsebenzisi ubuyiselwe ngempumelelo.',
        'import'    => 'Abasebenzisi bangeniswe ngempumelelo.',
    ),

    'error' => array(
        'create' => 'Kube nenkinga yokudala umsebenzisi. Ngicela uzame futhi.',
        'update' => 'Kube nenkinga yokuvuselela umsebenzisi. Ngicela uzame futhi.',
        'delete' => 'Kube nenkinga yokusula umsebenzisi. Ngicela uzame futhi.',
        'delete_has_assets' => 'Lo msebenzisi unezinto ezinikezwe futhi azikwazanga ukususwa.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Kube nenkinga engalindeleki umsebenzisi. Ngicela uzame futhi.',
        'import'    => 'Kube nenkinga yokungenisa abasebenzisi. Ngicela uzame futhi.',
        'asset_already_accepted' => 'Lelifa selivele lamukelwe.',
        'accept_or_decline' => 'Kufanele wamukele noma unqabe le mali.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Impahla oyizame ukwamukela ayizange ihlolwe kuwe.',
        'ldap_could_not_connect' => 'Ayikwazanga ukuxhuma kuseva ye-LDAP. Sicela uhlole ukumisa kweseva yakho ye-LDAP kufayili ye-LDAP config. <br>Iphutha kusuka kwiseva ye-LDAP:',
        'ldap_could_not_bind' => 'Ayikwazanga ukubopha iseva ye-LDAP. Sicela uhlole ukumisa kweseva yakho ye-LDAP kufayili ye-LDAP config. <br>Iphutha kusuka kwiseva ye-LDAP:',
        'ldap_could_not_search' => 'Ayikwazanga ukusesha isiphakeli se-LDAP. Sicela uhlole ukumisa kweseva yakho ye-LDAP kufayili ye-LDAP config. <br>Iphutha kusuka kwiseva ye-LDAP:',
        'ldap_could_not_get_entries' => 'Ayikwazanga ukungena okuvela kuseva ye-LDAP. Sicela uhlole ukumisa kweseva yakho ye-LDAP kufayili ye-LDAP config. <br>Iphutha kusuka kwiseva ye-LDAP:',
        'password_ldap' => 'Iphasiwedi yale akhawunti ilawulwa yi-LDAP / Active Directory. Sicela uxhumane nomnyango wakho we-IT ukushintsha iphasiwedi yakho.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Ifayela alisusiwe. Ngicela uzame futhi.',
        'success' => 'Ifayili isusiwe ngempumelelo.',
    ),

    'upload' => array(
        'error'   => 'Amafayela (ama) awalayishiwe. Ngicela uzame futhi.',
        'success' => 'Amafayela (ama) alayishwe ngempumelelo.',
        'nofiles' => 'Awukakhethi noma yimaphi amafayela okulayishwa',
        'invalidfiles' => 'Ifayela elilodwa noma ngaphezulu likhulu kakhulu noma ifayelathi engavumelekile. Amafayela afakiwe avunyelwe i-png, i-gif, i-jpg, i-doc, i-docx, i-pdf, ne-txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);