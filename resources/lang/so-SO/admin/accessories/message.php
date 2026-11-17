<?php

return [

    'does_not_exist' => 'Mulxaqa lasoo raaciyay [:aqoonsiga] ma ahan mid jira.',
    'not_found' => 'Qalabkaas lama helin.',
    'assoc_users' => 'Qalabkan waxa uu hadda hayaa :count walxo la hubiyay isticmaalayaasha Fadlan iska hubi agabka oo isku day markale. ',

    'create' => [
        'error' => 'Qalabka lama abuurin, fadlan isku day mar kale.',
        'success' => 'Mulxaqa lasoo raaciyay waa lagu guuleystay in la sameeyo.',
    ],

    'update' => [
        'error' => 'Qalabka lama cusboonaysiin, fadlan isku day mar kale',
        'success' => 'Mulxaqa lasoo raaciyay waa lagu guuleystay in la sameeyo.',
    ],

    'delete' => [
        'confirm' => 'Ma hubtaa inaad rabto inaad tirtirto qalabkan?',
        'error' => 'Waxaa jirtay xaalad tirtireysay mulxaqa. Fadlan isku day mar kale.',
        'success' => 'Mulxaqa lasoo raaciyay waa lagu guuleystay in la tir-tiro.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Agabka lama hubin, fadlan isku day mar kale',
        'success' => 'Mulxaqa lasoo raaciyay waa lagu guuleystay in la hubiyo.',
        'unavailable' => 'Agabka looma hayo hubinta Hubi tirada la heli karo',
        'user_does_not_exist' => 'Isticmaalahaas maahan mid jira. Fadlan isku day mar kale.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'Agabka lama hubin, fadlan isku day mar kale',
        'success' => 'Mulxaqa waa lagu guuleystay in la hubiyo.',
        'user_does_not_exist' => 'Isticmaalahaasi mahan mid jira. Fadlan isku day mar kale.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Wax feilds ah lama beddelin, markaa waxba lama cusboonaysiin.',
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
