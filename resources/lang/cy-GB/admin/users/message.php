<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Mae eich defnyddwyr wedi diweddaru\'n llwyddiannus ond mae\'r blwch rheolwr heb newid gan fod y rheolwr yn y rhestr o defnyddwyr. Dewisiwch eto heb cynnwys y rheolwr.',
    'user_exists'               => 'Defnyddiwr yn bodoli yn barod!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Mae angen llenwi\'r maes login',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Rhaid gosod cyfrinair.',
    'insufficient_permissions'  => 'Diffyg Hawliau.',
    'user_deleted_warning'      => 'Defnyddiwr wedi\'i dileu. Rhaid adfer y defnyddiwr I newid eu manylion neu clustnodi ased iddynt.',
    'ldap_not_configured'        => 'Nid ywr gosodiadau I dilysu trwy LDAP wedi gosod ar y system.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Wedi llwyddo i greu defnyddiwr.',
        'update'    => 'Wedi llwyddo i diweddaru defnyddiwr.',
        'update_bulk'    => 'Wedi lwyddo i diweddaru defnyddwyr!',
        'delete'    => 'Wedi dileu\'r defnyddiwr llwyddiannus.',
        'ban'       => 'Wedi llwyddo i wahardd defnyddiwr.',
        'unban'     => 'Wedi llwyddo i anwahardd defnyddiwr.',
        'suspend'   => 'Wedi llwyddo i wahardd y defnyddiwr.',
        'unsuspend' => 'Wedi llwyddo i anwahardd defnyddiwr.',
        'restored'  => 'Wedi adfer y defnyddiwr yn llwyddiannus.',
        'import'    => 'Defnyddwyr wedi mewnforio\'n llwyddiannus.',
    ),

    'error' => array(
        'create' => 'Roedd problem wrth ceisio creu\'r defnyddiwr. Ceisiwch eto o. g. y. dd.',
        'update' => 'Roedd problem wrth ceisio diweddaru\'r defnyddiwr. Ceisiwch eto o. g. y. dd.',
        'delete' => 'Roedd problem wrth ceisio dileu\'r defnyddiwr. Ceisiwch eto o. g. y. dd.',
        'delete_has_assets' => 'Offer wedi nodi yn erbyn y defnyddiwr felly heb ei ddileu.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Roedd problem wrth ceisio alluogi\'r defnyddiwr. Ceisiwch eto o. g. y. dd.',
        'import'    => 'Roedd problem wrth ceisio mewnforio defnyddwyr. Ceisiwch eto o. g. y. dd.',
        'asset_already_accepted' => 'Ased wedi\'i dderbyn yn barod.',
        'accept_or_decline' => 'Rhaid i chi unai derbyn neu gwrthod yr ased yma.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Rydych wedi ceisio derbyn ased sydd ddim wedi nodi yn erbyn eich cyfrif.',
        'ldap_could_not_connect' => 'Wedi methu cyylltu trwy LDAP. Gwiriwch eich gosodiadau LDAP. <br>Error from LDAP Server:',
        'ldap_could_not_bind' => 'Wedi methu cysylltu trwy LDAP. Gwiriwch eich gosodiadau LDAP. <br>Error from LDAP Server: ',
        'ldap_could_not_search' => 'Wedi methu cyraedd y server LDAP. Gwiriwch eich gosodiadau LDAP. <br>Error from LDAP Server:',
        'ldap_could_not_get_entries' => 'Wedi methu llwytho data trwy LDAP. Gwiriwch eich gosodiadau LDAP. <br>Error from LDAP Server:',
        'password_ldap' => 'Mae eich cyfrinair wedi\'i rheoli trwy LDAP/Active Directory. Cysylltwch a\'r Adran TGCh i\'w newid. ',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Ffeil heb ei ddileu. Ceisiwch eto o. g. y. dd.',
        'success' => 'Ffeil wedi dileu yn llwyddiannus.',
    ),

    'upload' => array(
        'error'   => 'Ffeil(iau) heb ei uwchlwytho. Ceisiwch eto o. g. y. dd.',
        'success' => 'Ffeil(iau) wedi uwchlwytho yn llwyddiannus.',
        'nofiles' => 'Nid ydych wedi dewis unrhyw ffeiliau i\'w uwchlwytho',
        'invalidfiles' => 'Mae un neu mwy o\'r ffeiliau unai yn rhy fawr neu ddim y math cywir. Derbynir png, gif, fjp, doc, docx, pdf a txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);