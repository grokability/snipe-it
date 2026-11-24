<?php

return [

    'does_not_exist' => 'Աքսեսուար [:id] գոյություն չունի։',
    'not_found' => 'Այս աքսեսուարը չի գտնվել։',
    'assoc_users' => 'Այս աքսեսուարին առկա են :count տրամադրված իրեր. Վերադարձրեք դրանք և փորձեք կրկին։ ',

    'create' => [
        'error' => 'Աքսեսուարը չի ստեղծվել, խնդրում ենք փորձել կրկին։',
        'success' => 'Աքսեսուարը հաջողությամբ ստեղծվեց։',
    ],

    'update' => [
        'error' => 'Աքսեսուարը չի թարմացվել, խնդրում ենք փորձել կրկին',
        'success' => 'Աքսեսուարը հաջողությամբ թարմացվեց։',
    ],

    'delete' => [
        'confirm' => 'Հաստատե՞լ ջնջումը այս աքսեսուարի համար:',
        'error' => 'Աքսեսուարը ջնջել հնարավոր չեղավ. Փորձեք կրկին։',
        'success' => 'Աքսեսուարը հաջողությամբ ջնջվեց։',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Աքսեսուարը չի տրամադրվել, խնդրում ենք փորձել կրկին',
        'success' => 'Աքսեսուարը դուրս գրվեց։',
        'unavailable' => 'Աքսեսուարը դուրս գրելու համար բավարար չէ։ Խնդրում ենք ստուգել հասանելի քանակը',
        'user_does_not_exist' => 'Օգտատերը անվավեր է. Փորձեք կրկին։',
        'checkout_qty' => [
            'lte' => 'Ընդհանուր հասանելի է :number_currently_remaining աքսեսուար, իսկ դուք փորձում եք դուրս գրել :checkout_qty։ Խնդրում ենք փոփոխել դուրս գրվողների քանակը կամ այս աքսեսուարի ընդհանուր պահուստը և փորձել կրկին։',
        ],

    ],

    'checkin' => [
        'error' => 'Աքսեսուարը դուրս գրվեց',
        'success' => 'Աքսեսուարը դուրս գրվեց։',
        'user_does_not_exist' => 'Օգտատերը անվավեր է. Փորձեք կրկին։',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Դաշտեր չեն փոփոխվել, ուստի ոչինչ չի թարմացվել։',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],

        'checkout' => [
            'nothing_selectable' => 'None of the selected accessories can be checked out. Archived accessories and accessories with no remaining stock are excluded.',
            'excluded_notice' => 'The following accessories were excluded from this checkout because they are archived or have no remaining stock:',
            'skipped_archived' => ':name was skipped because it is archived.',
            'skipped_unavailable' => ':name was skipped because no units are currently available.',
            'skipped_company_mismatch' => ':name was skipped because it belongs to a different company (:item_company) than the selected target.',
            'summary' => ':checked_out of :total accessories were checked out.',
        ],
    ],
];
