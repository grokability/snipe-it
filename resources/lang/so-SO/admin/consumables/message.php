<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'Wax la isticmaali karo ma jiro.',

    'create' => [
        'error' => 'La isticmaali karo lama abuurin, fadlan isku day mar kale.',
        'success' => 'Isticmaalka waa lagu guuleystay in la abuuro.',
    ],

    'update' => [
        'error' => 'Isticmaalka lama cusboonaysiin, fadlan isku day mar kale',
        'success' => 'Isticmaalka waa lagu guuleystay in la cusbooneysiiyo.',
    ],

    'delete' => [
        'confirm' => 'Ma hubtaa inaad rabto inaad tirtirto alaabtan?',
        'error' => 'Waxaa jirtay arin tireysay isticmaalka alaabta. Fadlan isku day mar kale.',
        'success' => 'Isticmaalka waa lagu guuleystay in la tir-tiro.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Isticmaalka lama hubin, fadlan isku day mar kale',
        'success' => 'Isticmaalka waa lagu guuleystay in la hubiyo.',
        'user_does_not_exist' => 'Isticmaalahaasi ma ahan mid jira. Fadlan isku day mar kale.',
        'unavailable' => 'Ma jiraan alaabooyin ku filan hubintan. Fadlan hubi tirada hadhay ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Isticmaalka lama hubin, fadlan isku day mar kale',
        'success' => 'Isticmaalka waa lagu guuleystay in la hubiyo.',
        'user_does_not_exist' => 'Isticmaalahaasi ma ahan mida jira. Fadlan isku day mar kale.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Wax feilds ah lama beddelin, markaa waxba lama cusboonaysiin.',
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
