<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'Käyttäjät on onnistuneesti päivitetty, mutta esimies-merkintää ei tallennettu, koska valitsemasi esimies oli mukana käyttäjäluettelossa, eikä käyttäjä voi olla itsensä esimies. Valitse käyttäjät uudelleen, poislukien esimies.',
    'user_exists'               => 'Käyttäjä on jo luotu!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'Käyttäjää ei ole olemassa tai sinulla ei ole oikeuksia tarkastella niitä.',
    'user_login_required'       => 'Käyttäjätunnus vaaditaan',
    'user_has_no_assets_assigned' => 'Käyttäjälle ei tällä hetkellä ole määritetty omaisuutta.',
    'user_password_required'    => 'Salasana vaaditaan.',
    'insufficient_permissions'  => 'Riittämättömät oikeudet.',
    'user_deleted_warning'      => 'Käyttäjä on jo poistettu. Mikäli haluat muokata tai luovuttaa laitteita hänelle sinun tulee palauttaa käyttäjä.',
    'ldap_not_configured'        => 'LDAP-integraatiota ei ole määritetty tähän asennukseen.',
    'password_resets_sent'      => 'Salasanan resetointilinkki on lähetetty niille käyttäjille, joille on määritetty voimassa oleva sähköpostiosoite.',
    'password_reset_sent'       => 'Salasanan palautuslinkki on lähetetty osoitteeseen :email!',
    'user_has_no_email'         => 'Tällä käyttäjällä ei ole sähköpostiosoitetta heidän profiilissaan.',
    'log_record_not_found'        => 'Tälle käyttäjälle ei löytynyt vastaavaa lokitietuetta.',


    'success' => array(
        'create'    => 'Käyttäjä luotiin onnistuneesti.',
        'update'    => 'Käyttäjä päivitettiin onnistuneesti.',
        'update_bulk'    => 'Käyttäjät päivitettiin onnistuneesti!',
        'delete'    => 'Käyttäjä poistettiin onnistuneesti.',
        'ban'       => 'Käyttäjä estettiin onnistuneesti.',
        'unban'     => 'Käyttäjän esto poistettiin onnistuneesti.',
        'suspend'   => 'Käyttäjä jäädytettiin onnistuneesti.',
        'unsuspend' => 'Käyttäjän jäädytys poistettiin onnistuneesti.',
        'restored'  => 'Käyttäjä palautettiin onnistuneesti.',
        'import'    => 'Käyttäjät tuotiin onnistuneesti.',
    ),

    'error' => array(
        'create' => 'Käyttäjä luonnissa tapahtui virhe. Yritä uudelleen.',
        'update' => 'Käyttäjän päivityksessä tapahtui virhe. Yritä uudelleen.',
        'delete' => 'Käyttäjän poistamisessa tapahtui virhe. Yritä uudelleen.',
        'delete_has_assets' => 'Käyttäjää ei voida poistaa, koska käyttäjälle on luovutettuna nimikkeitä.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'Käyttäjän jäädytyksen poistossa tapahtui virhe. Yritä uudelleen.',
        'import'    => 'Käyttäjien tuonnissa tapahtui virhe, Yritä uudelleen.',
        'asset_already_accepted' => 'Tämä laite on jo hyväksytty.',
        'accept_or_decline' => 'Sinun on joko hyväksyttävä tai hylättävä tämä laite.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'Laitetta jota yritit hyväksyä, ei luovutettu sinulle.',
        'ldap_could_not_connect' => 'Yhteyttä LDAP-palvelimeen ei voitu muodostaa. Tarkista LDAP-palvelimen määritys. <br> LDAP-palvelimen virhe:',
        'ldap_could_not_bind' => 'Yhdistäminen LDAP-palvelimeen ei onnistunut. Tarkista LDAP-palvelimen asetukset. <br>LDAP-palvelimen virhe: ',
        'ldap_could_not_search' => 'Haku LDAP-palvelimelta ei onnistunut ei voitu hakea. Tarkista LDAP-palvelimen määritys. <br>LDAP-palvelimen virhe:',
        'ldap_could_not_get_entries' => 'LDAP-palvelimelta ei palautunut kohteita. Tarkista LDAP-palvelimen määritys. <br>LDAP-palvelimen virhe:',
        'password_ldap' => 'Tätä salasanaa hallinnoi LDAP / Active Directory. Vaihda salasanasi IT-osastolla.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'Tiedostoa ei ole poistettu. Yritä uudelleen.',
        'success' => 'Tiedosto onnistuneesti poistettu.',
    ),

    'upload' => array(
        'error'   => 'Tiedostoja ei ole ladattu. Yritä uudelleen.',
        'success' => 'Tiedostot lähetettiin onnistuneesti.',
        'nofiles' => 'Et valinnut yhtään tiedostoa lähetettäväksi',
        'invalidfiles' => 'Yksi tai useampi tiedosto on liian suuri tai on tiedostotyyppi, jota ei sallita. Sallitut tiedostotyypit ovat png, gif, jpg, doc, docx, pdf ja txt.',
    ),

    'inventorynotification' => array(
        'error'   => 'Tällä käyttäjällä ei ole sähköpostia.',
        'success' => 'Käyttäjälle on ilmoitettu heidän nykyisestä tavaraluettelostaan.'
    )
);