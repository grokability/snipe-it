<?php

return [

    'does_not_exist' => 'கூறு இல்லை.',

    'create' => [
        'error' => 'கூறு உருவாக்கப்படவில்லை, தயவுசெய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கூறு வெற்றிகரமாக உருவாக்கப்பட்டது.',
    ],

    'update' => [
        'error' => 'கூறு புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'உபகரணமானது வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'நிச்சயமாக இந்த உறுப்பு நீக்க வேண்டுமா?',
        'error' => 'கூறு நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கூறு வெற்றிகரமாக நீக்கப்பட்டது.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'உபகரணத்தை சோதிக்க முடியவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'உபகரணமானது வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'unavailable' => 'Not enough components remaining: :remaining remaining, :requested requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'எந்த துறைகளும் மாற்றப்படவில்லை, அதனால் எதுவும் புதுப்பிக்கப்படவில்லை.',
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
        'error' => 'உபகரணத்தை சோதிக்க முடியவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'உபகரணமானது வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
    ],

];
