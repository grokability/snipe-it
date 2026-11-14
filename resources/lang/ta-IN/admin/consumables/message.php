<?php

return [

    'invalid_category_type' => 'The category must be a consumable category.',
    'does_not_exist' => 'நுகர்வோர் இல்லை.',

    'create' => [
        'error' => 'நுகர்வோர் உருவாக்கப்படவில்லை, தயவுசெய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'நுகர்வோர் வெற்றிகரமாக உருவாக்கப்பட்டது.',
    ],

    'update' => [
        'error' => 'நுகர்வோர் புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'நுகர்வோர் வெற்றிகரமாக மேம்படுத்தப்பட்டது.',
    ],

    'delete' => [
        'confirm' => 'நீங்கள் இந்த நுகர்வு நீக்க விரும்புகிறீர்களா?',
        'error' => 'நுகர்வு நீக்கம் ஒரு பிரச்சினை இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'நுகர்வோர் வெற்றிகரமாக நீக்கப்பட்டது.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'நுகர்வோர் சரிபார்க்கப்படவில்லை, தயவு செய்து மீண்டும் முயற்சிக்கவும்',
        'success' => 'நுகர்வோர் வெற்றிகரமாக சோதிக்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
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
        'error' => 'நுகர்வோர் சரிபார்க்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'நுகர்வோர் வெற்றிகரமாக சோதிக்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'எந்த துறைகளும் மாற்றப்படவில்லை, அதனால் எதுவும் புதுப்பிக்கப்படவில்லை.',
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
