<?php

return [

    'does_not_exist' => 'Բաղադրիչը գոյություն չունի։',

    'create' => [
        'error' => 'Բաղադրիչը չի ստեղծվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Բաղադրիչը հաջողությամբ ստեղծվել է։',
    ],

    'update' => [
        'error' => 'Բաղադրիչը չի թարմացվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Բաղադրիչը հաջողությամբ թարմացվել է։',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս բաղադրիչը։',
        'error' => 'Բաղադրիչը ջնջելիս խնդիր առաջացավ։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Բաղադրիչը հաջողությամբ ջնջվել է։',
        'error_qty' => 'Այս տեսակի որոշ բաղադրիչներ դեռ դուրս են տրված։ Խնդրում ենք նախ վերադարձնել դրանք և կրկին փորձել։',
    ],

    'checkout' => [
        'error' => 'Բաղադրիչը դուրս չի տրվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Բաղադրիչը հաջողությամբ դուրս է տրվել։',
        'user_does_not_exist' => 'Այդ օգտատերը անվավեր է։ Խնդրում ենք կրկին փորձել։',
        'unavailable' => 'Բավարար բաղադրիչներ չեն մնացել․ մնացել է՝ :remaining, պահանջվել է՝ :requested։',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Դաշտեր չեն փոփոխվել, ուստի ոչինչ չի թարմացվել։',
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
        'error' => 'Բաղադրիչը չի վերադարձվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Բաղադրիչը հաջողությամբ վերադարձվել է։',
        'user_does_not_exist' => 'Այդ օգտատերը անվավեր է։ Խնդրում ենք կրկին փորձել։',
    ],

];
