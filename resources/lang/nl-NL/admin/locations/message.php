<?php

return [

    'does_not_exist' => 'Locatie bestaat niet.',
    'assoc_users' => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets' => 'Deze locatie is momenteel gekoppeld met tenminste één asset en kan hierdoor niet worden verwijderd. Update je assets die niet meer bij deze locatie en probeer het opnieuw. ',
    'assoc_child_loc' => 'Deze locatie is momenteen de ouder van ten minste één kind locatie en kan hierdoor niet worden verwijderd. Update je locaties bij die niet meer naar deze locatie verwijzen en probeer het opnieuw. ',
    'assigned_assets' => 'Toegewezen activa',
    'current_location' => 'Huidige locatie',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',

    'create' => [
        'error' => 'Locatie is niet aangemaakt, probeer het opnieuw.',
        'success' => 'Locatie is met succes aangemaakt.',
    ],

    'update' => [
        'error' => 'Locatie is niet gewijzigd, probeer het opnieuw',
        'success' => 'Locatie is met succes gewijzigd.',
    ],

    'restore' => [
        'error' => 'Locatie is niet hersteld, probeer het opnieuw',
        'success' => 'Locatie hersteld.',
    ],

    'delete' => [
        'confirm' => 'Weet je het zeker dat je deze locatie wilt verwijderen?',
        'error' => 'Er was een probleem met het verwijderen van deze locatie. Probeer het opnieuw.',
        'success' => 'De locatie is met succes verwijderd.',
    ],

];
