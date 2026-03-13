<?php

return array(

    'accepted'                  => 'Je hebt het item succesvol geaccepteerd.',
    'declined'                  => 'Je hebt dit item succesvol afgewezen.',
    'bulk_manager_warn'	        => 'Uw gebruikers zijn succesvol bijgewerkt, de gekozen manager kon echter niet toegepast worden omdat deze persoon ook in de lijst staat, gebruikers mogen niet hun eigen manager zijn. Probeer het nogmaals en selecteer de gebruikers zonder de manager.',
    'user_exists'               => 'Gebruiker bestaat reeds!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Gebruiker bestaat niet of je hebt geen toestemming om deze te bekijken.',
    'user_login_required'       => 'Het veld gebruikersnaam is verplicht.',
    'user_has_no_assets_assigned' => 'Geen assets toegewezen aan de gebruiker.',
    'user_password_required'    => 'Het veld wachtwoord is verplicht.',
    'insufficient_permissions'  => 'Onvoldoende rechten.',
    'user_deleted_warning'      => 'Deze gebruiker is verwijderd. Je zult deze gebruiker moeten herstellen om hem te bewerken of om nieuwe assets toe te wijzen.',
    'ldap_not_configured'        => 'LDAP integratie is niet geconfigureerd voor deze installatie.',
    'password_resets_sent'      => 'De geselecteerde gebruikers die zijn geactiveerd en die een geldig e-mailadres hebben, hebben een wachtwoord reset link ontvangen.',
    'password_reset_sent'       => 'Een link om het wachtwoord te resetten is verstuurd naar :email!',
    'user_has_no_email'         => 'Deze gebruiker heeft geen e-mailadres in zijn profiel.',
    'log_record_not_found'        => 'Een overeenkomende logboekrecord voor deze gebruiker kon niet worden gevonden.',


    'success' => array(
        'create'    => 'Gebruiker succesvol aangemaakt.',
        'update'    => 'Gebruiker succesvol bijgewerkt.',
        'update_bulk'    => 'Gebruikers zijn succesvol bijgewerkt!',
        'delete'    => 'Gebruiker succesvol verwijderd.',
        'ban'       => 'Gebruiker succesvol verbannen.',
        'unban'     => 'Gebruiker succesvol opnieuw toegang verleend.',
        'suspend'   => 'Gebruiker werd succesvol uitgeschakeld.',
        'unsuspend' => 'Gebruiker werd succesvol ingeschakeld.',
        'restored'  => 'Gebruiker werd succesvol opnieuw geactiveerd.',
        'import'    => 'Gebruikers met succes geïmporteerd.',
    ),

    'error' => array(
        'create' => 'Er was een probleem tijdens het aanmaken van de gebruiker. Probeer opnieuw, aub.',
        'update' => 'Er was een probleem tijdens het bijwerken van de gebruiker. Probeer opnieuw, aub.',
        'delete' => 'Er was een probleem tijdens het verwijderen van de gebruiker. Probeer opnieuw, aub.',
        'delete_has_assets' => 'Deze gebruiker heeft toegewezen items en kon niet worden verwijderd.',
        'delete_has_assets_var' => 'Deze gebruiker heeft nog een asset toegewezen. Controleer dit eerst.|Deze gebruiker heeft nog steeds :count assets toegewezen. Controleer dit eerst.',
        'delete_has_licenses_var' => 'Deze gebruiker heeft nog een licentie toegewezen. Controleer dit eerst.|Deze gebruiker heeft nog steeds :count licenties toegewezen. Controleer dit eerst.',
        'delete_has_accessories_var' => 'Deze gebruiker heeft nog een accessoires toegewezen. Controleer dit eerst.|Deze gebruiker heeft nog steeds :count accessoires toegewezen. Controleer dit eerst.',
        'delete_has_locations_var' => 'Deze gebruiker beheert nog een locatie. Selecteer eerst een andere manager.|Deze gebruiker beheert nog steeds :count locaties. Selecteer eerst een andere manager.',
        'delete_has_users_var' => 'Deze gebruiker beheert nog een andere gebruiker. Selecteer eerst een andere manager.|Deze gebruiker beheert nog steeds :count gebruikers. Selecteer eerst een andere manager.',
        'unsuspend' => 'Er was een probleem tijdens het opnieuw inschakelen van de gebruiker. Probeer opnieuw, aub.',
        'import'    => 'Er was een probleem met het importeren van de gebruikers. Probeer het opnieuw.',
        'asset_already_accepted' => 'Dit asset is al geaccepteerd.',
        'accept_or_decline' => 'Je moet dit asset accepteren of weigeren.',
        'cannot_delete_yourself' => 'We zouden het heel erg vinden als je jezelf zou verwijderen, overweeg alsjeblieft opnieuw.',
        'incorrect_user_accepted' => 'Het asset dat je probeerde te accepteren is niet uitgecheckt aan jou.',
        'ldap_could_not_connect' => 'Kan niet verbinden met de LDAP server. Controleer je LDAP server configuratie in de LDAP configuratie bestand. <br>Fout van LDAP server:',
        'ldap_could_not_bind' => 'Kan niet verbinden met de LDAP server. Controleer je LDAP server configuratie in de LDAP configuratie bestand. <br>Fout van LDAP server: ',
        'ldap_could_not_search' => 'Kan niet zoeken in de LDAP server. Controleer je LDAP server configuratie in de LDAP configuratie bestand. <br>Fout van LDAP server:',
        'ldap_could_not_get_entries' => 'Kan geen gegeven van de LDAP server krijgen. Controleer je LDAP server configuratie in de LDAP configuratie bestand. <br>Fout van LDAP server:',
        'password_ldap' => 'Het wachtwoord voor deze account wordt beheerd door LDAP/Active Directory. Neem contact op met uw IT-afdeling om uw wachtwoord te wijzigen. ',
        'multi_company_items_assigned' => 'Deze gebruiker heeft items toegewezen welke tot een andere organisatie behoren. Check deze alstublieft in of bewerk het bedrijf van de gebruiker.'
    ),

    'deletefile' => array(
        'error'   => 'Bestand is niet verwijderd. Probeer het opnieuw.',
        'success' => 'Bestand met succes verwijderd.',
    ),

    'upload' => array(
        'error'   => 'Bestand(en) zijn niet geüpload. Probeer het opnieuw.',
        'success' => 'Bestand(en) zijn met succes geüpload.',
        'nofiles' => 'Je hebt geen bestanden geselecteerd voor de upload',
        'invalidfiles' => 'Een of meer van uw bestanden is te groot of is een bestandstype dat niet is toegestaan. Toegestaande bestandstypen png, gif, jpg, doc, docx, pdf en txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Deze gebruiker heeft geen e-mailadres ingesteld.',
        'success' => 'De gebruiker is op de hoogte gebracht van zijn huidige voorraad.'
    )
);