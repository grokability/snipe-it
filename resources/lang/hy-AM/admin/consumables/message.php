<?php

return [

    'invalid_category_type' => 'Կատեգորիան պետք է լինի սպառվող նյութերի կատեգորիա։',
    'does_not_exist' => 'Սպառվող նյութը գոյություն չունի։',

    'create' => [
        'error' => 'Սպառվող նյութը չի ստեղծվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Սպառվող նյութը հաջողությամբ ստեղծվել է։',
    ],

    'update' => [
        'error' => 'Սպառվող նյութը չի թարմացվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Սպառվող նյութը հաջողությամբ թարմացվել է։',
    ],

    'delete' => [
        'confirm' => 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս սպառվող նյութը։',
        'error' => 'Սպառվող նյութը ջնջելիս խնդիր առաջացավ։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Սպառվող նյութը հաջողությամբ ջնջվել է։',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Սպառվող նյութը դուրս չի տրվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Սպառվող նյութը հաջողությամբ դուրս է տրվել։',
        'user_does_not_exist' => 'Այդ օգտատերը անվավեր է։ Խնդրում ենք կրկին փորձել։',
        'unavailable' => 'Այս դուրսգրման համար բավարար սպառվող նյութեր չկան։ Խնդրում ենք ստուգել մնացած քանակը։',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Սպառվող նյութը չի վերադարձվել։ Խնդրում ենք կրկին փորձել։',
        'success' => 'Սպառվող նյութը հաջողությամբ վերադարձվել է։',
        'user_does_not_exist' => 'Այդ օգտատերը անվավեր է։ Խնդրում ենք կրկին փորձել։',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Դաշտեր չեն փոփոխվել, ուստի ոչինչ չի թարմացվել։',
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
