<?php

return array(

    'does_not_exist' => 'Sijaintia ei löydy.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Sijaintiin on tällä hetkellä liitettynä vähintään yksi laite, eikä sitä voi poistaa. Poista viittaus sijantiin ja yritä uudelleen. ',
    'assoc_child_loc'	 => 'Tämä sijainti on ylempi toiselle sijainnille eikä sitä voi poistaa. Päivitä sijainnit, jotta et enää viitata tähän sijaintiin ja yritä uudelleen. ',
    'assigned_assets' => 'Luovutetut laitteet',
    'current_location' => 'Nykyinen sijainti',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Sijaintia ei luotu, yritä uudelleen.',
        'success' => 'Sijainti luotiin onnistuneesti.'
    ),

    'update' => array(
        'error'   => 'Sijaintia ei päivitetty, yritä uudelleen',
        'success' => 'Sijainti päivitettiin onnistuneesti.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Oletko varma että haluat poistaa tämän sijainnin?',
        'error'   => 'Sijainnin poistossa tapahtui virhe. Yritä uudelleen.',
        'success' => 'Sijainti poistettiin onnistuneesti.'
    )

);
