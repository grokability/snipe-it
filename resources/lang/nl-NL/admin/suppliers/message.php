<?php

return array(

    'deleted' => 'Verwijderde leverancier',
    'does_not_exist' => 'De leverancier bestaat niet.',


    'create' => array(
        'error'   => 'De leverancier is niet aangemaakt, probeer het opnieuw.',
        'success' => 'De leverancier is succesvol aangemaakt.'
    ),

    'update' => array(
        'error'   => 'De leverancier is niet gewijzigd, probeer het opnieuw',
        'success' => 'De leverancier is succesvol aangemaakt.'
    ),

    'delete' => array(
        'confirm'   => 'Ben je zeker dat je de leverancier wil verwijderen?',
        'error'   => 'Er ging iets mis tijdens het verwijderen van de leverancier. Probeer het nog een keer.',
        'success' => 'De leverancier is succesvol verwijderd.',
        'assoc_assets'	 => 'Deze leverancier is momenteel gekoppeld met :asset_count asset(s) en kan hierdoor niet verwijderd worden. Pas je modellen aan zodat deze leverancier niet langer gebruikt wordt en probeer het opnieuw. ',
        'assoc_licenses'	 => 'Deze leverancier is momenteel gekoppeld met :licenses_count licences(s) en kan hierdoor niet verwijderd worden. Pas je modellen aan zodat deze leverancier niet langer gebruikt wordt en probeer het opnieuw. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
