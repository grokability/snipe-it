<?php

return [

    'update' => [
        'error'                 => 'Došlo je do pogreške prilikom ažuriranja. ',
        'success'               => 'Postavke su uspešno ažurirane.',
    ],
    'backup' => [
        'delete_confirm'        => 'Jeste li sigurni da želite izbrisati tu backup datoteku? Ova se akcija ne može poništiti. ',
        'file_deleted'          => 'Sigurnosna kopija datoteke je uspešno izbrisana. ',
        'generated'             => 'Nova sigurnosna kopija datoteke uspešno je kreirana.',
        'file_not_found'        => 'Sigurnosna kopija datoteke nije na serveru.',
        'restore_warning'       => 'Da, vrati. Potvrđujem da će ovo zameniti sve postojeće podatke koji se trenutno nalaze u bazi podataka. Ovo će takođe odjaviti sve vaše postojeće korisnike (uključujući i Vas).',
        'restore_confirm'       => 'Da li ste sigurni da želite da vratite svoju bazu podataka sa :filename?'
    ],
    'purge' => [
        'error'     => 'Došlo je do pogreške prilikom brisanja. ',
        'validation_failed'     => 'Vaša potvrda o brisanju nije ispravna. Upišite reč "DELETE" u okvir potvrde.',
        'success'               => 'Zapisi su uspešno i trajno obrisani.',
    ],
    'mail' => [
        'sending' => 'Slanje test e-pošte...',
        'success' => 'Pošta poslata!',
        'error' => 'Pošta ne može biti poslata.',
        'additional' => 'Nije navedena dodatna poruka o grešci. Proverite podešavanja pošte i dnevnik aplikacije.'
    ],
    'ldap' => [
        'testing' => 'Testiranje LDAP veze, vezivanja i upita...',
        '500' => '500 Greška servera. Molimo proverite evidenciju vašeg servera za više informacija.',
        'error' => 'Nešto nije u redu :(',
        'sync_success' => 'Uzorak od 10 korisnika vraćenih sa LDAP servera na osnovu vaših podešavanja:',
        'testing_authentication' => 'Testiranje LDAP autentifikacije...',
        'authentication_success' => 'Autentifikacija korisnika na LDAP-u je uspešna!'
    ],
    'webhook' => [
        'sending' => 'Slanje :app probne poruke...',
        'success_pt1' => 'Uspešno! Proverite ',
        'success_pt2' => ' kanal za vašu probnu poruku i obavezno kliknite na SAČUVAJ ispod da biste sačuvali svoja podešavanja.',
        '500' => '500 Greška servera.',
        'error' => 'Nešto nije u redu. :app je adgovorila sa: :error_message',
        'error_misc' => 'Nešto nije u redu. :( ',
    ]
];
