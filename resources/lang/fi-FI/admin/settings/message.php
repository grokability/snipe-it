<?php

return [

    'update' => [
        'error'                 => 'Päivityksessä tapahtui virhe. ',
        'success'               => 'Asetukset päivitettiin onnistuneesti.',
    ],
    'backup' => [
        'delete_confirm'        => 'Haluatko varmasti poistaa tämän varmuuskopiotiedoston? Tätä toimintoa ei voi kumota.',
        'file_deleted'          => 'Varmuuskopiotiedosto on poistettu onnistuneesti.',
        'generated'             => 'Uusi varmuuskopiotiedosto luotiin onnistuneesti.',
        'file_not_found'        => 'Tätä varmuuskopiotiedostoa ei löytynyt palvelimelta.',
        'restore_warning'       => 'Kyllä, palauttaa sen. Ymmärrän, että tämä korvaa kaikki olemassa olevat tiedot tietokannassa. Tämä myös kirjautuu ulos kaikista nykyisistä käyttäjistä (mukaan lukien sinä).',
        'restore_confirm'       => 'Oletko varma, että haluat palauttaa tietokannan :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Virhe on ilmennyt puhdistuksen aikana.',
        'validation_failed'     => 'Puhdistusvahvistus on virheellinen. Kirjoita vahvistusruutuun sana "DELETE".',
        'success'               => 'Poistetut tietueet puhdistettu onnistuneesti.',
    ],
    'mail' => [
        'sending' => 'Lähetetään Testiviestiä...',
        'success' => 'Sähköposti lähetetty!',
        'error' => 'Sähköpostia ei voitu lähettää.',
        'additional' => 'Lisävirheilmoitusta ei annettu. Tarkista sähköpostiasetuksesi ja sovelluslokisi.'
    ],
    'ldap' => [
        'testing' => 'Testataan Ldap-yhteyttä, Sidotetaan & Kysely...',
        '500' => 'Palvelimen virhe. Tarkista palvelimen lokitiedot saadaksesi lisätietoja.',
        'error' => 'Jokin meni pieleen :(',
        'sync_success' => 'Näyte 10 käyttäjää palasi LDAP palvelimelta perusteella asetukset:',
        'testing_authentication' => 'Testataan Ldap Todennusta...',
        'authentication_success' => 'Käyttäjä tunnistettu LDAP vastaan!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Lähetetään :app testiviestiä...',
        'success' => 'Sinun :webhook_name Integraatio toimii!',
        'success_pt1' => 'Onnistui! Tarkista ',
        'success_pt2' => ' kanava testiviestillesi ja varmista, että klikkaat Tallenna alla olevat asetukset tallentaaksesi.',
        '500' => '500 Palvelimen Virhe.',
        'error' => 'Jokin meni pieleen. :app vastasi: :error_message',
        'error_redirect' => 'VIRHE: 301/302 :endpoint palauttaa uudelleenohjauksen. Turvallisuussyistä emme seuraa uudelleenohjauksia. Käytä todellista päätepistettä.',
        'error_misc' => 'Jokin meni pieleen. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
