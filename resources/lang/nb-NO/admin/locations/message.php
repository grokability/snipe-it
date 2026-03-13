<?php

return array(

    'does_not_exist' => 'Lokasjon eksisterer ikke.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Lokasjonen er tilknyttet minst en eiendel og kan ikke slettes. Oppdater dine eiendeler slik at de ikke refererer til denne lokasjonen, og prøv igjen. ',
    'assoc_child_loc'	 => 'Lokasjonen er overordnet til minst en underlokasjon og kan ikke slettes. Oppdater din lokasjoner til å ikke referere til denne lokasjonen, og prøv igjen. ',
    'assigned_assets' => 'Tildelte ressurser',
    'current_location' => 'Gjeldende plassering',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Lokasjon ble ikke opprettet, prøv igjen.',
        'success' => 'Vellykket opprettelse av lokasjon.'
    ),

    'update' => array(
        'error'   => 'Lokasjon ble ikke oppdatert, prøv igjen',
        'success' => 'Vellykket oppdatering av plassering.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Er du sikker på at du vil slette denne plasseringen?',
        'error'   => 'Det oppstod et problem under sletting av plassering. Vennligst prøv igjen.',
        'success' => 'Vellykket sletting av plassering.'
    )

);
