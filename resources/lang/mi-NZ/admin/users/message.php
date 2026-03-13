<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Kua angitu te whakahoutia o nga kaiwhakamahi, heoi kihai i tohua to tautuhinga kaiwhakahaere no te mea ko te kaiwhakahaere i tohua e koe i roto i te rarangi kaiwhakamahi kia whakatikaia, kaore ano hoki nga kaiwhakamahi i to ratou ake kaiwhakahaere. Tēnā koa tīpako anō i ō kaiwhakamahi, kaore i te kaiwhakahaere.',
    'user_exists'               => 'Kua noho kē te Kaiwhakamahi!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Kei te hiahiatia te mara takiuru',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Kei te hiahiatia te kupuhipa.',
    'insufficient_permissions'  => 'Nga Whakaae Korero.',
    'user_deleted_warning'      => 'Kua mukua tenei kaiwhakamahi. Me whakahou e koe tenei kaiwhakamahi ki te whakatika i aua mea, ki te tuku ranei i nga taonga hou.',
    'ldap_not_configured'        => 'Ko te whakaurutanga LDAP kua kore i whirihorahia mo tenei whakauru.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'I hanga angitu te Kaiwhakamahi.',
        'update'    => 'I whakahoutia te kaiwhakamahi.',
        'update_bulk'    => 'Kua pai te whakahou o nga kaiwhakamahi!',
        'delete'    => 'Kua mukua te Kaiwhakamahi.',
        'ban'       => 'I whakatinanahia te Kaiwhakamahi.',
        'unban'     => 'I pai te whakakorea o te Kaiwhakamahi.',
        'suspend'   => 'Kua tohua te kaiwhakamahi.',
        'unsuspend' => 'I tino angitu te Kaiwhakamahi.',
        'restored'  => 'I angitu te ora o te Kaiwhakamahi.',
        'import'    => 'He pai te kawemai o nga kaiwhakamahi.',
    ),

    'error' => array(
        'create' => 'He raruraru kei te hanga i te kaiwhakamahi. Tena ngana ano.',
        'update' => 'He raru kei te whakahou i te kaiwhakamahi. Tena ngana ano.',
        'delete' => 'He raru kei te whakakore i te kaiwhakamahi. Tena ngana ano.',
        'delete_has_assets' => 'Kei a tenei kaiwhakamahi nga mea kua tohua me te kore e taea te muku.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'He raruraru kaore i te whakautu i te kaiwhakamahi. Tena ngana ano.',
        'import'    => 'He raruraru e kawemai ana i nga kaiwhakamahi. Tena ngana ano.',
        'asset_already_accepted' => 'Kua whakaaetia tenei taonga.',
        'accept_or_decline' => 'Me whakaae ranei koe ki te whakakore i tenei taonga.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Ko te taonga i whaia e koe ki te whakaae kihai i tukuna ki a koe.',
        'ldap_could_not_connect' => 'Kāore i taea te hono atu ki te tūmau LDAP. Titiro koa ki te whirihoranga o tō tūmau LDAP i te kōnae whirihora LDAP. <br>Error mai i te Tūmau LDAP:',
        'ldap_could_not_bind' => 'Kāore i taea te here ki te tūmau LDAP. Titiro koa ki te whirihoranga o tō tūmau LDAP i te kōnae whirihora LDAP. <br>Error mai i te Tūmau LDAP:',
        'ldap_could_not_search' => 'Kāore i taea te rapu i te tūmau LDAP. Titiro koa ki te whirihoranga o tō tūmau LDAP i te kōnae whirihora LDAP. <br>Error mai i te Tūmau LDAP:',
        'ldap_could_not_get_entries' => 'Kāore i taea te tiki tuhinga mai i te tūmau LDAP. Titiro koa ki te whirihoranga o tō tūmau LDAP i te kōnae whirihora LDAP. <br>Error mai i te Tūmau LDAP:',
        'password_ldap' => 'Ko te kupuhipa mo tenei kaute kei te whakahaeretia e LDAP / Active Directory. Tēnā whakapā atu ki tō tari IT hei huri i tō kupuhipa.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Kāore te kōnae i mukua. Tena ngana ano.',
        'success' => 'Kua mukua te kōnae.',
    ),

    'upload' => array(
        'error'   => 'Ko nga kōnae kāore i tukuna. Tena ngana ano.',
        'success' => 'Ko te (ngā) kōnae i tukuna paihia.',
        'nofiles' => 'Kāore i tīpakohia e koe tetahi kōnae hei tuku ake',
        'invalidfiles' => 'Kotahi, nui atu ranei o ou kōnae he nui rawa atu, he waaahi ranei e kore e whakaaetia. Ko nga kōnae e whakaaetia ana he png, gif, jpg, doc, docx, pdf, me te txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);