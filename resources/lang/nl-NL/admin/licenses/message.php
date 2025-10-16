<?php

return array(

    'does_not_exist' => 'Licentie bestaat niet of je hebt geen toestemming om het te bekijken.',
    'user_does_not_exist' => 'Gebruiker bestaat niet of je hebt geen toestemming om deze te bekijken.',
    'asset_does_not_exist' 	=> 'Het asset dat je probeert te koppelen aan deze licentie bestaat niet.',
    'owner_doesnt_match_asset' => 'Het asset dat je probeert te koppelen aan deze licentie is eigendom van iemand anders dan de persoon die is geselecteerd in de toegewezen aan de dropdown.',
    'assoc_users'	 => 'Deze licentie is momenteel uitgecheckt aan een gebruiker en kan daarom niet worden verwijderd. Controleer je licentie eerst en probeer het later nog eens. ',
    'select_asset_or_person' => 'U moet een asset of een gebruiker selecteren, maar niet beide.',
    'not_found' => 'Licentie niet gevonden',
    'seats_available' => ':seat_count plaatsen beschikbaar',


    'create' => array(
        'error'   => 'Licentie is niet aangemaakt, probeer het opnieuw.',
        'success' => 'Licentie is met succes aangemaakt.'
    ),

    'deletefile' => array(
        'error'   => 'Bestand is niet verwijderd. Probeer het opnieuw.',
        'success' => 'Bestand is met succes verwijderd.',
    ),

    'upload' => array(
        'error'   => 'Bestand(en) zijn niet geüpload. Probeer het opnieuw.',
        'success' => 'Bestand(en) zijn met succes geüpload.',
        'nofiles' => 'Je hebt geen bestanden geselecteerd om te uploaden, of het bestand wat je probeert te uploaden is te groot',
        'invalidfiles' => 'Een of meer van de bestanden is te groot of het bestandstype is niet toegestaan. Toegestane bestandstypes zijn png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml en lic.',
    ),

    'update' => array(
        'error'   => 'Licentie is niet gewijzigd, probeer het opnieuw',
        'success' => 'Licentie is met succes gewijzigd.'
    ),

    'delete' => array(
        'confirm'   => 'Weet je het zeker dat je deze licentie wilt verwijderen?',
        'error'   => 'Er was een probleem tijdens het verwijderen van deze licentie, probeer het opnieuw.',
        'success' => 'De licentie is met succes verwijderd.'
    ),

    'checkout' => array(
        'error'   => 'Er was een probleem met het uitchecken van deze licentie. Probeer het opnieuw.',
        'success' => 'De licentie is met succes uitgecheckt',
        'not_enough_seats' => 'Niet genoeg licentieplaatsen beschikbaar voor de kassa',
        'mismatch' => 'De opgegeven licentie werkplek komt niet overeen met de licentie',
        'unavailable' => 'Deze licentie is niet beschikbaar voor uitchecken.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Er was een probleem met het inchecken van deze licentie. Probeer het opnieuw.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'De licentie is met succes ingecheckt'
    ),

);
