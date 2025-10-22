<?php

return array(

    'does_not_exist' => 'La categoria non esiste.',
    'assoc_models'	 => 'La categoria è già associata ad almeno un modello, pertanto non può essere eliminata. Aggiorna i modelli in modo che non si riferiscano alla categoria e riprova. ',
    'assoc_items'	 => 'Questa Categoria al momento è associata ad almeno un :asset_type perciò non può essere eliminata. Aggiorna il tuo :asset_type in modo che non si riferisca più alla Categoria e riprova. ',

    'create' => array(
        'error'   => 'La categoria non è stata creata, si prega di riprovare.',
        'success' => 'Categoria creata con successo.'
    ),

    'update' => array(
        'error'   => 'La categoria non è stata aggiornata, si prega di riprovare',
        'success' => 'Categoria aggiornata con successo.',
        'cannot_change_category_type'   => 'Una volta creata una Categoria non puoi cambiarne il Tipo',
    ),

    'delete' => array(
        'confirm'                => 'Sicuro di voler eliminare questa Categoria?',
        'error'                  => 'C\'è stato un problema eliminando la Categoria. Riprova.',
        'success'                => 'Cetegoria eliminata con successo.',
        'bulk_success'           => 'Categorie eliminate con successo.',
        'partial_success'        => 'Categoria eliminata con successo. Leggi le informazioni aggiuntive qui sotto. | :count categorie eliminate con successo. Leggi le informazioni aggiuntive qui sotto.',
    )

);
