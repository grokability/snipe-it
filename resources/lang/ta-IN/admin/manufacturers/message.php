<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'உற்பத்தியாளர் இல்லை.',
    'assoc_users'	 => 'இந்த உற்பத்தியாளர் தற்போது குறைந்தது ஒரு மாதிரியுடன் தொடர்புடையது மற்றும் நீக்கப்பட முடியாது. இந்த உற்பத்தியாளரை இனி குறிப்பிடுவதற்கு உங்கள் மாதிரியைப் புதுப்பிக்கவும் மீண்டும் முயற்சிக்கவும்.',

    'create' => array(
        'error'   => 'உற்பத்தியாளர் உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்.',
        'success' => 'உற்பத்தியாளர் வெற்றிகரமாக உருவாக்கப்பட்டது.'
    ),

    'update' => array(
        'error'   => 'உற்பத்தியாளர் புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'உற்பத்தியாளர் வெற்றிகரமாக புதுப்பிக்கப்பட்டது.'
    ),

    'restore' => array(
        'error'   => 'Manufacturer was not restored, please try again',
        'success' => 'Manufacturer restored successfully.'
    ),

    'delete' => array(
        'confirm' => 'இந்த உற்பத்தியாளரை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error'   => 'தயாரிப்பாளரை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
