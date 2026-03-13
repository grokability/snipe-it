<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Brukerne er oppdatert, men lederen ble ikke lagret fordi lederen du valgte også i brukerlisten for redigering og brukere kan ikke være sin egen leder. Velg brukerne igjen, unntatt lederen.',
    'user_exists'               => 'Bruker finnes allerede!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Brukeren finnes ikke, eller du har ikke tillatelse til å se dem.',
    'user_login_required'       => 'Login-feltet er påkrevd',
    'user_has_no_assets_assigned' => 'Ingen eiendeler er tilordnet brukeren for øyeblikket.',
    'user_password_required'    => 'Passord er påkrevd.',
    'insufficient_permissions'  => 'Utilstrekkelige rettigheter.',
    'user_deleted_warning'      => 'Denne brukeren er slettet. Du vil må gjenopprette denne brukeren for å redigere, eller tildele nye eiendeler.',
    'ldap_not_configured'        => 'LDAP integrasjonen er ikke konfigurert i denne installasjonen.',
    'password_resets_sent'      => 'De valgte brukerne som er aktivert og har en gyldig e-postadresse har blitt sendt en tilbakestillingslenke.',
    'password_reset_sent'       => 'En lenke for tilbakestilling av passord har blitt sendt til :email!',
    'user_has_no_email'         => 'Denne brukeren har ingen e-postadresse i sin profil.',
    'log_record_not_found'        => 'Finner ikke et samsvarende loggelement for denne brukeren.',


    'success' => array(
        'create'    => 'Opprettelse av bruker vellykket.',
        'update'    => 'Oppdatering av bruker vellykket.',
        'update_bulk'    => 'Oppdatering av brukere vellykket!',
        'delete'    => 'Sletting av bruker vellykket.',
        'ban'       => 'Vellykket forbud av bruker.',
        'unban'     => 'Forbud av bruker ble opphevet.',
        'suspend'   => 'Vellykket deaktivering av bruker.',
        'unsuspend' => 'Vellykket aktivering av bruker.',
        'restored'  => 'Vellykket gjenopprettelse av bruker.',
        'import'    => 'Vellykket import av brukere.',
    ),

    'error' => array(
        'create' => 'Det oppstod et problem under opprettelse av bruker. Prøv igjen.',
        'update' => 'Det oppstod et problem under oppdatering av bruker. Prøv igjen.',
        'delete' => 'Det oppstod et problem under sletting av bruker. Prøv igjen.',
        'delete_has_assets' => 'Denne brukeren har utstyr tildelt og kan ikke slettes.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Det oppstod et problem under aktivering av bruker. Prøv igjen.',
        'import'    => 'Det oppstod et problem under import av brukere. Prøv igjen.',
        'asset_already_accepted' => 'Denne eiendelen er allerede akseptert.',
        'accept_or_decline' => 'Du må enten akseptere eller avvise denne eiendelen.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Eiendelen du prøvde å akseptere ble ikke sjekket ut til deg.',
        'ldap_could_not_connect' => 'Kunne ikke kople til LDAP-serveren. Sjekk LDAP-innstillingene i konfigurasjonsfilen. <br>Feil fra LDAP-server:',
        'ldap_could_not_bind' => 'Kunne ikke opprette tilkopling til LDAP-server. Sjekk LDAP-innstillingene i konfigurasjonsfilen. <br>Feil fra LDAP-server: ',
        'ldap_could_not_search' => 'Kunne ikke utføre søk på LDAP-serveren. Sjekk LDAP-innstillingene i konfigurasjonsfilen. <br>Feil fra LDAP-server:',
        'ldap_could_not_get_entries' => 'Fikk ingen oppføringer fra LDAP-serveren. Sjekk LDAP-innstillingene i konfigurasjonsfilen. <br>Feil fra LDAP-server:',
        'password_ldap' => 'Passordet for denne kontoen administreres av LDAP/Active Directory. Kontakt IT-avdelingen for å endre passordet. ',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Fil ble ikke slettet. Prøv igjen.',
        'success' => 'Fil ble slettet.',
    ),

    'upload' => array(
        'error'   => 'Fil(er) ble ikke lastet opp. Prøv igjen.',
        'success' => 'Vellykket opplasting av fil(er).',
        'nofiles' => 'Du valgte ingen filer for opplasting',
        'invalidfiles' => 'En eller flere av filene dine er for store eller av en filtype som ikke er tillatt. Tillatte filtyper er png, gif, jpg, doc, docx, pdf og txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Denne brukeren har ingen e-post.',
        'success' => 'Brukeren har blitt varslet om det gjeldende inventaret.'
    )
);