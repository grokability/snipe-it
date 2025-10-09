<?php

return array(

    'does_not_exist' => 'La Sede non esiste.',
    'assoc_users'    => 'Questa Sede al momento non è eliminabile, perché vi sono registrati almeno un oggetto o un utente, o ha Beni assegnati, o è la sede "madre" di un\'altra sede. Aggiorna i dati in modo che non facciano più riferimento a questa sede, e riprova. ',
    'assoc_assets'	 => 'Questa Sede è associata ad almeno un prodotto e non può essere cancellata. Si prega di aggiornare i vostri prodotti di riferimento e riprovare. ',
    'assoc_child_loc'	 => 'La Sede contiene almeno un\'altra Sede, pertanto non può essere eliminata. Aggiorna le Sedi in modo che non siano parte di questa Sede e riprova. ',
    'assigned_assets' => 'Beni Assegnati',
    'current_location' => 'Sede attuale',
    'open_map' => 'Apri con :map_provider_icon Maps',
    'deleted_warning' => 'Questa Sede è stata eliminata. Prima di provare a fare modifiche, ricorda di ripristinarla.',


    'create' => array(
        'error'   => 'La Sede non è stata creata, si prega di riprovare.',
        'success' => 'Sede creata con successo.'
    ),

    'update' => array(
        'error'   => 'La Sede non è stata aggiornata, si prega di riprovare',
        'success' => 'Sede aggiornata con successo.'
    ),

    'restore' => array(
        'error'   => 'La Sede non è stata ripristinata, si prega di riprovare',
        'success' => 'La Sede è stata ripristinata con successo.'
    ),

    'delete' => array(
        'confirm'   	=> 'Sei sicuro di voler cancellare questa Sede?',
        'error'   => 'C\'è stato un problema nell\'eliminare la Sede. Riprova.',
        'success' => 'Sede eliminata con successo.'
    )

);
