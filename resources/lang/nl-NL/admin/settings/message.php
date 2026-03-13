<?php

return [

    'update' => [
        'error'                 => 'Er is een fout opgetreden tijdens het updaten. ',
        'success'               => 'Instellingen zijn met succes gewijzigd.',
    ],
    'backup' => [
        'delete_confirm'        => 'Weet je het zeker dat je deze Back-up bestand wilt verwijderen? Dit kan niet meer terug gedraaid worden. ',
        'file_deleted'          => 'De Back-up bestand is met succes verwijderd. ',
        'generated'             => 'Een nieuw Back-up bestand is met succes aangemaakt.',
        'file_not_found'        => 'Die Back-up bestand kon niet gevonden worden op de server.',
        'restore_warning'       => 'Ja, herstellen. Ik bevestig dat dit alle bestaande gegevens die momenteel in de database aanwezig zijn, overschreven worden. Dit zal ook alle bestaande gebruikers uitloggen (inclusief jijzelf).',
        'restore_confirm'       => 'Weet je zeker dat je je database wilt herstellen met :filename?'
    ],
    'restore' => [
        'success'               => 'Uw systeemback-up is hersteld. Log opnieuw in.'
    ],
    'purge' => [
        'error'     => 'Er is iets fout gegaan tijdens het opschonen.',
        'validation_failed'     => 'De opschoon bevestiging is niet correct. Typ het woord "DELETE" in het bevestigingsveld.',
        'success'               => 'Verwijderde items succesvol opgeschoond',
    ],
    'mail' => [
        'sending' => 'Test e-mail wordt verzonden...',
        'success' => 'E-mail verzonden!',
        'error' => 'E-mail kon niet verzonden worden.',
        'additional' => 'Geen extra foutmelding beschikbaar. Controleer je e-mailinstellingen en je app log.'
    ],
    'ldap' => [
        'testing' => 'LDAP-verbinding testen, Binding & Query ...',
        '500' => '500 serverfout. Controleer de logbestanden van uw server voor meer informatie.',
        'error' => 'Er ging iets mis :(',
        'sync_success' => 'Een voorbeeld van 10 gebruikers is teruggekomen van de LDAP-server op basis van jouw instellingen:',
        'testing_authentication' => 'LDAP-authenticatie testen...',
        'authentication_success' => 'Gebruiker met succes geverifieerd met LDAP!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => ':app test bericht wordt verzonden...',
        'success' => 'Je :webhook_name integratie werkt!',
        'success_pt1' => 'Gelukt! Controleer de ',
        'success_pt2' => ' kanaal voor uw testbericht, vergeet niet om op OPSLAAN te klikken om de instellingen op te slaan.',
        '500' => '500 Server Error.',
        'error' => 'Er ging iets mis. :app reageerde met: :error_message',
        'error_redirect' => 'FOUT: 301/302 :endpoint geeft een omleiding. Om veiligheidsredenen volgen we geen omleidingen. Gebruik het werkelijke eindpunt.',
        'error_misc' => 'Er ging iets mis. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
