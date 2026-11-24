<?php

return [

    'does_not_exist' => 'Tokios būsenos žymos nėra.',
    'deleted_label' => 'Ištrinta būsenos žyma',
    'assoc_assets' => 'Ši būsenos žyma šiuo metu yra susieta bent su vienu turto vienetu ir negali būti ištrinta. Atnaujinkite savo turtą, kad nebebūtų sąsajos su šia būsenos žyma, ir bandykite dar kartą. ',

    'create' => [
        'error' => 'Būsenos žyma nebuvo sukurta, bandykite dar kartą.',
        'success' => 'Būsenos žyma sėkmingai sukurta.',
    ],

    'update' => [
        'error' => 'Būsenos žyma nebuvo atnaujinta, bandykite dar kartą',
        'success' => 'Būsenos žyma sėkmingai atnaujinta.',
    ],

    'delete' => [
        'confirm' => 'Ar tikrai norite ištrinti šią būsenos žymą?',
        'error' => 'Bandant ištrinti būsenos žymą įvyko klaida. Bandykite dar kartą.',
        'success' => 'Būsenos žyma sėkmingai ištrinta.',
        'bulk_success' => 'Būsenos žyma sėkmingai ištrinta.|Būsenos žymos (:count) sėkmingai ištrintos.',
        'partial_success' => 'Būsenos žyma sėkmingai ištrinta. Daugiau informacijos rasite žemiau. | Būsenos žymos (:count) buvo sėkmingai ištrintos. Daugiau informacijos rasite žemiau.',
    ],

    'help' => [
        'undeployable' => 'Šis turtas negali būti niekam priskiriamas.',
        'deployable' => 'Šis turtas gali būti išduodamas. Išdavus turtą, jo meta būsena pasikeis į <i class="fas fa-circle text-blue"></i> <strong>Naudojamas</strong>.',
        'archived' => 'Šio turto negalima išduoti, jis bus matomas tik rodinyje „Archyvuotas“. Ši būsena naudinga, norint išsaugoti duomenis apie turtą biudžeto planavimo ar istoriniais tikslais, tačiau pašalinant jį iš kasdienių turto sąrašų.',
        'pending' => 'Šis turtas dar negali būti kam nors priskirtas. Ši būsena dažniausiai naudojama, kai turtas yra perduotas remontui, bet planuojama jį sugrąžinti į apyvartą.',
    ],

];
