<?php

return array(

    'accepted'                  => 'Uspješno ste prihvatili ovaj resurs.',
    'declined'                  => 'Uspješno ste odbili ovaj resurs.',
    'bulk_manager_warn'	        => 'Your users have been successfully updated, however your manager entry was not saved because the manager you selected was also in the user list to be edited, and users may not be their own manager. Please select your users again, excluding the manager.',
    'user_exists'               => 'Korisnik već postoji!',
    'user_not_found'            => 'Korisnik ne postoji ili vi nemate ovlašćenja da ih vidite.',
    'user_login_required'       => 'Polje za prijavu je obavezno',
    'user_has_no_assets_assigned' => 'Trenutno nema imovine zadužene korisniku.',
    'user_password_required'    => 'Lozinka je obavezna.',
    'insufficient_permissions'  => 'Insufficient Permissions.',
    'user_deleted_warning'      => 'Ovaj je korisnik izbrisan. Morate vratiti tog korisnika da biste ga uredili ili mu dodeliti novi resurs.',
    'ldap_not_configured'        => 'LDAP integracija nije konfigurisana za ovu instalaciju.',
    'password_resets_sent'      => 'Odabranim korisnicima koji su aktivirani i imaju važeće adrese e-pošte poslat je link za resetovanje lozinke.',
    'password_reset_sent'       => 'Link za resetovanje lozinke je poslat na :email!',
    'user_has_no_email'         => 'Ovaj korisnik nema adresu e-pošte u svom profilu.',
    'log_record_not_found'        => 'Nije bilo moguće pronaći odgovarajući zapis u dnevniku za ovog korisnika.',


    'success' => array(
        'create'    => 'Korisnik je uspešno kreiran.',
        'update'    => 'Korisnik je uspešno ažuriran.',
        'update_bulk'    => 'Korisnici su uspešno ažurirani!',
        'delete'    => 'Korisnik je uspešno izbrisan.',
        'ban'       => 'Korisnik je uspešno zabranjen.',
        'unban'     => 'Korisnik je uspešno unbanned.',
        'suspend'   => 'Korisnik je uspješno suspendovan.',
        'unsuspend' => 'User was successfully unsuspended.',
        'restored'  => 'Korisnik je uspešno obnovljen.',
        'import'    => 'Korisnici su uspješno importovani.',
    ),

    'error' => array(
        'create' => 'Pojavio se problem pri kreiranju korisnika. Molim pokušajte ponovo.',
        'update' => 'Došlo je do problema s ažuriranjem korisnika. Molim pokušajte ponovo.',
        'delete' => 'Došlo je do problema s brisanjem korisnika. Molim pokušajte ponovo.',
        'delete_has_assets' => 'Ovaj korisnik ima dodeljene stavke i ne može biti obrisan.',
        'delete_has_assets_var' => 'Ovaj korisnik još uvek ima zaduženu imovinu. Molim vas prvo je razdužite.|Ovaj korisnik još uvek ima :count imovine zadužene. Molim vas prvo ih razdužite.',
        'delete_has_licenses_var' => 'Ovaj korisnik još uvek ima zaduženu licencu. Molim vas prvo je razdužite.|Ovaj korisnik još uvek ima :count licence zadužene. Molim vas prvo ih razdužite.',
        'delete_has_accessories_var' => 'Ovaj korisnik još uvek ima zaduženu dodatnu opremu. Molim vas prvo je razdužite.|Ovaj korisnik još uvek ima :count zadužene dodatne opreme. Molim vas prvo ih razdužite.',
        'delete_has_locations_var' => 'Ovaj korisnik još uvek upravlja lokacijom. Molim vas prvo izaberite drugog rukovodioca.|Ovaj korisnik još uvek upravlja :count lokacijama. Molim vas prvo izaberite drugog rukovodioca.',
        'delete_has_users_var' => 'Ovaj korisnik još uvek upravlja drugim korisnikom. Molim vas prvo izaberite drugog rukovodioca tom korisniku.|Ovaj korisnik još uvek upravlja :count korisnicima. Molim vas prvo im izaberite drugog rukovodioca.',
        'unsuspend' => 'There was an issue unsuspending the user. Please try again.',
        'import'    => 'Došlo je do problema s importom korisnika. Molim pokušajte ponovo.',
        'asset_already_accepted' => 'Ova je imovina već prihvaćena.',
        'accept_or_decline' => 'Morate prihvatiti ili odbaciti ovaj resurs, imovinu.',
        'cannot_delete_yourself' => 'Osećali bi smo se veoma loše kada bi ste obrisali samog sebe. Razmotrite šta pokušavate.',
        'incorrect_user_accepted' => 'The asset you have attempted to accept was not checked out to you.',
        'ldap_could_not_connect' => 'Povezivanje s LDAP serverom nije uspelo. Proverite konfiguraciju LDAP servera u LDAP konfig datoteci. <br>Greška sa LDAP servera:',
        'ldap_could_not_bind' => 'Nije moguće povezati se sa LDAP serverom. Provjerite konfiguraciju LDAP servera. <br>Greška sa LDAP servera: ',
        'ldap_could_not_search' => 'Nije moguće pretražiti LDAP server. Proverite konfiguraciju LDAP servera. <br>Greška sa LDAP servera:',
        'ldap_could_not_get_entries' => 'Nije bilo moguće dobiti zapise sa LDAP servera. Proverite konfiguraciju LDAP servera. <br>Greška sa LDAP servera:',
        'password_ldap' => 'Lozinku za ovaj nalog kontroliše LDAP / Active Directory. Obratite se IT centru za promenu lozinke. ',
        'multi_company_items_assigned' => 'Ovaj korisnik poseduje zadužene stavke koje pripadaju drugoj kompaniji. Molim vas razdužite ih ili promenite njihovu kompaniju.'
    ),

    'deletefile' => array(
        'error'   => 'Datoteka nije izbrisana. Molim pokušajte ponovo.',
        'success' => 'Datoteka je uspešno obrisana.',
    ),

    'upload' => array(
        'error'   => 'Datoteke nisu prenesene. Molim pokušajte ponovo.',
        'success' => 'Datoteke su uspješno učitane.',
        'nofiles' => 'Niste odabrali nijednu datoteku za prenos',
        'invalidfiles' => 'Jedna ili više datoteka je prevelika ili je vrsta datoteke koja nije dopuštena. Dopuštene vrste datoteka su png, gif, jpg, doc, docx, pdf i txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Korisnik nema navedenu adresu e-pošte.',
        'success' => 'Ovaj korisnik je obavešten o njegovom trenutnom inventaru.'
    )
);