<?php

return [

    'does_not_exist' => 'Tokios licencijos nėra arba jūs neturite teisės ją peržiūrėti.',
    'user_does_not_exist' => 'Tokio naudotojo nėra arba jūs neturite teisės jo peržiūrėti.',
    'asset_does_not_exist' => 'Tokio turto, kurį bandote susieti su šia licencija, nėra.',
    'owner_doesnt_match_asset' => 'Turtas, kurį bandote susieti su šia licencija, yra išduotas kažkam kitam, o ne asmeniui, pasirinktam iš sąrašo.',
    'assoc_users' => 'Ši licencija šiuo metu yra išduota naudotojui ir negali būti panaikinta. Pirmiausia paimkite licenciją ir tuomet vėl bandykite panaikinti. ',
    'select_asset_or_person' => 'Turite pasirinkti turtą arba naudotoją, bet ne abu.',
    'not_found' => 'Licencija nerasta',
    'seats_available' => 'Liko vietų: :seat_count',

    'create' => [
        'error' => 'Licencija nesukurta, bandykite dar kartą.',
        'success' => 'Licencija sėkmingai sukurta.',
    ],

    'deletefile' => [
        'error' => 'Failas nebuvo panaikintas. Bandykite dar kartą.',
        'success' => 'Failas sėkmingai panaikintas.',
    ],

    'upload' => [
        'error' => 'Failo (-ų) įkelti nepavyko. Bandykite dar kartą.',
        'success' => 'Failas(-ai) sėkmingai įkelti.',
        'nofiles' => 'Nepasirinkote jokio failo įkėlimui arba failas, kurį bandote įkelti, yra per didelis',
        'invalidfiles' => 'Vienas ar keli failai yra per dideli arba neleistino failų formato. Leidžiami failų tipai yra: png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, lic.',
    ],

    'update' => [
        'error' => 'Licencija nebuvo atnaujinta, bandykite dar kartą',
        'success' => 'Licencija sėkmingai atnaujinta.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite panaikinti šią licenciją?',
        'error' => 'Bandant panaikinti licenciją įvyko klaida. Bandykite dar kartą.',
        'success' => 'Licencija sėkmingai panaikinta.',
    ],

    'checkout' => [
        'error' => 'Bandant išduoti licenciją įvyko klaida. Bandykite dar kartą.',
        'success' => 'Licencija sėkmingai išduota',
        'not_enough_seats' => 'Turimų laisvų vietų nepakanka licencijos išdavimui',
        'mismatch' => 'Pateikta licencijos vieta nesutampa su licencija',
        'unavailable' => 'Šios licencijos negalima išduoti.',
        'license_is_inactive' => 'Šios licencijos galiojimas pasibaigęs arba ji yra nutraukta.',
    ],

    'checkin' => [
        'error' => 'Bandant paimti licenciją įvyko klaida. Bandykite dar kartą.',
        'not_reassignable' => 'Vieta buvo panadota',
        'success' => 'Licencija sėkmingai paimta',
    ],

];
