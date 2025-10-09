<?php

return array(

    'does_not_exist' => 'Beliggenhed findes ikke.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Denne placering er i øjeblikket forbundet med mindst ét ​​aktiv og kan ikke slettes. Opdater dine aktiver for ikke længere at henvise til denne placering, og prøv igen.',
    'assoc_child_loc'	 => 'Denne placering er for øjeblikket forælder på mindst et barns placering og kan ikke slettes. Opdater dine placeringer for ikke længere at henvise til denne placering, og prøv igen.',
    'assigned_assets' => 'Tildelte aktiver',
    'current_location' => 'Aktuel lokation',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Placeringen blev ikke oprettet, prøv igen.',
        'success' => 'Placering oprettet med succes.'
    ),

    'update' => array(
        'error'   => 'Placeringen blev ikke opdateret, prøv igen',
        'success' => 'Placering opdateret med succes.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Er du sikker på, at du vil slette denne placering?',
        'error'   => 'Der opstod et problem ved at slette placeringen. Prøv igen.',
        'success' => 'Placeringen blev slettet korrekt.'
    )

);
