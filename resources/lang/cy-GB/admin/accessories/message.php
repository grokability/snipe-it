<?php

return [

    'does_not_exist' => 'Nid yw\'r ategolyn [:id] yn bodoli.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'Mae\'r ategolyn yma hefo :count eitem wedi nodi allan i defnyddwyr. Nodwch yr ategolion yn ol i fewn ac yna ceisiwch eto. ',

    'create' => [
        'error' => 'Ni crewyd yr ategolyn, ceisiwch eto o.g.y.dd.',
        'success' => 'Ategolyn wedi creu yn llwyddiannus.',
    ],

    'update' => [
        'error' => 'Ni diweddarwyd yr ategolyn, ceisiwch eto o.g.y.dd',
        'success' => 'Diweddarwyd yr ategolyn yn llwyddiannus.',
    ],

    'delete' => [
        'confirm' => 'Ydych chi\'n sicr eich bod eisiau dileu\'r eitem hwn?',
        'error' => 'Nid oedd yn bosib dileu\'r eitem. Ceisiwch eto o.g.y.dd.',
        'success' => 'Ategolyn wedi dileu yn llwyddiannus.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Ategolyn heb ei nodi allan, ceisiwch eto o. g. y. dd',
        'success' => 'Ategolyn wedi nodi allan yn llwyddiannus.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o.g.y.dd.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Nid oedd yn bosib nodi\'r ategolyn i fewn, ceisiwch eto o.g.y.dd',
        'success' => 'Ategolyn wedi nodi i fewn yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o.g.y.dd.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Dim newid mewn manylder, felly dim byd i diweddaru.',
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
    ],
];
