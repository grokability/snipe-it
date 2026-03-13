<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Jou gebruikers is suksesvol opgedateer, maar jou bestuurderinskrywing is nie gestoor nie, want die bestuurder wat jy gekies het, was ook in die gebruikerslys om geredigeer te word, en gebruikers mag nie hul eie bestuurder wees nie. Kies asseblief u gebruikers weer, behalwe die bestuurder.',
    'user_exists'               => 'Gebruiker bestaan ​​reeds!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Die aanmeldingsveld is nodig',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Die wagwoord is nodig.',
    'insufficient_permissions'  => 'Onvoldoende Toestemmings.',
    'user_deleted_warning'      => 'Hierdie gebruiker is verwyder. Jy sal hierdie gebruiker moet herstel om hulle te wysig of nuwe bates toe te ken.',
    'ldap_not_configured'        => 'LDAP-integrasie is nie vir hierdie installasie gekonfigureer nie.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Gebruiker is suksesvol geskep.',
        'update'    => 'Gebruiker is suksesvol opgedateer.',
        'update_bulk'    => 'Gebruikers is suksesvol opgedateer!',
        'delete'    => 'Gebruiker is suksesvol verwyder.',
        'ban'       => 'Gebruiker is suksesvol verban.',
        'unban'     => 'Gebruiker is suksesvol geblokkeer.',
        'suspend'   => 'Gebruiker is suksesvol opgeskort.',
        'unsuspend' => 'Gebruiker is suksesvol ingetrek.',
        'restored'  => 'Gebruiker is suksesvol herstel.',
        'import'    => 'Gebruikers suksesvol ingevoer.',
    ),

    'error' => array(
        'create' => 'Kon nie die gebruiker skep nie. Probeer asseblief weer.',
        'update' => 'Kon nie die gebruiker opdateer nie. Probeer asseblief weer.',
        'delete' => 'Daar was \'n probleem met die verwydering van die gebruiker. Probeer asseblief weer.',
        'delete_has_assets' => 'Hierdie gebruiker het items toegeken en kon nie uitgevee word nie.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Daar was \'n probleem wat die gebruiker onttrek het. Probeer asseblief weer.',
        'import'    => 'Kon nie gebruikers invoer nie. Probeer asseblief weer.',
        'asset_already_accepted' => 'Hierdie bate is reeds aanvaar.',
        'accept_or_decline' => 'U moet hierdie bate aanvaar of afkeur.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Die bate wat u probeer aanvaar het, is nie na u gekontroleer nie.',
        'ldap_could_not_connect' => 'Kon nie aan die LDAP-bediener koppel nie. Gaan asseblief die LDAP-bediener opstelling in die LDAP-konfigurasie lêer. <br>Error van LDAP-bediener:',
        'ldap_could_not_bind' => 'Kon nie aan die LDAP-bediener bind nie. Gaan asseblief die LDAP-bediener opstelling in die LDAP-konfigurasie lêer. <br>Error van LDAP-bediener:',
        'ldap_could_not_search' => 'Kon nie die LDAP-bediener soek nie. Gaan asseblief die LDAP-bediener opstelling in die LDAP-konfigurasie lêer. <br>Error van LDAP-bediener:',
        'ldap_could_not_get_entries' => 'Kon nie inskrywings van die LDAP-bediener kry nie. Gaan asseblief die LDAP-bediener opstelling in die LDAP-konfigurasie lêer. <br>Error van LDAP-bediener:',
        'password_ldap' => 'Die wagwoord vir hierdie rekening word bestuur deur LDAP / Active Directory. Kontak asseblief u IT-afdeling om u wagwoord te verander.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Lêer nie verwyder nie. Probeer asseblief weer.',
        'success' => 'Lêer suksesvol uitgevee.',
    ),

    'upload' => array(
        'error'   => 'Lêer (s) nie opgelaai nie. Probeer asseblief weer.',
        'success' => 'Lêer (s) suksesvol opgelaai.',
        'nofiles' => 'Jy het nie enige lêers vir oplaai gekies nie',
        'invalidfiles' => 'Een of meer van jou lêers is te groot of is \'n filetipe wat nie toegelaat word nie. Toegelate filetipes is png, gif, jpg, doc, docx, pdf en txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);