<?php

return array(

    'deleted' => 'Fornitore eliminato',
    'does_not_exist' => 'Il fornitore non esiste.',


    'create' => array(
        'error'   => 'Il fornitore non è stato creato, si prega di riprovare.',
        'success' => 'Fornitore creato con successo.'
    ),

    'update' => array(
        'error'   => 'Il fornitore non è stato aggiornato, si prega di riprovare',
        'success' => 'Fornitore aggiornato con successo.'
    ),

    'delete' => array(
        'confirm'   => 'Sei sicuro di voler cancellare questo fornitore?',
        'error'   => 'C\'è stato un problema nell\'eliminazione del fornitore. Riprova.',
        'success' => 'Fornitore eliminato con successo.',
        'assoc_assets'	 => 'Questo fornitore è attualmente associato ad almeno un modello e non può essere eliminato. Si prega di aggiornare i modelli di riferimento e riprovare.',
        'assoc_licenses'	 => 'Questo fornitore è attualmente associato a :licenses_count licenze e non può essere eliminato. Si prega di aggiornare le licenze in modo che non si colleghino a questo fornitore e riprova. ',
        'assoc_maintenances'	 => 'Questo fornitore è associato con :maintenances_count manutenzione/i e quindi non può essere cancellato. Aggiorna le manutenzioni dei beni per fare in modo che non referenzino questo fornitore e riprova. ',
    )

);
