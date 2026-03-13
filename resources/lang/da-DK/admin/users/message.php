<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Dine brugere er blevet opdateret, men din administratorindgang blev ikke gemt, fordi den valgte leder også var på brugerlisten, der skulle redigeres, og brugerne er måske ikke deres egen administrator. Vælg venligst dine brugere igen, undtagen manager.',
    'user_exists'               => 'Bruger eksistere allerede!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Brugeren findes ikke, eller du har ikke tilladelse til at se dem.',
    'user_login_required'       => 'Login-feltet er påkrævet',
    'user_has_no_assets_assigned' => 'Ingen aktiver i øjeblikket tildelt brugeren.',
    'user_password_required'    => 'Adgangskoden er påkrævet.',
    'insufficient_permissions'  => 'Utilstrækkelige tilladelser.',
    'user_deleted_warning'      => 'Denne bruger er blevet slettet. Du skal gendanne denne bruger for at redigere dem eller tildele dem nye aktiver.',
    'ldap_not_configured'        => 'LDAP-integration er ikke konfigureret til denne installation.',
    'password_resets_sent'      => 'De valgte brugere, der er aktiveret og har en gyldig e-mail-adresser, har fået tilsendt et link til nulstilling af adgangskode.',
    'password_reset_sent'       => 'Et link til nulstilling af adgangskode er blevet sendt til :email!',
    'user_has_no_email'         => 'Denne bruger har ikke en email-adresse i deres profil.',
    'log_record_not_found'        => 'En matchende log post for denne bruger kunne ikke findes.',


    'success' => array(
        'create'    => 'Bruger blev oprettet.',
        'update'    => 'Bruger blev opdateret.',
        'update_bulk'    => 'Brugere blev opdateret!',
        'delete'    => 'Bruger blev slettet korrekt.',
        'ban'       => 'Bruger blev forbudt.',
        'unban'     => 'Brugeren blev ubemærket.',
        'suspend'   => 'Bruger blev suspenderet.',
        'unsuspend' => 'Bruger blev succesløst afbrudt.',
        'restored'  => 'Bruger blev genoprettet.',
        'import'    => 'Brugere importeres med succes.',
    ),

    'error' => array(
        'create' => 'Der opstod et problem, der skabte brugeren. Prøv igen.',
        'update' => 'Der opstod et problem, der opdaterede brugeren. Prøv igen.',
        'delete' => 'Der opstod et problem ved at slette brugeren. Prøv igen.',
        'delete_has_assets' => 'Denne bruger har elementer tildelt og kunne ikke slettes.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Der opstod et problem, der afbrudte brugeren. Prøv igen.',
        'import'    => 'Der var et problem, der importerede brugere. Prøv igen.',
        'asset_already_accepted' => 'Dette aktiv er allerede accepteret.',
        'accept_or_decline' => 'Du skal enten acceptere eller afvise dette aktiv.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Det aktiv, du har forsøgt at acceptere, blev ikke tjekket ud til dig.',
        'ldap_could_not_connect' => 'Kunne ikke oprette forbindelse til LDAP-serveren. Tjek venligst din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Error fra LDAP-server:',
        'ldap_could_not_bind' => 'Kunne ikke binde til LDAP-serveren. Tjek venligst din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Error fra LDAP-server:',
        'ldap_could_not_search' => 'Kunne ikke søge på LDAP-serveren. Tjek venligst din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Error fra LDAP-server:',
        'ldap_could_not_get_entries' => 'Kunne ikke få poster fra LDAP-serveren. Tjek venligst din LDAP-serverkonfiguration i LDAP-konfigurationsfilen. <br>Error fra LDAP-server:',
        'password_ldap' => 'Adgangskoden til denne konto administreres af LDAP / Active Directory. Kontakt din it-afdeling for at ændre dit kodeord.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Filen er ikke slettet. Prøv igen.',
        'success' => 'Filen er slettet korrekt.',
    ),

    'upload' => array(
        'error'   => 'Fil (er) ikke uploadet. Prøv igen.',
        'success' => 'Fil (er), der blev uploadet korrekt.',
        'nofiles' => 'Du valgte ikke nogen filer til upload',
        'invalidfiles' => 'En eller flere af dine filer er for store eller er en filtype, der ikke er tilladt. Tilladte filtyper er png, gif, jpg, doc, docx, pdf og txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Denne bruger har ikke indsat en email.',
        'success' => 'Brugeren er blevet underrettet om deres aktuelle beholdning.'
    )
);