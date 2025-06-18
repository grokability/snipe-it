<?php

return [

    'update' => [
        'error'                 => 'Errore durante l\'aggiornamento. ',
        'success'               => 'Impostazioni aggiornate correttamente.',
    ],
    'backup' => [
        'delete_confirm'        => 'Sei sicuro di voler cancellare questo file di backup? Questa operazione è irreversibile. ',
        'file_deleted'          => 'Il file di backup è stato cancellato con successo. ',
        'generated'             => 'Un nuovo file di backup è stato creato con successo.',
        'file_not_found'        => 'Quel file di backup non può essere trovato sul server.',
        'restore_warning'       => 'Si, ripristina. Riconosco che il ripristino sovrascriverà tutti i dati al momento presenti nel database. Inoltre, tutti gli utenti verranno disconnessi (incluso te).',
        'restore_confirm'       => 'Sei sicuro di voler ripristinare il tuo database da :filename?'
    ],
    'restore' => [
        'success'               => 'Il backup del sistema è stato ripristinato. Effettua nuovamente il login.'
    ],
    'purge' => [
        'error'     => 'Si è verificato un errore durante la pulizia. ',
        'validation_failed'     => 'La conferma dell\'eliminazione non è corretta. Digita "DELETE" nel box di conferma.',
        'success'               => 'I record cancellati sono stati correttamente eliminati.',
    ],
    'mail' => [
        'sending' => 'Invio Email Di Prova...',
        'success' => 'Mail inviata!',
        'error' => 'Non è stato possibile inviare l\'email.',
        'additional' => 'Nessun messaggio di errore aggiuntivo fornito. Controlla le impostazioni della posta e il log dell\'app.'
    ],
    'ldap' => [
        'testing' => 'Testo Connessione, Binding e Query LDAP ...',
        '500' => 'Errore del server 500. Controlla i log del tuo server per maggiori informazioni.',
        'error' => 'Qualcosa è andato storto :(',
        'sync_success' => 'Un campione di 10 utenti restituiti dal server LDAP in base alle tue impostazioni:',
        'testing_authentication' => 'Testo l\'Autenticazione LDAP...',
        'authentication_success' => 'Utente autenticato correttamente con LDAP!'
    ],
    'labels' => [
        'null_template' => 'Modello di etichetta non trovato. Si prega di selezionare un modello.',
        ],
    'webhook' => [
        'sending' => 'Invio a :app un messaggio di prova...',
        'success' => 'La tua integrazione :webhook_name funziona!',
        'success_pt1' => 'Successo! Controlla il canale ',
        'success_pt2' => ' con il messaggio di prova, e assicurati di fare clic su SALVA qui sotto per memorizzare le impostazioni.',
        '500' => 'Errore del server 500.',
        'error' => 'Qualcosa è andato storto. :app ha risposto con: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint restituisce un reindirizzamento. Per motivi di sicurezza, non seguiamo reindirizzamenti. Si prega di utilizzare l\'endpoint attuale.',
        'error_misc' => 'Qualcosa è andato storto. :( ',
        'webhook_fail' => ' notifica webhook fallita: Controlla che l\'URL sia ancora valido.',
        'webhook_channel_not_found' => ' canale webhook non trovato.',
        'ms_teams_deprecation' => 'L\'URL del webhook di Microsoft Teams selezionato verrà ritirato il 31 dicembre 2025. Si prega di utilizzare un URL di un flusso di lavoro. La documentazione Microsoft sulla creazione di un flusso di lavoro è disponibile <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">qui.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Le tue impostazioni non sono state salvate.',
        'mismatch' => 'C\'è 1 elemento nel database che ha bisogno della vostra attenzione prima di abilitare i filtri per Sede.|Ci sono :count elementi nel database che hanno bisogno della vostra attenzione prima di abilitare i filtri per Sede.',
    ],
];
