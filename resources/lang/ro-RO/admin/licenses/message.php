<?php

return array(

    'does_not_exist' => 'Licența nu există sau nu aveți permisiunea de a o vizualiza.',
    'user_does_not_exist' => 'Utilizatorul nu există sau nu aveți permisiunea de a le vizualiza.',
    'asset_does_not_exist' 	=> 'Activul pe care incercati sa-l asociati cu aceasta licenta nu exista.',
    'owner_doesnt_match_asset' => 'Activul pe care incercati sa-l asociati cu aceasta licenta apartine unei alte persoane decat cea selectata.',
    'assoc_users'	 => 'Aceasta licenta este momentan predata catre un utilizator si nu poate fi stearsa. Va rugam verificati licenta mai intai si dupa incercati s-o stergeti iar. ',
    'select_asset_or_person' => 'Trebuie să selectați un material sau un utilizator, dar nu ambele.',
    'not_found' => 'Licența nu a fost găsită',
    'seats_available' => ':seat_count locuri disponibile',


    'create' => array(
        'error'   => 'Licenta nu a fost creata, va rugam incercati iar.',
        'success' => 'Licenta a fost creata.'
    ),

    'deletefile' => array(
        'error'   => 'Fisierul n-a fost sters. Incercati iar.',
        'success' => 'Fisierul a fost sters.',
    ),

    'upload' => array(
        'error'   => 'Fisierul/Fisierele nu au fost uploadate. Incecati iar.',
        'success' => 'Fisierul/Fisierele au fost uploadate.',
        'nofiles' => 'Nu ați selectat niciun fișier pentru încărcare sau fișierul pe care încercați să îl încărcați este prea mare',
        'invalidfiles' => 'Unul sau mai multe fișiere este prea mare sau este un tip de fișier care nu este permis. Tipurile de fișiere permise sunt png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml și lic.',
    ),

    'update' => array(
        'error'   => 'Licenta nu a fost actualizata, va rugam incercati iar',
        'success' => 'Licenta a fost actualizata.'
    ),

    'delete' => array(
        'confirm'   => 'Sunteti sigur ca doriti sa stergeti aceasta licenta?',
        'error'   => 'A aparut o problema la stergerea licentei. Va rugam sa incercati iar.',
        'success' => 'Licenta a fost stearsa.'
    ),

    'checkout' => array(
        'error'   => 'A aparut o problema la predarea licentei. Va rugam incercati iar.',
        'success' => 'Licenta a fost predata',
        'not_enough_seats' => 'Nu sunt disponibile suficiente locuri de licență pentru checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'A aparut o problema la primirea licentei. Va rugam incercati iar.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Licenta a fost primita'
    ),

);
