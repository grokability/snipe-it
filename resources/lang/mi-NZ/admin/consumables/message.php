<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Kaore e taea te whakamahi.',

    'create' => [
        'error' => 'Kāore i whakamahia te whakamahinga, tēnā whakamātau anō.',
        'success' => 'Ko te whakamahinga i hanga angitu.',
    ],

    'update' => [
        'error' => 'Kāore i whakamahia te whakamahinga, tēnā whakamātau anō',
        'success' => 'Ko te whakamahinga kua whakahoutia pai.',
    ],

    'delete' => [
        'confirm' => 'Kei te hiahia koe ki te muku i tenei whakamahinga?',
        'error' => 'He raru kei te whakakore i te kai. Tena ngana ano.',
        'success' => 'Kua mukua te kaihoko.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Kāore i whakamahia te whakamahinga, tēnā whakamātau anō',
        'success' => 'He pai te tirotiro i te whakamahinga.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
        'unavailable' => 'There are not enough consumables for this checkout. Please check the quantity left. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Kāore i whakamahia te whakamahinga, tēnā whakamātau anō',
        'success' => 'Kua tohua te whakamahinga i te pai.',
        'user_does_not_exist' => 'He muhu te kaiwhakamahi. Tena ngana ano.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Kaore i whakarereke nga mara, naore i whakahoutia.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
