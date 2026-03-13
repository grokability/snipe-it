<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Jūsu lietotāji ir veiksmīgi atjaunināti, taču jūsu pārvaldnieka ieraksts netika saglabāts, jo izvēlētā pārvaldnieks bija arī rediģējamo lietotāju sarakstā, un lietotāji, iespējams, nav viņu īpašnieks. Lūdzu, vēlreiz atlasiet savus lietotājus, izņemot pārvaldnieku.',
    'user_exists'               => 'Lietotājs jau eksistē!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Ievades lauks ir nepieciešams',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'Parole ir nepieciešama.',
    'insufficient_permissions'  => 'Nepietiekamas atļaujas.',
    'user_deleted_warning'      => 'Šis lietotājs ir izdzēsts. Jums būs jāatjauno šis lietotājs, lai tos rediģētu vai piešķirtu tiem jaunus aktīvus.',
    'ldap_not_configured'        => 'LDAP integrācija nav konfigurēta šai instalācijai.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Lietotājs tika veiksmīgi izveidots.',
        'update'    => 'Lietotājs tika veiksmīgi atjaunināts.',
        'update_bulk'    => 'Lietotāji tika veiksmīgi atjaunināti!',
        'delete'    => 'Lietotājs tika veiksmīgi izdzēsts.',
        'ban'       => 'Lietotājs tika veiksmīgi aizliegts.',
        'unban'     => 'Lietotājs tika veiksmīgi aizliegts.',
        'suspend'   => 'Lietotājs tika veiksmīgi apturēts.',
        'unsuspend' => 'Lietotājs tika veiksmīgi atcelts.',
        'restored'  => 'Lietotājs tika veiksmīgi atjaunots.',
        'import'    => 'Lietotāji veiksmīgi importēti.',
    ),

    'error' => array(
        'create' => 'Radās problēma, izveidojot lietotāju. Lūdzu mēģiniet vēlreiz.',
        'update' => 'Radās problēma, atjauninot lietotāju. Lūdzu mēģiniet vēlreiz.',
        'delete' => 'Radās problēma, izdzēšot lietotāju. Lūdzu mēģiniet vēlreiz.',
        'delete_has_assets' => 'Šim lietotājam ir piešķirti priekšmeti un to nevarēja dzēst.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Nebija saistīta problēma, kas saistītu ar lietotāju. Lūdzu mēģiniet vēlreiz.',
        'import'    => 'Bija problēma importēt lietotājus. Lūdzu mēģiniet vēlreiz.',
        'asset_already_accepted' => 'Šis aktīvs jau ir pieņemts.',
        'accept_or_decline' => 'Jums ir vai nu jāpieņem vai jāatsakās no šī īpašuma.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Aktīvs, kuru jūs mēģinājāt pieņemt, netika izrakstīts jums.',
        'ldap_could_not_connect' => 'Nevarēja izveidot savienojumu ar LDAP serveri. Lūdzu, pārbaudiet LDAP servera konfigurāciju LDAP konfigurācijas failā. <br>Par LDAP servera kļūda:',
        'ldap_could_not_bind' => 'Nevarēja saistīties ar LDAP serveri. Lūdzu, pārbaudiet LDAP servera konfigurāciju LDAP konfigurācijas failā. <br>Par LDAP servera kļūda:',
        'ldap_could_not_search' => 'Nevarēja meklēt LDAP serverī. Lūdzu, pārbaudiet LDAP servera konfigurāciju LDAP konfigurācijas failā. <br>Par LDAP servera kļūda:',
        'ldap_could_not_get_entries' => 'Nevarēja iegūt ierakstus no LDAP servera. Lūdzu, pārbaudiet LDAP servera konfigurāciju LDAP konfigurācijas failā. <br>Par LDAP servera kļūda:',
        'password_ldap' => 'Šī konta paroli pārvalda LDAP / Active Directory. Lai mainītu savu paroli, lūdzu, sazinieties ar IT nodaļu.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Fails nav izdzēsts. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Fails veiksmīgi izdzēsts.',
    ),

    'upload' => array(
        'error'   => 'Faili nav augšupielādēti. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Faili (-i) ir veiksmīgi augšupielādēti.',
        'nofiles' => 'Jūs neesat atlasījis augšupielādes failus',
        'invalidfiles' => 'Viens vai vairāki jūsu faili ir pārāk lieli vai nav atļauto faila tipu. Atļautie failu tipi ir png, gif, jpg, doc, docx, pdf un txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);