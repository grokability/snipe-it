<?php

return [

    'update' => [
        'error'                 => 'Med posodabljanjem je prišlo do napake. ',
        'success'               => 'Nastavitve so bile posodobljene uspešno.',
    ],
    'backup' => [
        'delete_confirm'        => 'Ali ste prepričani, da želite izbrisati to varnostno datoteko? To dejanje ni mogoče razveljaviti. ',
        'file_deleted'          => 'Varnostna datoteka je bila uspešno izbrisana. ',
        'generated'             => 'Ustvarjena je bila nova varnostna kopija.',
        'file_not_found'        => 'To varnostno datoteko ni bilo mogoče najti na strežniku.',
        'restore_warning'       => 'Da, obnovi. Potrjujem, da bo to prepisalo vse obstoječe podatke, ki so trenutno v zbirki podatkov. S tem se bodo odjavili tudi vsi vaši obstoječi uporabniki (vključno z vami).',
        'restore_confirm'       => 'Ali ste prepričani, da želite obnoviti svojo bazo podatkov iz :filename?'
    ],
    'restore' => [
        'success'               => 'Varnostna kopija vašega sistema je bila obnovljena. Prosimo, prijavite se znova.'
    ],
    'purge' => [
        'error'     => 'Pri čiščenju je prišlo do napake. ',
        'validation_failed'     => 'Vaša potrditev čiščenja je napačna. V polje za potrditev vnesite besedo »DELETE«.',
        'success'               => 'Izbrisani zapisi so bili uspešno počiščeni.',
    ],
    'mail' => [
        'sending' => 'Pošiljanje testnega e-maila...',
        'success' => 'Pošta poslana!',
        'error' => 'Pošte ni bilo mogoče poslati.',
        'additional' => 'Ni bilo prikazanih dodatnih sporočil o napaki. Preverite nastavitve pošte in dnevnik aplikacije.'
    ],
    'ldap' => [
        'testing' => 'Testing LDAP Connection, Binding & Query ...',
        '500' => '500 Server Error. Za več informacij preverite dnevnike strežnika.',
        'error' => 'Nekaj je šlo narobe :(',
        'sync_success' => 'Vzorec 10 uporabnikov, vrnjenih s strežnika LDAP na podlagi vaših nastavitev:',
        'testing_authentication' => 'Testiranje LDAP Avtentikacije...',
        'authentication_success' => 'Uporabnik se je uspešno avtoriziral z LDAP!'
    ],
    'labels' => [
        'null_template' => 'Predloge oznake ni bilo mogoče najti. Izberite predlogo.',
        ],
    'webhook' => [
        'sending' => 'Pošiljanje :apikacija testirno sporočilo...',
        'success' => 'Tvoj :ime_webhooka integracija deluje!',
        'success_pt1' => 'Uspeh! Preverite ',
        'success_pt2' => ' kanal za testno sporočilo in ne pozabite klikniti SHRANI spodaj, da shranite nastavitve.',
        '500' => '500 strežniška napaka.',
        'error' => 'Nekaj je šlo narobe. :app je odgovoril z: :error_message',
        'error_redirect' => 'NAPAKA: 301/302 :endpoint vrne preusmeritev. Zaradi varnostnih razlogov ne sledimo preusmeritvam. Uporabite dejansko končno točko.',
        'error_misc' => 'Nekaj je šlo narobe. :( ',
        'webhook_fail' => ' obvestilo o spletnem webhook-u ni uspelo: Preverite, ali je URL še vedno veljaven.',
        'webhook_channel_not_found' => ' webhook kanal ni najden.',
        'ms_teams_deprecation' => 'Izbrani URL spletnega kavlja za Microsoft Teams bo 31. decembra 2025 zastarel. Uporabite URL poteka dela. Microsoftovo dokumentacijo o ustvarjanju poteka dela najdete <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> tukaj.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Vaše nastavitve niso bile shranjene.',
        'mismatch' => 'V zbirki podatkov je 1 element, ki potrebuje vašo pozornost, preden lahko omogočite določanje obsega lokacije.|V zbirki podatkov je :count elementov, ki potrebujejo vašo pozornost, preden lahko omogočite določanje obsega lokacije.',
    ],
];
