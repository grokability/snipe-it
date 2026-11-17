<?php

return [

    'field' => [
        'invalid' => 'Tokio lauko nėra.',
        'already_added' => 'Laukas jau pridėtas',
        'none_selected' => 'Nepasirinktas joks laukas',

        'create' => [
            'error' => 'Laukas nebuvo sukurtas, bandykite dar kartą.',
            'success' => 'Laukas sėkmingai sukurtas.',
            'assoc_success' => 'Laukas sėkmingai įtrauktas į laukų rinkinį.',
        ],

        'update' => [
            'error' => 'Laukas nebuvo atnaujintas, bandykite dar kartą',
            'success' => 'Laukas sėkmingai atnaujintas.',
        ],

        'delete' => [
            'confirm' => 'Ar tikrai norite ištrinti šį lauką?',
            'error' => 'Bandant panaikinti lauką įvyko klaida. Bandykite dar kartą.',
            'success' => 'Laukas sėkmingai panaikintas.',
            'in_use' => 'Laukas yra naudojamas.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Tokio laukų rinkinio nėra',

        'create' => [
            'error' => 'Laukas nebuvo sukurtas, bandykite dar kartą.',
            'success' => 'Laukų rinkinys sėkmingai sukurtas.',
        ],

        'update' => [
            'error' => 'Laukų rinkinys nebuvo atnaujintas, bandykite dar kartą',
            'success' => 'Laukų rinkinys sėkmingai atnaujintas.',
        ],

        'delete' => [
            'confirm' => 'Ar tikrai norite ištrinti šį laukų rinkinį?',
            'error' => 'Bandant panaikinti laukų rinkinį įvyko klaida. Bandykite dar kartą.',
            'success' => 'Laukų rinkinys sėkmingai panaikintas.',
            'in_use' => 'Laukų rinkinys yra naudojamas.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Laukų rinkinys sėkmingai klonuotas.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Klaida tikrinant numatytąsias laukų reikšmes.',

    ],

];
