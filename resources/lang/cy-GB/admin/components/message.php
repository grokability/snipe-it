<?php

return [

    'does_not_exist' => 'Nid ywr cydran yn bodoli.',

    'create' => [
        'error' => 'Ni crewyd y cydran, ceisiwch eto o. g. y. dd.',
        'success' => 'Cydran wedi creu yn llwyddiannus.',
    ],

    'update' => [
        'error' => 'Ni ddiweddarwyd y cydran, ceisiwch eto o. g. y. dd',
        'success' => 'Diweddarwyd y gydran yn llwyddiannus.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Ydych chi\'n siwr eich bod eisiau dileu\'r cydran yma?',
        'error' => 'Nid oedd yn bosib dileu\'r cydran. Ceisiwch eto o. g. y. dd.',
        'success' => 'Cydran wedi dileu\'n llwyddiannus.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Cydran heb ei nodi allan, ceisiwch eto o. g. y. dd',
        'success' => 'Cydran wedi nodi allan yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o. g. y. dd.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Dim newid mewn manylder, felly dim byd i diweddaru.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'Cydran heb ei nodi i fewn, ceisiwch eto o. g. y. dd',
        'success' => 'Cydran wedi nodi i fewn yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o. g. y. dd.',
    ],

];
