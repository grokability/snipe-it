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
    'restore' => [
        'success'               => 'Rezervna kopija vašeg sistema je povraćena. Molim vas prijavite se ponovo.'
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
    'labels' => [
        'null_template' => 'Nije pronađen šablon oznake. Molim vas izaberite šablon.',
        ],
    'webhook' => [
        'sending' => 'Slanje :app probne poruke...',
        'success' => 'Vaša :webhook_name integracija funkcioniše!',
        'success_pt1' => 'Uspešno! Proverite ',
        'success_pt2' => ' kanal za vašu probnu poruku i obavezno kliknite na SAČUVAJ ispod da biste sačuvali svoja podešavanja.',
        '500' => '500 Greška servera.',
        'error' => 'Nešto nije u redu. :app je adgovorila sa: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint vraća preusmerenje. Zbog bezbednosnih razloga, mi ne sledimo preusmerenja. Molim vas koristite direktnu krajnju tačku.',
        'error_misc' => 'Nešto nije u redu. :( ',
        'webhook_fail' => ' neuspelo obaveštavanje putem veb zakačke: Proverite da li je URL i dalje validan.',
        'webhook_channel_not_found' => ' kanal veb zakačke nije pronađen.',
        'ms_teams_deprecation' => 'Izabrana adresa veb zakačke za Microsoft Teams će zastariti 31. decembra 2025. Molim vas koristite adresa radnog toka. Microsoft-ovu dokumentaciju za kreiranje radnog toka možete pronaći <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">ovde.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Vaša podešavanja nisu sačuvana.',
        'mismatch' => 'Postoji 1 stavka u bazi podataka koja iziskuje vašu pažnju pre uključivanja opsega lokacije.|Postoji :count stavki u bazi podataka koje iziskuju vašu pažnju pre uključivanja opsega lokacije.',
    ],
];
