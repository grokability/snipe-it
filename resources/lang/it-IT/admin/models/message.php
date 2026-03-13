<?php

return array(

    'deleted' => 'Modello bene eliminato',
    'does_not_exist' => 'Il modello non esiste.',
    'no_association' => 'ATTENZIONE! Il modello di bene per questo oggetto non è valido o è mancante!',
    'no_association_fix' => 'Ciò romperà cose in modi strani e brutti. Modifica questo bene per assegnargli un modello.',
    'assoc_users'	 => 'Questo modello è attualmente associato ad uno o più beni e non può essere eliminato. Eliminare i beni e poi provare a eliminare nuovamente. ',
    'invalid_category_type' => 'La categoria deve essere quella di un Bene.',

    'create' => array(
        'error'   => 'Il modello non è stato creato, si prega di riprovare.',
        'success' => 'Modello creato con successo.',
        'duplicate_set' => 'Un modello di prodotto con quel nome, produttore e numero di modello esiste già.',
    ),

    'update' => array(
        'error'   => 'Il modello non è stato aggiornato, si prega di riprovare',
        'success' => 'Modello aggiornato con successo.',
    ),

    'delete' => array(
        'confirm'   => 'Sei sicuro di voler eliminare questo modello?',
        'error'   => 'C\'è stato un problema durante la cancellazione del modello. Riprova per favore.',
        'success' => 'Modello cancellato con successo.'
    ),

    'restore' => array(
        'error'   		=> 'Il modello non è stato ripristinato, si prega di riprovare',
        'success' 		=> 'Modello ripristinato con successo.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Nessun campo è stato modificato, quindi niente è stato aggiornato.',
        'success' 		=> 'Modello aggiornato. |:model_count modelli aggiornati con successo.',
        'warn'          => 'Stai per aggiornare le proprietà del seguente modello:|Stai per modificare le proprietà dei seguenti :model_count modelli:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Non sono stati selezionati modelli, quindi non è stato eliminato nulla.',
        'success' 		    => 'Modello cancellato!|:success_count modelli cancellati!',
        'success_partial' 	=> ':success_count modelli sono stati eliminati, tuttavia non è stato possibile eliminare :fail_count modelli perché dispongono ancora di risorse associate.'
    ),

);
