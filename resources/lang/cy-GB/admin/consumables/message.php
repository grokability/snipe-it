<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Nid yw\'r nwydd traul yn bodoli.',

    'create' => [
        'error' => 'Ni crewyd y nwydd traul, ceisiwch eto o. g. y. dd.',
        'success' => 'Nwydd traul wedi creu yn llwyddiannus.',
    ],

    'update' => [
        'error' => 'Ni diweddarwyd y nwydd traul, ceisiwch eto o. g. y. dd',
        'success' => 'Nwydd traul wedi diweddaru\'n llwyddiannus.',
    ],

    'delete' => [
        'confirm' => 'Ydych chi\'n siwr eich bod eisiau dileu\'r nwydd traul yma?',
        'error' => 'Nid oedd yn bosib dileu\'r nwydd traul. Ceisiwch eto o. g. y. dd.',
        'success' => 'Nwydd traul wedi dileu\'n llwyddiannus.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Nwydd traul heb ei nodi allan, ceisiwch eto o. g. y. dd',
        'success' => 'Nwydd traul wedi nodi allan yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o. g. y. dd.',
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
        'error' => 'Nwydd traul heb ei nodi i fewn, ceisiwch eto o. g. y. dd',
        'success' => 'Nwydd traul wedi nodi i fewn yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o. g. y. dd.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Dim newid mewn manylder, felly dim byd i diweddaru.',
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
