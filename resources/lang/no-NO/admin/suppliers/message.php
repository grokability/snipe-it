<?php

return array(

    'deleted' => 'Slettet leverandør',
    'does_not_exist' => 'Leverandør finnes ikke.',


    'create' => array(
        'error'   => 'Leverandør ble ikke opprettet. Prøv igjen.',
        'success' => 'Opprettelse av leverandør vellykket.'
    ),

    'update' => array(
        'error'   => 'Leverandør ble ikke oppdatert. Prøv igjen',
        'success' => 'Oppdatering av leverandør vellykket.'
    ),

    'delete' => array(
        'confirm'   => 'Er du sikker på at du vil slette denne leverandøren?',
        'error'   => 'Det oppstod et problem under sletting av leverandør. Prøv igjen.',
        'success' => 'Sletting av leverandør vellykket.',
        'assoc_assets'	 => 'Denne leverandøren er tilknyttet med :asset_count eiendel(er), og kan ikke slettes. Oppdater eiendelene dine til ikke å bruke denne leverandøren og prøv igjen. ',
        'assoc_licenses'	 => 'Denne leverandøren er tilknyttet med :licenses_count lisens(er), og kan ikke slettes. Oppdater lisensene dine til ikke å bruke denne leverandøren og prøv igjen. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
