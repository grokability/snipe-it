<?php

return [

    'undeployable' 		 => 'I seguenti Beni non possono essere consegnati e sono stati rimossi dall\'Assegnazione: :asset_tags',
    'does_not_exist' 	 => 'Questo Asset non esiste.',
    'does_not_exist_var' => 'Bene con tag :asset_tag non trovato.',
    'no_tag' 	         => 'Nessun tag del Bene è stato fornito.',
    'does_not_exist_or_not_requestable' => 'Questo bene non esiste o non è disponibile.',
    'assoc_users'	 	 => 'Questo asset è stato assegnato ad un Utente e non può essere cancellato. Per favore Riassegnalo in magazzino,e dopo riprova a cancellarlo.',
    'warning_audit_date_mismatch' 	=> 'La prossima data d\'inventario di questo Bene (:next_audit_date) precede l\'ultima data d\'inventario (:last_audit_date). Si prega di aggiornare la prossima data d\'inventario.',
    'labels_generated'   => 'Etichette generate con successo.',
    'error_generating_labels' => 'Errore durante la generazione delle etichette.',
    'no_assets_selected' => 'Nessun Bene selezionato.',

    'create' => [
        'error'   		=> 'L\'asset non è stato creato, riprova per favore. :(',
        'success' 		=> 'L\'asset è stato creato con successo. :)',
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
        'error'   			=> 'Il bene non è stato aggiornato, si prega di riprovare',
        'success' 			=> 'Bene aggiornato con successo.',
        'encrypted_warning' => 'Asset aggiornato con successo, ma i campi personalizzati crittografati non sono dovuti ai permessi',
        'nothing_updated'	=>  'Non è stato selezionato nessun campo, nulla è stato aggiornato.',
        'no_assets_selected'  =>  'Nessun asset è stato selezionato, quindi niente è stato eliminato.',
        'assets_do_not_exist_or_are_invalid' => 'Gli asset selezionati non possono essere aggiornati.',
    ],

    'restore' => [
        'error'   		=> 'Il bene non è stato ripristinato, riprova',
        'success' 		=> 'Bene ripristinato con successo.',
        'bulk_success' 		=> 'Bene ripristinato con successo.',
        'nothing_updated'   => 'Nessun bene selezionato, non è stato ripristinato nulla.', 
    ],

    'audit' => [
        'error'   		=> 'Inventario del Bene non riuscito: :error ',
        'success' 		=> 'L\'audit di risorse si è registrato con successo.',
    ],


    'deletefile' => [
        'error'   => 'File non cancellato. Riprova.',
        'success' => 'File cancellato con successo.',
    ],

    'upload' => [
        'error'   => 'File non caricato/i. Riprova.',
        'success' => 'File caricato/i con successo.',
        'nofiles' => 'Non hai selezionato nessun file per il caricamento, oppure il file selezionato è troppo grande',
        'invalidfiles' => 'Uno o più file è troppo grande o è un tipo di file non consentito. Tipi di file ammessi sono png, gif, jpg, doc, docx, pdf, txt.',
    ],

    'import' => [
        'import_button'         => 'Importa Processo',
        'error'                 => 'Alcuni elementi non sono stati importati correttamente.',
        'errorDetail'           => 'Gli articoli seguenti non sono stati importati correttamente a causa di errori.',
        'success'               => 'Il file è stato importato con successo',
        'file_delete_success'   => 'Il file è stato cancellato con successo',
        'file_delete_error'      => 'Impossibile eliminare il file',
        'file_missing' => 'File selezionato mancante',
        'file_already_deleted' => 'Il file selezionato è già stato eliminato',
        'header_row_has_malformed_characters' => 'Uno o più attributi nella riga d\'intestazione contengono caratteri UTF-8 malformati',
        'content_row_has_malformed_characters' => 'Uno o più attributi nella prima riga del contenuto contengono caratteri UTF-8 malformati',
        'transliterate_failure' => 'Traslitterazione da :encoding a UTF-8 non riuscita a causa di caratteri non validi nell\'input'
    ],


    'delete' => [
        'confirm'   	=> 'Sei sicuro di voler eliminare questo bene?',
        'error'   		=> 'C\'è stato un problema durante la cancellazione del bene. Riprova per favore.',
        'assigned_to_error' => '{1}Il tag: :asset_tag è assegnato. Effettua il check in del dispositivo prima di cancellarlo.|[2,*] :asset_tag sono assegnati. Effettua il check-in dei dispositivi prima di cancellarli.',
        'nothing_updated'   => 'Nessun patrimonio è stato selezionato, quindi niente è stato eliminato.',
        'success' 		=> 'Il bene è stato eliminato con successo.',
    ],

    'checkout' => [
        'error'   		=> 'Il bene non è stato assegnato, per favore riprova',
        'success' 		=> 'Il bene è stato assegnato con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'not_available' => 'Questo Bene non è disponibile per l\'assegnazione!',
        'no_assets_selected' => 'Devi selezionare almeno un Bene dall\'elenco',
    ],

    'multi-checkout' => [
        'error'   => 'L\'assegnazione non è andata a buon fine, riprova|Le assegnazioni non sono andate a buon fine, riprova',
        'success' => 'Bene assegnato correttamente.|Beni assegnati correttamente.',
    ],

    'checkin' => [
        'error'   		=> 'Il Bene non è stato restituito, riprova',
        'success' 		=> 'Bene restituito con successo.',
        'user_does_not_exist' => 'Questo utente non è valido. Riprova.',
        'already_checked_in'  => 'Il prodotto è già rientrato.',

    ],

    'requests' => [
        'error'   		=> 'Richiesta non riuscita, riprova.',
        'success' 		=> 'Richiesta inviata con successo.',
        'canceled'      => 'Richiesta annullata con successo.',
        'cancel'        => 'Annulla questa richiesta',
    ],

];
