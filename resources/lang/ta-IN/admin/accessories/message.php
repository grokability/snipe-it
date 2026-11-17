<?php

return [

    'does_not_exist' => 'துணைக்கருவி [:id] இல்லை.',
    'not_found' => 'That accessory was not found.',
    'assoc_users' => 'தற்போது இந்த இணைப்பு உள்ளது: பயனர்களை சோதிக்கப்படும் எண்ணைக் கணக்கிடு. ஆபரணங்களை சரிபார்த்து மீண்டும் முயற்சிக்கவும்.',

    'create' => [
        'error' => 'துணை உருவாக்கம் செய்யப்படவில்லை, தயவுசெய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'துணை வெற்றிகரமாக உருவாக்கப்பட்டது.',
    ],

    'update' => [
        'error' => 'இணைப்பு புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'துணை வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
    ],

    'delete' => [
        'confirm' => 'நிச்சயமாக இந்த துணை நீக்க விரும்புகிறீர்களா?',
        'error' => 'துணை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'துணை வெற்றிகரமாக நீக்கப்பட்டது.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'அசெஸரி அவுட் சரிபார்க்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'அசெஸரி வெற்றிகரமாக சோதிக்கப்பட்டது.',
        'unavailable' => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'அசெஸரி சோதிக்கப்படவில்லை, தயவுசெய்து மீண்டும் முயற்சிக்கவும்',
        'success' => 'அசெஸரி வெற்றிகரமாக சரிபார்க்கப்பட்டது.',
        'user_does_not_exist' => 'அந்த பயனர் தவறானது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'எந்த துறைகளும் மாற்றப்படவில்லை, அதனால் எதுவும் புதுப்பிக்கப்படவில்லை.',
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
