<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Dina användare har uppdaterats, men ansvarigfältet sparades inte eftersom den ansvarige du valt även finns i användarlistan varvid en användare inte kan ange sig själv som ansvarig. Vänligen välj dina användare igen, med den ansvarige exkluderad ur valen.',
    'user_exists'               => 'Användaren existerar redan!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Användaren finns inte eller så har du inte behörighet att se den.',
    'user_login_required'       => 'Inloggningsfältet krävs',
    'user_has_no_assets_assigned' => 'Inga tillgångar har tilldelats denna användare.',
    'user_password_required'    => 'Lösenordet krävs.',
    'insufficient_permissions'  => 'Otillräckliga behörigheter.',
    'user_deleted_warning'      => 'Den här användaren har raderats. Du måste återställa den här användaren för att redigera eller tilldela nya tillgångar.',
    'ldap_not_configured'        => 'LDAP-integrationen har inte konfigurerats för den här uppsättningen.',
    'password_resets_sent'      => 'De valda användarna som är aktiverade och har en giltig e-postadress har skickats en länk för lösenordsåterställning.',
    'password_reset_sent'       => 'En återställningslänk för lösenord har skickats till :email!',
    'user_has_no_email'         => 'Den här användaren har ingen e-postadress i sin profil.',
    'log_record_not_found'        => 'Det gick inte att hitta en matchande logg för den här användaren.',


    'success' => array(
        'create'    => 'Användare skapad.',
        'update'    => 'Användare uppdaterad.',
        'update_bulk'    => 'Användare uppdaterade.',
        'delete'    => 'Användaren har tagits bort.',
        'ban'       => 'Användare avstängd.',
        'unban'     => 'Användare aktiverad.',
        'suspend'   => 'Användare suspenderad.',
        'unsuspend' => 'Användare aktiverad.',
        'restored'  => 'Användare återställd.',
        'import'    => 'Användare importerades.',
    ),

    'error' => array(
        'create' => 'Det gick inte att skapa användaren. Var god försök igen.',
        'update' => 'Det gick inte att uppdatera användaren. Var god försök igen.',
        'delete' => 'Det gick inte att ta bort användaren. Var god försök igen.',
        'delete_has_assets' => 'Den här användaren har objekt tilldelade och kunde inte raderas.',
        'delete_has_assets_var' => 'Den här användaren har fortfarande en tilldelad tillgång. Vänligen checka in den först.|Den här användaren har fortfarande :count tillgångar tilldelade. Vänligen check in dem först.',
        'delete_has_licenses_var' => 'Den här användaren har en licensplats tilldelad. Vänligen checka in den först.|Den här användaren har fortfarande :count licensplatser tilldelade. Vänligen checka in dem först.',
        'delete_has_accessories_var' => 'Den här användaren har fortfarande ett tillbehör tilldelat. Vänligen checka in det först.|Den här användaren har fortfarande :count tillbehör tilldelade. Vänligen checka in dem först.',
        'delete_has_locations_var' => 'Den här användaren hanterar fortfarande en plats. Välj en annan ansvarig först.|Den här användaren hanterar fortfarande :count platser. Välj en annan ansvarig först.',
        'delete_has_users_var' => 'Den här användaren hanterar fortfarande en annan användare. Välj en annan ansvarig för den användaren först. Den här användaren hanterar fortfarande :count användare. Välj en annan ansvarig för dem först.',
        'unsuspend' => 'Det gick inte att aktivera användaren. Var god försök igen.',
        'import'    => 'Det gick inte att importera användare. Var god försök igen.',
        'asset_already_accepted' => 'Denna tillgång har redan godkänts.',
        'accept_or_decline' => 'Du måste antingen godkänna eller avböja den här tillgången.',
        'cannot_delete_yourself' => 'Vi skulle verkligen bli ledsna om du raderade ditt konto. Hoppas att du kan tänka om.',
        'incorrect_user_accepted' => 'Den tillgång du försökte acceptera har inte checkats ut till dig.',
        'ldap_could_not_connect' => 'Det gick inte att ansluta till LDAP-servern. Kontrollera din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Fel från LDAP-servern:',
        'ldap_could_not_bind' => 'Kunde inte ansluta till LDAP-servern. Kontrollera din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Fel från LDAP-servern: ',
        'ldap_could_not_search' => 'Det gick inte att söka på LDAP-servern. Kontrollera din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Fel från LDAP-servern:',
        'ldap_could_not_get_entries' => 'Det gick inte att erhålla värden från LDAP-servern. Kontrollera din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Fel från LDAP-servern:',
        'password_ldap' => 'Lösenordet för det här kontot hanteras av LDAP/Active Directory. Vänligen kontakta din IT-ansvarige för att ändra ditt lösenord. ',
        'multi_company_items_assigned' => 'Denna användare har objekt tilldelade som tillhör ett annat företag. Vänligen checka in dem eller redigera deras företag.'
    ),

    'deletefile' => array(
        'error'   => 'Filen har inte tagits bort. Var god försök igen.',
        'success' => 'Filen har tagits bort.',
    ),

    'upload' => array(
        'error'   => 'Fil(er) har inte laddats upp. Var god försök igen.',
        'success' => 'Fil(er) uppladdad(e).',
        'nofiles' => 'Inga filer valda för uppladdning.',
        'invalidfiles' => 'En eller flera av dina filer är för stora eller är av en filtyp som inte stöds. Tillåtna filtyper är png, gif, jpg, doc, docx, pdf och txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Den här användaren har ingen e-postadress.',
        'success' => 'Användaren har meddelats om sitt nuvarande inventarie.'
    )
);