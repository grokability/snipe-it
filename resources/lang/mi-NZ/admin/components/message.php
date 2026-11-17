<?php

return [

    'does_not_exist' => 'Kāore i te tīariari te wae.',

    'create' => [
        'error' => 'Kāore i hangahia te waehanga, tēnā whakamātau anō.',
        'success' => 'Kua pai te hanga o te wae.',
    ],

    'update' => [
        'error' => 'Kāore i whakahoutia te wāhanga, tēnā whakamātau anō',
        'success' => 'Kua whakahoutia te mea.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Kei te hiahia koe ki te muku i tenei waahanga?',
        'error' => 'He raruraru kei te whakakore i te waahanga. Tena ngana ano.',
        'success' => 'I mukua te wāhanga.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Kāore i tutakina te wāhanga, tēnā whakamātau anō',
        'success' => 'Kua tohua te Waeine.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Kaore i whakarereke nga mara, naore i whakahoutia.',
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
        'error' => 'Kāore i whakauruhia te wāhanga, tēnā whakamātau anō',
        'success' => 'Kua takitakihia te Waeine.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
    ],

];
