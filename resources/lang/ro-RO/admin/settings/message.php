<?php

return [

    'update' => [
        'error'             => 'A apărut o eroare la actualizare.',
        'success'           => 'Setările au fost actualizate cu succes.',
    ],
    'backup' => [
        'delete_confirm'    => 'Sunteți sigur că doriți să ștergeți acest fișier de backup? Această acțiune nu poate fi anulată.',
        'file_deleted'      => 'Fișierul de backup a fost șters cu succes.',
        'generated'         => 'Un nou fișier de backup a fost creat cu succes.',
        'file_not_found'    => 'Acest fișier de backup nu a putut fi găsit pe server.',
        'restore_warning'   => 'Da, restaurează-l. Recunosc că acest lucru va suprascrie orice date existente în prezent în baza de date. Acest lucru va deconecta, de asemenea, toți utilizatorii existenți (inclusiv pe dumneavoastră).',
        'restore_confirm'   => 'Sunteți sigur că doriți să restaurați baza de date din :filename?'
    ],
    'restore' => [
        'success'           => 'Backup-ul sistemului a fost restaurat. Vă rugăm să vă autentificați din nou.'
    ],
    'purge' => [
        'error'     => 'A apărut o eroare la ștergerea permanentă.',
        'validation_failed' => 'Confirmarea ștergerii permanente este incorectă. Vă rugăm să tastați cuvântul "DELETE" în caseta de confirmare.',
        'success'           => 'Înregistrările șterse au fost curățate cu succes.',
    ],
    'mail' => [
        'sending' => 'Se trimite email de test...',
        'success' => 'Email trimis!',
        'error' => 'Emailul nu a putut fi trimis.',
        'additional' => 'Nu a fost furnizat niciun mesaj de eroare suplimentar. Verificați setările de email și jurnalul aplicației.'
    ],
    'ldap' => [
        'testing' => 'Se testează conexiunea LDAP, legătura și interogarea ...',
        '500' => 'Eroare de server 500. Vă rugăm să verificați jurnalele serverului pentru mai multe informații.',
        'error' => 'Ceva nu a mers bine :(',
        'sync_success' => 'Un eșantion de 10 utilizatori returnat de pe serverul LDAP pe baza setărilor dumneavoastră:',
        'testing_authentication' => 'Se testează autentificarea LDAP...',
        'authentication_success' => 'Utilizator autentificat cu succes în raport cu LDAP!'
    ],
    'labels' => [
        'null_template' => 'Șablonul etichetei nu a fost găsit. Vă rugăm să selectați un șablon.',
        ],
    'webhook' => [
        'sending' => 'Se trimite mesajul de test :app...',
        'success' => 'Integrarea dumneavoastră :webhook_name funcționează!',
        'success_pt1' => 'Succes! Verificați ',
        'success_pt2' => ' canalul pentru mesajul dumneavoastră de test și asigurați-vă că faceți clic pe SALVARE mai jos pentru a stoca setările.',
        '500' => 'Eroare de server 500.',
        'error' => 'Ceva nu a mers bine. :app a răspuns cu: :error_message',
        'error_redirect' => 'EROARE: 301/302 :endpoint returnează o redirecționare. Din motive de securitate, nu urmăm redirecționările. Vă rugăm să utilizați endpoint-ul real.',
        'error_misc' => 'Ceva nu a mers bine. :( ',
        'webhook_fail' => ' notificarea webhook a eșuat: Verificați pentru a vă asigura că URL-ul este încă valid.',
        'webhook_channel_not_found' => ' canalul webhook nu a fost găsit.',
        'ms_teams_deprecation' => 'URL-ul webhook selectat pentru Microsoft Teams va fi depreciat la 31 decembrie 2025. Vă rugăm să utilizați un URL de flux de lucru. Documentația Microsoft privind crearea unui flux de lucru poate fi găsită <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> aici.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Setările dumneavoastră nu au fost salvate.',
        'mismatch' => 'Există 1 element în baza de date care necesită atenția dumneavoastră înainte de a putea activa definirea domeniului locației.|Există :count elemente în baza de date care necesită atenția dumneavoastră înainte de a putea activa definirea domeniului locației.',
    ],
];