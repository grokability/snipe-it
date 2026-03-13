<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Vaši su korisnici uspješno ažurirani, ali vaš unos upravitelja nije spremljen jer je upravitelj koji ste odabrali također bio na popisu korisnika koji se uređuje, a korisnici možda nisu vlastiti upravitelj. Ponovno odaberite svoje korisnike, isključujući upravitelja.',
    'user_exists'               => 'Korisnik već postoji!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'Potrebno je polje za prijavu',
    'user_has_no_assets_assigned' => 'Niti jedno sredstvo trenutno nije dodjeljeno korisniku.',
    'user_password_required'    => 'Zaporka je potrebna.',
    'insufficient_permissions'  => 'Nedovoljna dozvola.',
    'user_deleted_warning'      => 'Ovaj je korisnik izbrisan. Morat ćete vratiti tog korisnika da biste ih uredili ili dodijelili novu imovinu.',
    'ldap_not_configured'        => 'LDAP integracija nije konfigurirana za ovu instalaciju.',
    'password_resets_sent'      => 'Označenim korisnicima koji su aktivni i imaju važeće email adrese je poslan link za ponovno postavljanje vjerodajnica.',
    'password_reset_sent'       => 'Link za resetiranje vjerodajnica poslan je na :email!',
    'user_has_no_email'         => 'Ovaj korisnik ne posjeduje email adresu na njihovom profilu.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'Korisnik je uspješno izrađen.',
        'update'    => 'Korisnik je uspješno ažuriran.',
        'update_bulk'    => 'Korisnici su uspješno ažurirani!',
        'delete'    => 'Korisnik je uspješno izbrisan.',
        'ban'       => 'Korisnik je uspješno zabranjen.',
        'unban'     => 'Korisnik je uspješno bio razotkriven.',
        'suspend'   => 'Korisnik je uspješno obustavljen.',
        'unsuspend' => 'Korisnik je uspješno suspendiran.',
        'restored'  => 'Korisnik je uspješno obnovljen.',
        'import'    => 'Korisnici su uspješno uvezeni.',
    ),

    'error' => array(
        'create' => 'Pojavio se problem pri stvaranju korisnika. Molim te pokušaj ponovno.',
        'update' => 'Došlo je do problema s ažuriranjem korisnika. Molim te pokušaj ponovno.',
        'delete' => 'Došlo je do problema s brisanjem korisnika. Molim te pokušaj ponovno.',
        'delete_has_assets' => 'Ovaj korisnik ima stavke dodijeljene i nije ih moguće izbrisati.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Došlo je do problema s obustavom korisnika. Molim te pokušaj ponovno.',
        'import'    => 'Došlo je do problema s uvozom korisnika. Molim te pokušaj ponovno.',
        'asset_already_accepted' => 'Ova je imovina već prihvaćena.',
        'accept_or_decline' => 'Morate prihvatiti ili odbiti ovaj materijal.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Predmete koje ste pokušali prihvatiti nisu provjereni.',
        'ldap_could_not_connect' => 'Povezivanje s LDAP poslužiteljem nije uspjelo. Provjerite konfiguraciju LDAP poslužitelja u LDAP konfiguracijskoj datoteci. <br>Preku s LDAP poslužitelja:',
        'ldap_could_not_bind' => 'Nije moguće povezati se s LDAP poslužiteljem. Provjerite konfiguraciju LDAP poslužitelja u LDAP konfiguracijskoj datoteci. <br>Preku s LDAP poslužitelja:',
        'ldap_could_not_search' => 'Nije moguće pretražiti LDAP poslužitelj. Provjerite konfiguraciju LDAP poslužitelja u LDAP konfiguracijskoj datoteci. <br>Preku s LDAP poslužitelja:',
        'ldap_could_not_get_entries' => 'Nije bilo moguće dobiti unose s LDAP poslužitelja. Provjerite konfiguraciju LDAP poslužitelja u LDAP konfiguracijskoj datoteci. <br>Preku s LDAP poslužitelja:',
        'password_ldap' => 'Lozinku za ovaj račun upravlja LDAP / Active Directory. Obratite se IT odjelu za promjenu zaporke.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Datoteka nije izbrisana. Molim te pokušaj ponovno.',
        'success' => 'Datoteka je uspješno obrisana.',
    ),

    'upload' => array(
        'error'   => 'Datoteke nisu prenesene. Molim te pokušaj ponovno.',
        'success' => 'Datoteke su uspješno učitane.',
        'nofiles' => 'Niste odabrali nijednu datoteku za prijenos',
        'invalidfiles' => 'Jedna ili više datoteka je prevelika ili je vrsta datoteke koja nije dopuštena. Dopuštene vrste datoteka su png, gif, jpg, doc, docx, pdf i txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Ovaj korisnik nema postavljenu mail adresu.',
        'success' => 'The user has been notified about their current inventory.'
    )
);