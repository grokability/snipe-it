<?php

return array(

    'does_not_exist' => 'Licența nu există sau nu ai permisiunea să o vizualizezi.',
    'user_does_not_exist' => 'Utilizatorul nu există sau nu ai permisiunea să-l vizualizezi.',
    'asset_does_not_exist'  => 'Activul pe care încerci să-l asociezi cu această licență nu există.',
    'owner_doesnt_match_asset' => 'Activul pe care încerci să-l asociezi cu această licență aparține unei alte persoane decât cea selectată în meniul "alocat către".',
    'assoc_users'   => 'Această licență este în prezent alocată unui utilizator și nu poate fi ștearsă. Te rog să înregistrezi mai întâi returnarea licenței, apoi să încerci din nou să o ștergi.',
    'select_asset_or_person' => 'Trebuie să selectezi un activ sau un utilizator, dar nu ambele.',
    'not_found' => 'Licența nu a fost găsită',
    'seats_available' => ':seat_count utilizatori disponibili',


    'create' => array(
        'error'   => 'Licența nu a fost creată, te rog să încerci din nou.',
        'success' => 'Licența a fost creată cu succes.'
    ),

    'deletefile' => array(
        'error'   => 'Fișierul nu a fost șters. Te rog să încerci din nou.',
        'success' => 'Fișierul a fost șters cu succes.',
    ),

    'upload' => array(
        'error'   => 'Fișierul/fișierele nu au fost încărcate. Te rog să încerci din nou.',
        'success' => 'Fișierul/fișierele au fost încărcate cu succes.',
        'nofiles' => 'Nu ai selectat niciun fișier pentru încărcare, sau fișierul pe care încerci să-l încarci este prea mare',
        'invalidfiles' => 'Unul sau mai multe dintre fișierele tale sunt prea mari sau au un tip de fișier nepermis. Tipurile de fișiere permise sunt png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml și lic.',
    ),

    'update' => array(
        'error'   => 'Licența nu a fost actualizată, te rog să încerci din nou',
        'success' => 'Licența a fost actualizată cu succes.'
    ),

    'delete' => array(
        'confirm'   => 'Ești sigur că dorești să ștergi această licență?',
        'error'   => 'A apărut o problemă la ștergerea licenței. Te rog să încerci din nou.',
        'success' => 'Licența a fost ștearsă cu succes.'
    ),

    'checkout' => array(
        'error'   => 'A apărut o problemă la alocarea licenței. Te rog să încerci din nou.',
        'success' => 'Licența a fost alocată cu succes',
        'not_enough_seats' => 'Nu sunt suficienți utilizatori disponibili pentru alocare',
        'mismatch' => 'Utilizatorul licenței furnizate nu corespunde licenței',
        'unavailable' => 'Acest utilizator nu este disponibil pentru alocare.',
    ),

    'checkin' => array(
        'error'   => 'A apărut o problemă la returnarea licenței. Te rog să încerci din nou.',
        'not_reassignable' => 'Licența nu este realocabilă',
        'success' => 'Licența a fost returnată cu succes'
    ),

);