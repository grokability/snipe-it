<?php

return [

    'update' => [
        'error'                 => 'Der opstod en fejl under opdatering. ',
        'success'               => 'Indstillinger opdateret med succes.',
    ],
    'backup' => [
        'delete_confirm'        => 'Er du sikker på, at du vil slette denne sikkerhedskopieringsfil? Denne handling kan ikke fortrydes.',
        'file_deleted'          => 'Sikkerhedsfilen blev slettet korrekt.',
        'generated'             => 'En ny sikkerhedskopieringsfil blev oprettet.',
        'file_not_found'        => 'Denne backup-fil kunne ikke findes på serveren.',
        'restore_warning'       => 'Ja, gendanne den. Jeg anerkender, at dette vil overskrive alle eksisterende data i databasen. Dette vil også logge ud alle dine eksisterende brugere (inklusive dig).',
        'restore_confirm'       => 'Er du sikker på, at du vil gendanne din database fra :filnavn?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Der opstod en fejl under udrensning.',
        'validation_failed'     => 'Din udrensningsbekræftelse er forkert. Indtast ordet "DELETE" i bekræftelsesboksen.',
        'success'               => 'Slettet arkiver, der er renset for succes.',
    ],
    'mail' => [
        'sending' => 'Sender Test Email...',
        'success' => 'Mail sendt!',
        'error' => 'Mail kunne ikke sendes.',
        'additional' => 'Ingen yderligere fejlmeddelelse angivet. Tjek dine mail-indstillinger og din app-log.'
    ],
    'ldap' => [
        'testing' => 'Test LDAP Forbindelse, Binding & Query ...',
        '500' => '500 serverfejl. Tjek venligst dine serverlogs for mere information.',
        'error' => 'Noget gik galt :(',
        'sync_success' => 'En prøve på 10 brugere returnerede fra LDAP-serveren baseret på dine indstillinger:',
        'testing_authentication' => 'Test LDAP Autentificering...',
        'authentication_success' => 'Bruger godkendt mod LDAP!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Sender :app test besked...',
        'success' => 'Dine :webhook_name Integration virker!',
        'success_pt1' => 'Succes! Tjek ',
        'success_pt2' => ' kanal til din testbesked, og sørg for at klikke på GEM nedenfor for at gemme dine indstillinger.',
        '500' => '500 Serverfejl.',
        'error' => 'Noget gik galt. :app svarede med: :error_message',
        'error_redirect' => 'FEJL: 301/302: endpoint returnerer en omdirigering. Af sikkerhedsmæssige årsager følger vi ikke omdirigeringer. Brug det faktiske slutpunkt.',
        'error_misc' => 'Noget gik galt. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
