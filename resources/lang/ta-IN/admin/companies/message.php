<?php

return [
    'does_not_exist' => 'நிறுவனம் இல்லை.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'நிறுவனம் உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்.',
        'success' => 'நிறுவனம் வெற்றிகரமாக உருவாக்கப்பட்டது.',
    ],
    'update' => [
        'error' => 'நிறுவனம் புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'நிறுவனம் வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
    ],
    'delete' => [
        'confirm' => 'இந்த நிறுவனத்தை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error' => 'நிறுவனத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'நிறுவனம் வெற்றிகரமாக நீக்கப்பட்டது.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
