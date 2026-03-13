<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Utilizatorii dvs. au fost actualizați cu succes, cu toate acestea, intrarea managerului dvs. nu a fost salvată, deoarece managerul pe care l-ați selectat a fost, de asemenea, în lista de utilizatori care urmează să fie editat și este posibil ca utilizatorii să nu fie propriul manager. Selectați din nou utilizatorii dvs., cu excepția managerului.',
    'user_exists'               => 'Utilizatorul exista deja!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Utilizatorul nu există sau nu aveți permisiunea de a le vizualiza.',
    'user_login_required'       => 'Campul de login este necesar',
    'user_has_no_assets_assigned' => 'Nici un activ alocat utilizatorului în prezent.',
    'user_password_required'    => 'Este necesara parola.',
    'insufficient_permissions'  => 'Nu aveti permisiuni suficiente.',
    'user_deleted_warning'      => 'Acest utilizator a fost sters. Trebuie sa restaurati utilizator ca sa-l editati sau sa-i desemnati active noi.',
    'ldap_not_configured'        => 'Integrarea LDAP nu a fost configurată pentru această instalare.',
    'password_resets_sent'      => 'Utilizatorii selectați care sunt activați și au o adresă de e-mail validă au primit un link de resetare a parolei.',
    'password_reset_sent'       => 'Un link de resetare a parolei a fost trimis la :email!',
    'user_has_no_email'         => 'Acest utilizator nu are o adresă de e-mail în profilul său.',
    'log_record_not_found'        => 'Nu s-a putut găsi o înregistrare de identificare pentru acest utilizator.',


    'success' => array(
        'create'    => 'Utilizatorul a fost creat.',
        'update'    => 'Utilizatorul a fost actualizat.',
        'update_bulk'    => 'Utilizatorii au fost actualizați cu succes!',
        'delete'    => 'Utilizatorul a fost sters.',
        'ban'       => 'Utilizatorul a fost banat.',
        'unban'     => 'Utilizatorul a fost debanat.',
        'suspend'   => 'Utilizatorul a fost suspendat.',
        'unsuspend' => 'Utilizatorul a fost activat.',
        'restored'  => 'Utilizatorul a fost restaurat.',
        'import'    => 'Utilizatorii importați cu succes.',
    ),

    'error' => array(
        'create' => 'A aparut o problema la crearea utilizatorului. Incercati iar.',
        'update' => 'A aparut o problema la actualizarea utilizatorului. Incercati iar.',
        'delete' => 'A aparut o problema la stergerea utilizatorului. Incercati iar.',
        'delete_has_assets' => 'Acest utilizator are elemente atribuite și nu a putut fi șters.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'A aparut o problema la reactivarea utilizatorului. Incercati iar.',
        'import'    => 'A apărut o problemă la importarea utilizatorilor. Vă rugăm să încercați din nou.',
        'asset_already_accepted' => 'Acest activ a fost deja acceptat.',
        'accept_or_decline' => 'Trebuie să acceptați sau să refuzați acest activ.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Activitatea pe care ați încercat să o acceptați nu a fost verificată de dvs.',
        'ldap_could_not_connect' => 'Nu s-a putut conecta la serverul LDAP. Verificați configurația serverului LDAP în fișierul de configurare LDAP. <br>Error de la LDAP Server:',
        'ldap_could_not_bind' => 'Nu s-a putut lega la serverul LDAP. Verificați configurația serverului LDAP în fișierul de configurare LDAP. <br>Error de la LDAP Server:',
        'ldap_could_not_search' => 'Căutarea serverului LDAP nu a putut fi efectuată. Verificați configurația serverului LDAP în fișierul de configurare LDAP. <br>Error de la LDAP Server:',
        'ldap_could_not_get_entries' => 'Nu s-au putut obține intrări de pe serverul LDAP. Verificați configurația serverului LDAP în fișierul de configurare LDAP. <br>Error de la LDAP Server:',
        'password_ldap' => 'Parola pentru acest cont este gestionată de LDAP / Active Directory. Contactați departamentul IT pentru a vă schimba parola.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Fișierul nu a fost șters. Vă rugăm să încercați din nou.',
        'success' => 'Fișierul a fost șters cu succes.',
    ),

    'upload' => array(
        'error'   => 'Fișierul nu a fost încărcat. Vă rugăm să încercați din nou.',
        'success' => 'Fișierul a fost încărcat cu succes.',
        'nofiles' => 'Nu ați selectat niciun fișier pentru încărcare',
        'invalidfiles' => 'Unul sau mai multe fișiere este prea mare sau este un tip de fișier care nu este permis. Tipurile de fișiere permise sunt png, gif, jpg, doc, docx, pdf și txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Acest utilizator nu are nici un set de e-mail.',
        'success' => 'Utilizatorul a fost notificat despre inventarul său curent.'
    )
);