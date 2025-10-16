<?php

return array(

    'does_not_exist' => 'Lokacija ne postoji.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Ta je lokacija trenutačno povezana s barem jednim resursom i ne može se izbrisati. Ažurirajte svoju imovinu da više ne referira na tu lokaciju i pokušajte ponovno.',
    'assoc_child_loc'	 => 'Ta je lokacija trenutačno roditelj najmanje jedne lokacije za djecu i ne može se izbrisati. Ažurirajte svoje lokacije da više ne referiraju ovu lokaciju i pokušajte ponovo.',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Lokacija nije izrađena, pokušajte ponovo.',
        'success' => 'Lokacija je uspješno izrađena.'
    ),

    'update' => array(
        'error'   => 'Lokacija nije ažurirana, pokušajte ponovo',
        'success' => 'Lokacija je uspješno ažurirana.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Jeste li sigurni da želite izbrisati tu lokaciju?',
        'error'   => 'Došlo je do problema s brisanjem lokacije. Molim te pokušaj ponovno.',
        'success' => 'Lokacija je uspješno obrisana.'
    )

);
