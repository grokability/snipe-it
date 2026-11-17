<?php

return [

    'does_not_exist' => 'Qeyb ahaan majirto.',

    'create' => [
        'error' => 'Qayb lama abuurin, fadlan isku day mar kale.',
        'success' => 'Qayb ahaan waa lagu guuleystay in la sameeyo.',
    ],

    'update' => [
        'error' => 'Qaybaha lama cusboonaysiin, fadlan isku day mar kale',
        'success' => 'Qeyb ahaan waa lagu guuleystay in la cusbooneysiiyo.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Ma hubtaa inaad rabto inaad tirtirto qaybtan?',
        'error' => 'Waxaa jirtay arrin tir-tireysay qaybta. Fadlan isku day mar kale.',
        'success' => 'Qaybta waa lagu guuleystay in la tir-tiro.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'Qaybaha lama hubin, fadlan isku day mar kale',
        'success' => 'Qaybta waa lagu guuleystay in la hubiyo.',
        'user_does_not_exist' => 'Isticmaalahaasi ma ahan mid jira. Fadlan isku day mar kale.',
        'unavailable' => 'Qaybo aan ku filnayn ayaa hadhsan: :remaining hadhay, :requested waa la codsaday ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Wax feilds ah lama beddelin, markaa waxba lama cusboonaysiin.',
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
        'error' => 'Qaybaha lama hubin, fadlan isku day mar kale',
        'success' => 'Qaybta waa lagu guuleystay in la hubiyo.',
        'user_does_not_exist' => 'Isticmaalahaasi ma ahan mid jira. Fadlan isku day mar kale.',
    ],

];
