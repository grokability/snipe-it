<?php

return array(

    'accepted'            => 'Ai acceptat cu succes acest activ.',
    'declined'            => 'Ai refuzat cu succes acest activ.',
    'bulk_manager_warn'   => 'Utilizatorii tăi au fost actualizați cu succes, însă înregistrarea managerului tău nu a fost salvată deoarece managerul selectat era și în lista de utilizatori de editat, iar utilizatorii nu pot fi proprii manageri. Te rugăm să-ți selectezi din nou utilizatorii, excluzând managerul.',
    'user_exists'         => 'Utilizatorul există deja!',
    'user_not_found'      => 'Utilizatorul nu există sau nu ai permisiunea de a-l vizualiza.',
    'user_login_required' => 'Câmpul de autentificare este obligatoriu.',
    'user_has_no_assets_assigned' => 'Niciun activ nu este alocat în prezent utilizatorului.',
    'user_password_required' => 'Parola este obligatorie.',
    'insufficient_permissions' => 'Permisiuni insuficiente.',
    'user_deleted_warning' => 'Acest utilizator a fost șters. Va trebui să restaurezi acest utilizator pentru a-l edita sau a-i aloca noi active.',
    'ldap_not_configured' => 'Integrarea LDAP nu a fost configurată pentru această instalare.',
    'password_resets_sent' => 'Utilizatorilor selectați, care sunt activați și au adrese de email valide, li s-a trimis un link de resetare a parolei.',
    'password_reset_sent' => 'Un link de resetare a parolei a fost trimis la :email!',
    'user_has_no_email'   => 'Acest utilizator nu are o adresă de email în profilul său.',
    'log_record_not_found' => 'Nu s-a găsit o înregistrare de jurnal corespunzătoare pentru acest utilizator.',


    'success' => array(
        'create'    => 'Utilizatorul a fost creat cu succes.',
        'update'    => 'Utilizatorul a fost actualizat cu succes.',
        'update_bulk' => 'Utilizatorii au fost actualizați cu succes!',
        'delete'    => 'Utilizatorul a fost șters cu succes.',
        'ban'       => 'Utilizatorul a fost interzis cu succes.',
        'unban'     => 'Utilizatorul a fost dezinterzis cu succes.',
        'suspend'   => 'Utilizatorul a fost suspendat cu succes.',
        'unsuspend' => 'Utilizatorul a fost nesuspendat cu succes.',
        'restored'  => 'Utilizatorul a fost restaurat cu succes.',
        'import'    => 'Utilizatorii au fost importați cu succes.',
    ),

    'error' => array(
        'create' => 'A apărut o problemă la crearea utilizatorului. Te rugăm să încerci din nou.',
        'update' => 'A apărut o problemă la actualizarea utilizatorului. Te rugăm să încerci din nou.',
        'delete' => 'A apărut o problemă la ștergerea utilizatorului. Te rugăm să încerci din nou.',
        'delete_has_assets' => 'Acest utilizator are articole alocate și nu a putut fi șters.',
        'delete_has_assets_var' => 'Acest utilizator are încă un activ alocat. Te rugăm să-l înregistrezi mai întâi.|Acest utilizator are încă :count active alocate. Te rugăm să le înregistrezi mai întâi.',
        'delete_has_licenses_var' => 'Acest utilizator are încă un utilizator de licență alocat. Te rugăm să-l înregistrezi mai întâi.|Acest utilizator are încă :count utilizatori de licență alocați. Te rugăm să-i înregistrezi mai întâi.',
        'delete_has_accessories_var' => 'Acest utilizator are încă un accesoriu alocat. Te rugăm să-l înregistrezi mai întâi.|Acest utilizator are încă :count accesorii alocate. Te rugăm să le înregistrezi mai întâi.',
        'delete_has_locations_var' => 'Acest utilizator gestionează încă o locație. Te rugăm să selectezi mai întâi un alt manager.|Acest utilizator gestionează încă :count locații. Te rugăm să selectezi mai întâi un alt manager.',
        'delete_has_users_var' => 'Acest utilizator gestionează încă un alt utilizator. Te rugăm să selectezi mai întâi un alt manager pentru acel utilizator.|Acest utilizator gestionează încă :count utilizatori. Te rugăm să selectezi mai întâi un alt manager pentru ei.',
        'unsuspend' => 'A apărut o problemă la nesuspendarea utilizatorului. Te rugăm să încerci din nou.',
        'import'    => 'A apărut o problemă la importarea utilizatorilor. Te rugăm să încerci din nou.',
        'asset_already_accepted' => 'Acest activ a fost deja acceptat.',
        'accept_or_decline' => 'Trebuie să accepți sau să refuzi acest activ.',
        'cannot_delete_yourself' => 'Ne-ar părea rău dacă te-ai șterge singur, te rugăm să reconsideri.',
        'incorrect_user_accepted' => 'Activul pe care ai încercat să-l accepți nu ți-a fost alocat.',
        'ldap_could_not_connect' => 'Nu s-a putut conecta la serverul LDAP. Te rugăm să verifici configurația serverului LDAP în fișierul de configurare LDAP. <br>Eroare de la serverul LDAP:',
        'ldap_could_not_bind' => 'Nu s-a putut face bind la serverul LDAP. Te rugăm să verifici configurația serverului LDAP în fișierul de configurare LDAP. <br>Eroare de la serverul LDAP: ',
        'ldap_could_not_search' => 'Nu s-a putut căuta pe serverul LDAP. Te rugăm să verifici configurația serverului LDAP în fișierul de configurare LDAP. <br>Eroare de la serverul LDAP:',
        'ldap_could_not_get_entries' => 'Nu s-au putut obține intrări de la serverul LDAP. Te rugăm să verifici configurația serverului LDAP în fișierul de configurare LDAP. <br>Eroare de la serverul LDAP:',
        'password_ldap' => 'Parola pentru acest cont este gestionată de LDAP/Active Directory. Te rugăm să contactezi departamentul IT pentru a-ți schimba parola.',
        'multi_company_items_assigned' => 'Acest utilizator are articole alocate care aparțin unei companii diferite. Te rugăm să le înregistrezi sau să le editezi compania.'
    ),

    'deletefile' => array(
        'error'   => 'Fișierul nu a fost șters. Te rugăm să încerci din nou.',
        'success' => 'Fișierul a fost șters cu succes.',
    ),

    'upload' => array(
        'error'   => 'Fișierul/fișierele nu au fost încărcate. Te rugăm să încerci din nou.',
        'success' => 'Fișierul/fișierele au fost încărcate cu succes.',
        'nofiles' => 'Nu ai selectat niciun fișier pentru încărcare',
        'invalidfiles' => 'Unul sau mai multe dintre fișierele tale sunt prea mari sau au un tip de fișier nepermis. Tipurile de fișiere permise sunt png, gif, jpg, doc, docx, pdf și txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Acest utilizator nu are o adresă de email setată.',
        'success' => 'Utilizatorul a fost notificat despre inventarul său curent.'
    )
);