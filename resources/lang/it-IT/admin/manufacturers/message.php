<?php

return array(

    'support_url_help' => 'Le variabili <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code> e <code>{MODEL_NAME}</code> possono essere utilizzate nell\'URL per riempire i campi dinamicamente - ad esempio https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'Il produttore non esiste.',
    'assoc_users'	 => 'Questo produttore è attualmente associato con almeno un modello e non può essere eliminato. Si prega di aggiornare i modelli di riferimento e riprovare. ',

    'create' => array(
        'error'   => 'Il produttore non è stato creato, si prega di riprovare.',
        'success' => 'Produttore creato con successo.'
    ),

    'update' => array(
        'error'   => 'Il produttore non è stato aggiornato, riprova',
        'success' => 'Produttore aggiornato con successo.'
    ),

    'restore' => array(
        'error'   => 'Il produttore non è stato ripristinato, per favore riprova',
        'success' => 'Produttore ripristinato con successo.'
    ),

    'delete' => array(
        'confirm' => 'Sei sicuro di voler eliminare questo produttore?',
        'error'   => 'C\'è stato un problema nell\'eliminazione del produttore. Riprova.',
        'success'                => 'Produttore eliminato con successo.',
        'bulk_success'           => 'Produttori eliminati con successo.',
        'partial_success'        => 'Produttore eliminato con successo. Leggi le informazioni aggiuntive qui sotto. | :count produttori eliminati con successo. Leggi le informazioni aggiuntive qui sotto.',
    )

);
