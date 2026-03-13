<?php

return array(

    'accepted'                  => 'You have successfully accepted this item.',
    'declined'                  => 'You have successfully declined this item.',
    'bulk_manager_warn'	        => 'உங்கள் பயனர் வெற்றிகரமாக புதுப்பிக்கப்பட்டிருந்தாலும், உங்கள் மேலாளர் நுழைவு சேமிக்கப்படவில்லை, ஏனெனில் நீங்கள் தேர்ந்தெடுத்த மேலாளர் பயனர் பட்டியலில் திருத்தப்பட வேண்டும், மேலும் பயனர்கள் தங்கள் மேலாளராக இருக்கலாம். மேலாளரைத் தவிர்த்து உங்கள் பயனர்களை மீண்டும் தேர்ந்தெடுக்கவும்.',
    'user_exists'               => 'பயனர் ஏற்கனவே உள்ளது!',
    'cannot_delete'             => 'User does not exist or you do not have permission to delete them.',
    'user_not_found'            => 'User does not exist or you do not have permission to view them.',
    'user_login_required'       => 'உள்நுழைவுத் துறை தேவைப்படுகிறது',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => 'கடவுச்சொல் தேவை.',
    'insufficient_permissions'  => 'போதிய அனுமதிகள் இல்லை.',
    'user_deleted_warning'      => 'இந்த பயனர் நீக்கப்பட்டது. நீங்கள் அவற்றை திருத்தவோ அல்லது புதிய சொத்துகளை ஒதுக்கவோ இந்த பயனரை மீட்டெடுக்க வேண்டும்.',
    'ldap_not_configured'        => 'இந்த நிறுவலுக்கு LDAP ஒருங்கிணைப்பு கட்டமைக்கப்படவில்லை.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => 'A password reset link has been sent to :email!',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => 'பயனர் வெற்றிகரமாக உருவாக்கப்பட்டது.',
        'update'    => 'பயனர் வெற்றிகரமாக மேம்படுத்தப்பட்டது.',
        'update_bulk'    => 'பயனர்கள் வெற்றிகரமாக மேம்படுத்தப்பட்டன!',
        'delete'    => 'பயனர் வெற்றிகரமாக நீக்கப்பட்டது.',
        'ban'       => 'பயனர் வெற்றிகரமாக தடை செய்யப்பட்டது.',
        'unban'     => 'பயனர் வெற்றிகரமாக தடைசெய்யப்படவில்லை.',
        'suspend'   => 'பயனர் வெற்றிகரமாக இடைநீக்கம் செய்யப்பட்டார்.',
        'unsuspend' => 'பயனர் வெற்றிகரமாக தடுக்கப்படவில்லை.',
        'restored'  => 'பயனர் வெற்றிகரமாக மீட்டமைக்கப்பட்டார்.',
        'import'    => 'பயனர்கள் வெற்றிகரமாக இறக்குமதி செய்தனர்.',
    ),

    'error' => array(
        'create' => 'பயனர் உருவாக்கும் சிக்கல் ஏற்பட்டது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'update' => 'பயனரைப் புதுப்பிப்பதில் சிக்கல் ஏற்பட்டது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'delete' => 'பயனரை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'delete_has_assets' => 'இந்த பயனருக்கு ஒதுக்கப்பட்டுள்ள உருப்படிகளைக் கொண்டுள்ளது, மேலும் நீக்க முடியாது.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => 'பயனரை unsuspending ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'import'    => 'பயனர்களை இறக்குமதி செய்வதில் சிக்கல் ஏற்பட்டது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'asset_already_accepted' => 'இந்த சொத்து ஏற்கனவே ஏற்கப்பட்டுள்ளது.',
        'accept_or_decline' => 'நீங்கள் இந்த சொத்தை ஏற்கவோ அல்லது குறைக்கவோ கூடாது.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => 'நீங்கள் ஏற்றுக்கொள்ள முயற்சித்த சொத்து உங்களிடம் சோதிக்கப்படவில்லை.',
        'ldap_could_not_connect' => 'LDAP சேவையகத்துடன் இணைக்க முடியவில்லை. LDAP கட்டமைப்பு கோப்பில் உங்கள் LDAP சர்வர் கட்டமைப்பை சரிபார்க்கவும். <br> LDAP சேவையகத்திலிருந்து பிழை:',
        'ldap_could_not_bind' => 'LDAP சேவையகத்துடன் இணைக்க முடியவில்லை. LDAP கட்டமைப்பு கோப்பில் உங்கள் LDAP சர்வர் கட்டமைப்பை சரிபார்க்கவும். <br> LDAP சேவையகத்திலிருந்து பிழை:',
        'ldap_could_not_search' => 'LDAP சேவையகத்தை தேட முடியவில்லை. LDAP கட்டமைப்பு கோப்பில் உங்கள் LDAP சர்வர் கட்டமைப்பை சரிபார்க்கவும். <br> LDAP சேவையகத்திலிருந்து பிழை:',
        'ldap_could_not_get_entries' => 'LDAP சேவையகத்திலிருந்து உள்ளீடுகளை பெற முடியவில்லை. LDAP கட்டமைப்பு கோப்பில் உங்கள் LDAP சர்வர் கட்டமைப்பை சரிபார்க்கவும். <br> LDAP சேவையகத்திலிருந்து பிழை:',
        'password_ldap' => 'இந்த கணக்கிற்கான கடவுச்சொல் LDAP / Active Directory மூலம் நிர்வகிக்கப்படுகிறது. உங்கள் கடவுச்சொல்லை மாற்ற உங்கள் IT பிரிவை தொடர்பு கொள்ளவும்.',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => 'கோப்பு நீக்கப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு வெற்றிகரமாக நீக்கப்பட்டது.',
    ),

    'upload' => array(
        'error'   => 'கோப்பு (கள்) பதிவேற்றப்படவில்லை. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'கோப்பு (கள்) வெற்றிகரமாக பதிவேற்றப்பட்டது.',
        'nofiles' => 'பதிவேற்றுவதற்கான எந்தவொரு கோப்பையும் நீங்கள் தேர்ந்தெடுக்கவில்லை',
        'invalidfiles' => 'உங்கள் கோப்புகளில் ஒன்று அல்லது அதற்கு மேற்பட்டவை மிக அதிகமாக உள்ளது அல்லது அனுமதிக்கப்படாத கோப்பு வகை உள்ளது. அனுமதிக்கப்பட்ட கோப்புரிமைகள் png, gif, jpg, doc, docx, pdf மற்றும் txt ஆகியவை.',
    ),

    'inventorynotification' => array(
        'error'   => 'This user has no email set.',
        'success' => 'The user has been notified about their current inventory.'
    )
);