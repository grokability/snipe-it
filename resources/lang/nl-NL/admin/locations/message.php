<?php

return array(

    'does_not_exist' => 'Locatie bestaat niet.',
    'assoc_users'    => 'Deze locatie is momenteel niet verwijderbaar omdat het de locatie is voor ten minste één product of gebruiker, heeft de assets toegewezen of is de bovenliggende locatie van een andere locatie. Update uw gegevens zodat deze locatie niet langer gebruikt wordt en probeer het opnieuw ',
    'assoc_assets'	 => 'Deze locatie is momenteel gekoppeld met tenminste één asset en kan hierdoor niet worden verwijderd. Update je assets die niet meer bij deze locatie en probeer het opnieuw. ',
    'assoc_child_loc'	 => 'Deze locatie is momenteen de ouder van ten minste één kind locatie en kan hierdoor niet worden verwijderd. Update je locaties bij die niet meer naar deze locatie verwijzen en probeer het opnieuw. ',
    'assigned_assets' => 'Toegewezen activa',
    'current_location' => 'Huidige locatie',
    'open_map' => 'Open in :map_provider_icon kaarten',


    'create' => array(
        'error'   => 'Locatie is niet aangemaakt, probeer het opnieuw.',
        'success' => 'Locatie is met succes aangemaakt.'
    ),

    'update' => array(
        'error'   => 'Locatie is niet gewijzigd, probeer het opnieuw',
        'success' => 'Locatie is met succes gewijzigd.'
    ),

    'restore' => array(
        'error'   => 'Locatie is niet hersteld, probeer het opnieuw',
        'success' => 'Locatie hersteld.'
    ),

    'delete' => array(
        'confirm'   	=> 'Weet je het zeker dat je deze locatie wilt verwijderen?',
        'error'   => 'Er was een probleem met het verwijderen van deze locatie. Probeer het opnieuw.',
        'success' => 'De locatie is met succes verwijderd.'
    )

);
