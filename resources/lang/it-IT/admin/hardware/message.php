<?php

return [

    'undeployable' => 'I seguenti Beni non possono essere consegnati e sono stati rimossi dall\'Assegnazione: :asset_tags',
    'does_not_exist' => 'Questo Asset non esiste.',
    'does_not_exist_var' => 'Bene con tag :asset_tag non trovato.',
    'no_tag' => 'Nessun tag del Bene è stato fornito.',
    'does_not_exist_or_not_requestable' => 'Questo bene non esiste o non è disponibile.',
    'assoc_users' => 'Questo asset è stato assegnato ad un Utente e non può essere cancellato. Per favore Riassegnalo in magazzino,e dopo riprova a cancellarlo.',
    'warning_audit_date_mismatch' => 'La prossima data d\'inventario di questo Bene (:next_audit_date) precede l\'ultima data d\'inventario (:last_audit_date). Si prega di aggiornare la prossima data d\'inventario.',
    'labels_generated' => 'Etichette generate con successo.',
    'error_generating_labels' => 'Errore durante la generazione delle etichette.',
    'no_assets_selected' => 'Nessun Bene selezionato.',

    'create' => [
        'error' => 'L\'asset non è stato creato, riprova per favore. :(',
        'success' => 'L\'asset è stato creato con successo. :)',
        'success_no_checkout' => 'Asset created successfully, but was not checked out because you do not have permission to check assets out.',
        'checkout_skipped_no_permission' => 'The asset was created, but was not checked out to the requested target because you do not have permission to check assets out.',
        'success_linked' => 'Bene creato con tag :tag . <strong><a href=":link" style="color: white;">Clicca per vedere</a></strong>.',
        'multi_success_linked' => 'Il bene con tag :links è stato creato con successo.|:count beni sono stati creati con successo. :links.',
        'partial_failure' => 'Non è stato possibile creare un bene. Motivo: :failures|Non è stato possibile creare :count beni. Motivi: :failures',
        'target_not_found' => [
            'user' => 'L\'utente assegnato non è stato trovato.',
            'asset' => 'Il Bene assegnato non è stato trovato.',
            'location' => 'La Sede assegnata non è stata trovata.',
        ],
    ],

    'update' => [
        'error' => 'Il bene non è stato aggiornato, si prega di riprovare',
        'success' => 'Bene aggiornato con successo.',
        'encrypted_warning' => 'Asset aggiornato con successo, ma i campi personalizzati crittografati non sono dovuti ai permessi',
        'nothing_updated' => 'Non è stato selezionato nessun campo, nulla è stato aggiornato.',
        'no_assets_selected' => 'Nessun asset è stato selezionato, quindi niente è stato eliminato.',
        'assets_do_not_exist_or_are_invalid' => 'Gli asset selezionati non possono essere aggiornati.',
    ],

    'bulk_update' => [
        'success' => 'Bene aggiornato con successo.|:count Beni aggiornati con successo.',
        'partial' => ':success Bene/i aggiornato con successo, :failed aggiornamenti falliti. Vedi l\'elenco risultati per dettagli maggiori.',
        'error' => 'Nessun Bene aggiornato. Vedi l\'elenco dei risultati per maggiori dettagli.',
    ],

    'restore' => [
        'error' => 'Il bene non è stato ripristinato, riprova',
        'success' => 'Bene ripristinato con successo.',
        'bulk_success' => 'Bene ripristinato con successo.',
        'nothing_updated' => 'Nessun bene selezionato, non è stato ripristinato nulla.',
    ],

    'audit' => [
        'error' => 'Inventario del Bene non riuscito: :error ',
        'success' => 'L\'audit di risorse si è registrato con successo.',
    ],

    'deletefile' => [
        'error' => 'File non cancellato. Riprova.',
        'success' => 'File cancellato con successo.',
    ],

    'upload' => [
        'error' => 'File non caricato/i. Riprova.',
        'success' => 'File caricato/i con successo.',
        'nofiles' => 'Non hai selezionato nessun file per il caricamento, oppure il file selezionato è troppo grande',
        'invalidfiles' => 'Uno o più file è troppo grande o è un tipo di file non consentito. Tipi di file ammessi sono png, gif, jpg, doc, docx, pdf, txt.',
    ],

    'import' => [
        'import_button' => 'Importa Processo',
        'error' => 'Alcuni elementi non sono stati importati correttamente.',
        'errorDetail' => 'Gli articoli seguenti non sono stati importati correttamente a causa di errori.',
        'success' => 'Il file è stato importato con successo',
        'file_delete_success' => 'Il file è stato cancellato con successo',
        'file_delete_error' => 'Impossibile eliminare il file',
        'file_missing' => 'File selezionato mancante',
        'file_already_deleted' => 'Il file selezionato è già stato eliminato',
        'file_missing_on_disk' => 'Il file per questa importazione non è più su disco. Potrebbe essere stato eliminato al di fuori di Snipe-IT. Elimina questa voce e ricarica il file per riprovare.',
        'file_empty' => 'Questo file non ha righe di dati. Niente da importare.',
        'already_processing' => 'Questa importazione è attualmente in fase di elaborazione da un altro utente. Si prega di attendere che finisca prima di riprovare.',
        'header_row_missing' => 'Questo file non ha una riga d\'intestazione riconosciuta. Elimina questa voce e ricarica il file per riprovare.',
        'header_row_has_malformed_characters' => 'Uno o più attributi nella riga d\'intestazione contengono caratteri UTF-8 malformati',
        'content_row_has_malformed_characters' => 'Uno o più attributi nella prima riga del contenuto contengono caratteri UTF-8 malformati',
        'transliterate_failure' => 'Traslitterazione da :encoding a UTF-8 non riuscita a causa di caratteri non validi nell\'input',
        'bulk_delete' => [
            'button' => 'Elimina Selezionati (:count)',
            'confirm_title' => 'Eliminare i file di import selezionati?',
            'confirm_body' => 'Stai per eliminare permanentemente :count file d\'importazione. Una volta eseguita, questa operazione non può essere annullata.',
            'confirm_button' => 'Cancella',
            'success' => 'File di importazione eliminato con successo.|:count file di importazione eliminati con successo.',
            'skipped' => ':count file saltati perché non hai i privilegi necessari per eliminarli.',
            'select_all' => 'Seleziona tutti i file su questa pagina',
            'select_row' => 'Seleziona :file per eliminazione di massa',
        ],
        'row_count' => '{0} Nessuna riga di dati in questo file|{1} :count riga di dati da importare|[2,*] :count righe di dati da importare',
        'summary' => [
            'created' => 'Creato',
            'updated' => 'Aggiornato',
            'skipped' => 'Ignorati perché duplicati',
            'errored' => 'In errore',
            'no_changes' => 'L\'importazione è terminata ma non è stato creato o aggiornato niente. Ogni riga è stata saltata, di solito perché i record sottostanti esistevano già. Controlla i conteggi qui sotto e aggiusta il tipo CSV o il tipo d\'importazione se non ti aspettavi questo comportamento.',
        ],
        'type_required' => 'Scegli un tipo d\'importazione prima di proseguire.',
        'processing' => 'Sto elaborando la tua importazione. Prima di chiudere la pagina, attendi che finisca.',
        'backup_running' => 'Esecuzione del backup prima dell\'importazione. Può richiedere un po\' di tempo per file più grandi. Attendere prego.',
        'backup_label' => 'Backup pre-importazione',
        'backup_complete' => 'Backup completato',
        'import_label' => 'Importa',
        'required_fields_missing' => 'I seguenti campi obbligatori non sono mappati: :fields',
        'history' => [
            'missing_asset_tag_identity' => '(tag Bene mancante)',
            'missing_asset_tag_message' => 'Riga saltata: non è stato fornito nessun tag del Bene.',
            'asset_not_found_message' => 'Il Bene con questo tag non esiste. Prima importa i Beni, poi rilancia l\'importazione della cronologia.',
            'target_not_matched_message' => 'Nessun :target_type corrisponde a ":name". Per gli utenti, attiva le opzioni "ricerca per" nel passo 1, o crea prima l\'utente. Per le sedi, assicurati che il nome della sede nel CSV corrisponda esattamente a un sede esistente.',
            'invalid_target_type_message' => 'Il tipo di destinazione ":value" non è riconosciuto. Usa "utente" o "sede", o lascia vuota la colonna per usare l\'utente come predefinito.',
        ],
        'wizard' => [
            'step_type' => 'Scegli tipo',
            'step_map' => 'Mappa campi',
            'step_preview' => 'Anteprima',
            'back' => 'Indietro',
            'next' => 'Successivo',
            'preview_button' => 'Anteprima',
            'process' => 'Importa Processo',
            'preview_intro' => 'Anteprima delle prime :count righe dopo aver applicato la mappatura. Utilizzare il pulsante Indietro se devi modificare gli attributi mappati prima dell\'importazione.',
        ],
    ],

    'delete' => [
        'confirm' => 'Sei sicuro di voler eliminare questo bene?',
        'error' => 'C\'è stato un problema durante la cancellazione del bene. Riprova per favore.',
        'assigned_to_error' => '{1}Il tag: :asset_tag è assegnato. Effettua il check in del dispositivo prima di cancellarlo.|[2,*] :asset_tag sono assegnati. Effettua il check-in dei dispositivi prima di cancellarli.',
        'nothing_updated' => 'Nessun patrimonio è stato selezionato, quindi niente è stato eliminato.',
        'success' => 'Il bene è stato eliminato con successo.',
    ],

    'checkout' => [
        'error' => 'Il bene non è stato assegnato, per favore riprova',
        'success' => 'Il bene è stato assegnato con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'not_available' => 'Questo Bene non è disponibile per l\'assegnazione!',
        'no_assets_selected' => 'Devi selezionare almeno un Bene dall\'elenco',
    ],

    'multi-checkout' => [
        'error' => 'L\'assegnazione non è andata a buon fine, riprova|Le assegnazioni non sono andate a buon fine, riprova',
        'success' => 'Bene assegnato correttamente.|Beni assegnati correttamente.',
    ],

    'multi-checkin' => [
        'error' => 'Il Bene non è stato restituito, riprova|I Beni non sono stati restituiti, riprova',
        'success' => 'Bene restituito correttamente.|Beni restituiti correttamente.',
        'no_assets_selected' => 'Devi selezionare almeno un Bene dall\'elenco',
    ],

    'multi-audit' => [
        'success' => ':count Bene inventariato con successo.|:count Beni inventariati con successo.',
        'partial_error' => ':success Bene inventariato, :failed falliti. Controlla gli errori qui sotto e riprova.|:success Beni inventariati, :failed falliti. Controlla gli errori qui sotto e riprova.',
        'no_assets_selected' => 'Devi selezionare almeno un Bene dall\'elenco',
    ],

    'checkin' => [
        'error' => 'Il Bene non è stato restituito, riprova',
        'success' => 'Bene restituito con successo.',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'already_checked_in' => 'Il prodotto è già rientrato.',
        'force_checkin_orphaned_success' => 'Assegnazione non valida annullata con successo.',
        'force_checkin_not_orphaned' => 'L\'articolo non è in uno stato di assegnazione non valido.',
        'force_checkin_error' => 'Impossibile cancellare l\'assegnazione non valida.',

    ],

    'requests' => [
        'error' => 'Richiesta non riuscita, riprova.',
        'success' => 'Richiesta inviata con successo.',
        'canceled' => 'Richiesta annullata con successo.',
        'cancel' => 'Annulla questa richiesta',
        'duplicate' => 'Hai già una richiesta attiva per questo elemento.',
        'no_active' => 'Non hai richieste attive da annullare per questo elemento.',
        'insufficient_stock' => 'Disponibilità insufficiente per questa richiesta. Prima rifornisci.',
        'confirm_cancel_by_admin' => "Annullare la richiesta di :user per :item?",
        'no_selection' => 'Nessuna riga selezionata da evadere.',
        'row_stale' => 'La richiesta #:id non è più in attesa ed è stata saltata.',
        'row_qty_invalid' => 'La richiesta #:id ha una quantità non valida ed è stata saltata.',
        'row_user_missing' => 'La richiesta #:id non ha un utente di destinazione valido ed è stata saltata.',
        'row_asset_missing' => 'Richiesta #:id non ha un Bene di destinazione valido ed è stata saltata.',
        'row_asset_not_requesters' => 'Richiesta #:id saltata: il Bene selezionato non appartiene al richiedente.',
        'row_asset_not_available' => 'Richiesta #:id saltata: il Bene selezionato non è un\'unità disponibile di questo modello.',
        'row_asset_taken' => 'Richiesta #:id saltata: il Bene selezionato è stato assegnato a qualcun altro nel frattempo.',
        'row_company_mismatch' => 'Richiesta #:id saltata: :user non può ricevere articoli da questa azienda.',
        'row_over_allocated' => 'Richiesta #:id saltata: quantità di magazzino insufficiente al momento della richiesta.',
        'no_target_assets_for_user' => ':user non ha Beni in cui installare.',
        'no_available_units' => 'Nessuna unità disponibile di questo modello da distribuire.',
        'bulk_summary' => 'Soddisfatte :fulfilled richieste su :total.',
        'bulk_fulfill_notification_intro' => 'A ciascuna richiesta evasa si applicherà quanto segue:',
    ],

];
