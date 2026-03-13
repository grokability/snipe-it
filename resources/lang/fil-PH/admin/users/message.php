<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Ang iyong mga user ay matagumpay nang nai-update, subalit ang iyong manager entry ay hindi nai-save dahil ang manager na iyong pinili ay kabilang sa listahan ng user na kailangang i-edit, at ang mga user ay maaaring wala sa sarili nilang pamamahala. Mangyaring pumiling muli ng iyong user, hindi kasama ang manager.',
    'user_exists'               => 'Ang user ay umiiral na!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Ang field ng login ay kinakailangan',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Ang password ay kinakailangan.',
    'insufficient_permissions'  => 'Hindi sapat na mga pahintulot.',
    'user_deleted_warning'      => 'Ang user na ito ay nai-delete na. Kailangang ibalik ang user na ito upang i-edit o mag-assign ng bagong mga asset.',
    'ldap_not_configured'        => 'Ang integrasyon ng LDAP ay hindi nai-configure sa pag-install na ito.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Ang user ay matagumpay na nalikha.',
        'update'    => 'Ang user ay matagumpay na nai-update.',
        'update_bulk'    => 'Ang mga user ay matagumpay nai-update!',
        'delete'    => 'Ang user ay matagumpay na nai-delete.',
        'ban'       => 'Ang user ay matagumpay na nai-ban.',
        'unban'     => 'Ang user ay matagumpay na nai-unban.',
        'suspend'   => 'Ang user ay matagumpay na nasuspende.',
        'unsuspend' => 'Ang user ay matagumpay na hindi na sinuspende.',
        'restored'  => 'Ang user ay matagumpay na naibalik sa dati.',
        'import'    => 'Ang mga user ay matagumpay nang na-import.',
    ),

    'error' => array(
        'create' => 'Mayroong isyu sa pagsagawa ng user. Mangyaring subukang muli.',
        'update' => 'Mayroong isyu sa pag-update sa user. Mangyaring subukang muli.',
        'delete' => 'Mayroong isyu sa pag-delete ng user. Mangyaring subukang muli.',
        'delete_has_assets' => 'Ang user na ito any may mga aytem na nai-assign at hindi maaring i-delete.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Mayroong isyu sa pagtanggal ng suspenso sa user. Mangyaring subukang muli.',
        'import'    => 'Mayroong isyu sa pag-import ng mga user. Mangyaring subukang muli.',
        'asset_already_accepted' => 'Ang asset na ito ay tinanggap na.',
        'accept_or_decline' => 'Dapat mong tanggapin o kaya tanggihan ang asset na ito.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Ang asset na tinangka mong tanggapin ay hindi nai-check out sa iyo.',
        'ldap_could_not_connect' => 'Hindi maka-konekta sa serber ng LDAP. Mangyaring surrin ang iyong konpigurasyon ng serber ng LDAP sa LDAP config file. <br>May error mula sa Serber ng LDAP:',
        'ldap_could_not_bind' => 'Hindi makapah-bind sa serber ng LDAP. Mangyaring suriin ang iyong konpigurasyon ng serber ng LDAP sa LDAP config file. <br>may error mula sa Serber ng LDAP: 
 ',
        'ldap_could_not_search' => 'Hindi makapaghanap ng serber ng LDAP. Mangyaring suriin ang iyong konpigurasyon ng serber ng LDAP sa LDAP config file. <br>may error mula sa Serber ng LDAP:',
        'ldap_could_not_get_entries' => 'Hindi makakuha ng entry mula sa serber ng LDAP. Mangyaring surrin ang iyong konpigurasyon ng serber ng LDAP sa LDAP config file. <br>May-error mula sa Serber ng LDAP:',
        'password_ldap' => 'Ang password sa account na ito ay pinamahalaan ng LDAP/Actibong Direktorya. Mangyaring komontak sa iyong IT department para baguhin ang iyong password. ',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Ang file ay hindi nai-delete. Mangyaring subukang muli.',
        'success' => 'Ang file ay matagumpay nang nai-delete.',
    ),

    'upload' => array(
        'error'   => 'Ang file(s) ay hindi nai-upload. Mangyaring subukang muli.',
        'success' => 'Ang file(s) ay matagumpay na nai-upload.',
        'nofiles' => 'Hindi ka pumili ng kahit anong mga file para i-upload',
        'invalidfiles' => 'Ang isa o higit sa iyong mga file ay masyadong malaki o isang uri ng file na hindi pinapayagan. Ang mga pinapayagang mga file ay ang png, gif, jpg, doc, docx, pdf, at txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);