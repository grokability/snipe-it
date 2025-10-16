<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'நீங்கள் இந்த உரிமத்துடன் தொடர்பு கொள்ள முயற்சிக்கும் சொத்து இல்லை.',
    'owner_doesnt_match_asset' => 'நீங்கள் இந்த உரிமத்துடன் தொடர்பு கொள்ள முயற்சிக்கும் சொத்தை சொடுக்கும் ஒதுக்கீட்டில் தேர்ந்தெடுக்கப்பட்ட நபரை விட வேறு ஏதேனும் சொந்தமானது.',
    'assoc_users'	 => 'இந்த உரிமம் தற்போது ஒரு பயனர் வெளியே சோதிக்கப்பட்டது மற்றும் நீக்க முடியாது. முதலில் உரிமம் சரிபார்க்கவும், பின்னர் மீண்டும் நீக்கி முயற்சிக்கவும்.',
    'select_asset_or_person' => 'நீங்கள் ஒரு சொத்து அல்லது ஒரு பயனர் தேர்ந்தெடுக்க வேண்டும், ஆனால் இருவரும் அல்ல.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'உரிமம் உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்.',
        'success' => 'உரிமம் வெற்றிகரமாக உருவாக்கப்பட்டது.'
    ),

    'deletefile' => array(
        'error'   => 'கோப்பு நீக்கப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு வெற்றிகரமாக நீக்கப்பட்டது.',
    ),

    'upload' => array(
        'error'   => 'கோப்பு (கள்) பதிவேற்றப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு (கள்) வெற்றிகரமாக பதிவேற்றப்பட்டது.',
        'nofiles' => 'பதிவேற்றுவதற்கான எந்தவொரு கோப்பையும் நீங்கள் தேர்ந்தெடுக்கவில்லை அல்லது நீங்கள் பதிவேற்ற முயற்சிக்கும் கோப்பு மிகப்பெரியது',
        'invalidfiles' => 'உங்கள் கோப்புகளில் ஒன்று அல்லது அதற்கு மேற்பட்டவை மிக அதிகமாக உள்ளது அல்லது அனுமதிக்கப்படாத கோப்பு வகை உள்ளது. அனுமதிக்கப்பட்ட கோப்புரிமைகள் png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, மற்றும் lic.',
    ),

    'update' => array(
        'error'   => 'உரிமம் புதுப்பிக்கப்படவில்லை, தயவு செய்து மீண்டும் முயற்சிக்கவும்',
        'success' => 'உரிமம் வெற்றிகரமாக புதுப்பிக்கப்பட்டது.'
    ),

    'delete' => array(
        'confirm'   => 'இந்த உரிமத்தை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error'   => 'உரிமத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'உரிமம் வெற்றிகரமாக நீக்கப்பட்டது.'
    ),

    'checkout' => array(
        'error'   => 'உரிமத்தை சரிபார்க்க ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'உரிமம் வெற்றிகரமாக சரிபார்க்கப்பட்டது',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'உரிமத்தில் சோதனை ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'உரிமம் வெற்றிகரமாக சரிபார்க்கப்பட்டது'
    ),

);
