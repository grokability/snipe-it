<?php

return [

    'update' => [
        'error' => 'Atnaujinant įvyko klaida. ',
        'success' => 'Nustatymai sėkmingai atnaujinti.',
    ],
    'backup' => [
        'delete_confirm' => 'Ar tikrai norite ištrinti atsarginę kopiją? Šis veiksmas negrįžtamas. ',
        'file_deleted' => 'Atsarginė kopija sėkmingai ištrinta. ',
        'generated' => 'Atsarginė kopija sėkmingai sukurta.',
        'file_not_found' => 'Šio atsarginės kopijos failo serveryje rasti nepavyko.',
        'restore_warning' => 'Taip, atkurti. Suprantu, kad tai perrašys visus šiuo metu duomenų bazėje esančius duomenis. Taip pat, kad bus atjungti visi esami naudotojai (įskaitant mane).',
        'restore_confirm' => 'Ar tikrai norite atkurti savo duomenų bazę iš :filename?',
    ],
    'restore' => [
        'success' => 'Jūsų sistemos atsarginė kopija buvo atkurta. Prisijunkite iš naujo.',
    ],
    'purge' => [
        'error' => 'Valymo metu įvyko klaida. ',
        'validation_failed' => 'Jūsų įvestas išvalymo patvirtinimas yra neteisingas. Patvirtinimo lauke įveskite žodį „DELETE“.',
        'success' => 'Anksčiau panaikinti įrašai sėkmingai pašalinti.',
    ],
    'mail' => [
        'sending' => 'Siunčiamas bandomasis el. laiškas...',
        'success' => 'El. laiškas išsiųstas!',
        'error' => 'El. laiško išsiųsti nepavyko.',
        'additional' => 'Nėra jokio papildomo klaidos pranešimo. Patikrinkite pašto nustatymus ir programos žurnalą.',
    ],
    'ldap' => [
        'testing' => 'Tikrinamas LDAP ryšys, susiejimas ir užklausos...',
        '500' => '500 serverio klaida. Norėdami gauti daugiau informacijos, patikrinkite savo serverio žurnalus.',
        'error' => 'Kažkas ne taip :(',
        'sync_success' => '10 naudotojų, gautų iš LDAP serverio, pagal jūsų nustatymus:',
        'testing_authentication' => 'Tikrinamas LDAP autentifikavimas...',
        'authentication_success' => 'Naudotojas sėkmingai atpažintas naudojant LDAP!',
    ],
    'labels' => [
        'null_template' => 'Etiketės šablonas nerastas. Pasirinkite šabloną.',
    ],
    'webhook' => [
        'sending' => ':app siunčiamas bandomasis pranešimas...',
        'success' => 'Jūsų :webhook_name integracija veikia!',
        'success_pt1' => 'Pavyko! Patikrink ',
        'success_pt2' => ' bandomojo pranešimo kanalą ir spustelėkite IŠSAUGOTI, kad išsaugotumėte nustatymus.',
        '500' => '500 serverio klaida.',
        'error' => 'Kažkas ne taip. :app atsakė: :error_message',
        'error_redirect' => 'KLAIDA: 301/302 :endpoint rodo peradresavimą. Saugumo sumetimais peradresavimų nevykdome. Naudokite tikrąjį galinį tašką.',
        'error_misc' => 'Kažkas ne taip. :( ',
        'webhook_fail' => ' „Webhook“ pranešimas nepavyko: patikrinkite ar URL vis dar galioja.',
        'webhook_channel_not_found' => ' „webhook“ kanalas nerastas.',
        'ms_teams_deprecation' => 'Pasirinktas „Microsoft Teams“ „webhook“ URL bus nebenaudojamas nuo 2025 m. gruodžio 31 d. Naudokite darbo eigos URL. „Microsoft“ dokumentaciją apie darbo eigos kūrimą galite rasti <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank">čia.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Jūsų nustatymai nebuvo išsaugoti.',
        'mismatch' => 'Duomenų bazėje yra 1 elementas, į kurį reikia atkreipti dėmesį, prieš įjungiant vietų susiejimą.|Duomenų bazėje yra :count elementai (-ų), į kuriuos reikia atkreipti dėmesį, prieš įjungiant vietų susiejimą.',
    ],
    'oauth' => [
        'token_revoked' => 'Asmeninis prieigos raktas sėkmingai atšauktas.',
        'token_unrevoked' => 'Asmeninis prieigos raktas sėkmingai atkurtas.',
        'token_not_found' => 'Tokio asmeninio prieigos rakto rasti nepavyko.',
        'token_revoke_error' => 'Atšaukiant prieigos raktą įvyko klaida.',
        'token_unrevoke_error' => 'Atkuriant prieigos raktą įvyko klaida.',
        'client_created' => 'OAuth klientas sėkmingai sukurtas.',
        'client_updated' => 'OAuth klientas sėkmingai atnaujintas.',
        'client_deleted' => 'OAuth klientas sėkmingai ištrintas.',
        'client_revoked' => 'OAuth klientas sėkmingai atšauktas.',
        'client_unrevoked' => 'OAuth klientas sėkmingai atkurtas.',
        'client_not_found' => 'Tokio OAuth kliento rasti nepavyko.',
        'token_deleted' => 'Prieigos raktas sėkmingai atšauktas.',
        'client_delete_denied' => 'Neturite teisių ištrinti šio kliento.',
        'client_edit_denied' => 'Neturite teisių redaguoti šio kliento.',
        'token_delete_denied' => 'Neturite teisių atšaukti šio prieigos rakto.',
    ],
];
