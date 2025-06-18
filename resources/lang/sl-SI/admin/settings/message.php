<?php

return [

    'update' => [
        'error'                 => 'Med posodabljanjem je prišlo do napake. ',
        'success'               => 'Nastavitve so bile posodobljene uspešno.',
    ],
    'backup' => [
        'delete_confirm'        => 'Ali ste prepričani, da želite izbrisati to varnostno datoteko? To dejanje ni mogoče razveljaviti. ',
        'file_deleted'          => 'Varnostna datoteka je bila uspešno izbrisana. ',
        'generated'             => 'Ustvarjena je bila nova varnostna kopija.',
        'file_not_found'        => 'To varnostno datoteko ni bilo mogoče najti na strežniku.',
        'restore_warning'       => 'Yes, restore it. I acknowledge that this will overwrite any existing data currently in the database. This will also log out all of your existing users (including you).',
        'restore_confirm'       => 'Are you sure you wish to restore your database from :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Pri čiščenju je prišlo do napake. ',
        'validation_failed'     => 'Vaša potrditev čiščenja je napačna. V polje za potrditev vnesite besedo »DELETE«.',
        'success'               => 'Izbrisani zapisi so bili uspešno počiščeni.',
    ],
    'mail' => [
        'sending' => 'Sending Test Email...',
        'success' => 'Mail sent!',
        'error' => 'Mail could not be sent.',
        'additional' => 'No additional error message provided. Check your mail settings and your app log.'
    ],
    'ldap' => [
        'testing' => 'Testing LDAP Connection, Binding & Query ...',
        '500' => '500 Server Error. Please check your server logs for more information.',
        'error' => 'Nekaj je šlo narobe :(',
        'sync_success' => 'A sample of 10 users returned from the LDAP server based on your settings:',
        'testing_authentication' => 'Testiranje LDAP Avtentikacije...',
        'authentication_success' => 'Uporabnik se je uspešno avtoriziral z LDAP!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Pošiljanje :apikacija testirno sporočilo...',
        'success' => 'Tvoj :ime_webhooka integracija deluje!',
        'success_pt1' => 'Success! Check the ',
        'success_pt2' => ' channel for your test message, and be sure to click SAVE below to store your settings.',
        '500' => '500 strežniška napaka.',
        'error' => 'Something went wrong. :app responded with: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => 'Nekaj je šlo narobe. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
