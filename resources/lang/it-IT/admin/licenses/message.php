<?php

return array(

    'does_not_exist' => 'Il database non esiste o non si dispone delle autorizzazioni per la connessione.',
    'user_does_not_exist' => 'L\'utente non esiste o non ha il permesso di visualizzarlo.',
    'asset_does_not_exist' 	=> 'Il bene che si sta cercando di associare a questa licenza non esiste.',
    'owner_doesnt_match_asset' => 'Il bene che si sta cercando di associare a questa licenza è di proprietà di una persona diversa dal soggetto selezionato nel menù a discesa.',
    'assoc_users'	 => 'Questo bene è stato assegnato ad un Utente e non può essere cancellato. Per favore Riassegnalo in magazzino,e dopo riprova a cancellarlo. ',
    'select_asset_or_person' => 'È necessario selezionare un\'attività o un utente, ma non entrambi.',
    'not_found' => 'Licenza non trovata',
    'seats_available' => ':seat_count copie disponibili',


    'create' => array(
        'error'   => 'La licenza non è stata creata, si prega di riprovare.',
        'success' => 'Licenza creata con successo.'
    ),

    'deletefile' => array(
        'error'   => 'File non cancellato. Riprova.',
        'success' => 'File cancellato con successo.',
    ),

    'upload' => array(
        'error'   => 'File non caricato/i. Riprova.',
        'success' => 'File caricato/i con successo.',
        'nofiles' => 'Non hai selezionato nessun file per il caricamento, oppure il file selezionato è troppo grande',
        'invalidfiles' => 'Uno o più file sono troppo grandi o il formato del file non è consentito. I tipi di file consentiti sono png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, e lic.',
    ),

    'update' => array(
        'error'   => 'La licenza non è stata aggiornata, si prega di riprovare',
        'success' => 'Licenza aggiornata con successo.'
    ),

    'delete' => array(
        'confirm'   => 'Sei sicuro di voler cancellare questa licenza?',
        'error'   => 'C\'è stato un problema nell\'eliminazione della licenza. Riprova.',
        'success' => 'Licenza eliminata con successo.'
    ),

    'checkout' => array(
        'error'   => 'Problema durante l\'assegnazione della Licenza. Riprova.',
        'success' => 'La licenza è stata assegnata con successo',
        'not_enough_seats' => 'Non ci sono abbastanza copie della licenza disponibili per l\'assegnazione',
        'mismatch' => 'Lo slot di licenza fornito non corrisponde alla licenza',
        'unavailable' => 'Questo slot non è disponibile per l\'Assegnazione.',
        'license_is_inactive' => 'Questa licenza è scaduta o terminata.',
    ),

    'checkin' => array(
        'error'   => 'C\'è stato un problema nella restituzione della licenza. Riprova.',
        'not_reassignable' => 'Licenza già utilizzata',
        'success' => 'La licenza è stata restituita con successo'
    ),

);
