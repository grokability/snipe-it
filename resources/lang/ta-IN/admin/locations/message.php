<?php

return array(

    'does_not_exist' => 'இருப்பிடம் இல்லை.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'இந்த இடம் தற்போது குறைந்தது ஒரு சொத்துடன் தொடர்புடையது மற்றும் நீக்கப்பட முடியாது. இந்த இருப்பிடத்தை இனி குறிப்பிடாமல் உங்கள் சொத்துக்களை புதுப்பித்து மீண்டும் முயற்சிக்கவும்.',
    'assoc_child_loc'	 => 'இந்த இடம் தற்போது குறைந்தது ஒரு குழந்தையின் இருப்பிடத்தின் பெற்றோர் மற்றும் அதை நீக்க முடியாது. இந்த இருப்பிடத்தை இனி குறிப்பிடாமல் இருக்க உங்கள் இருப்பிடங்களை புதுப்பித்து மீண்டும் முயற்சிக்கவும்.',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'இருப்பிடம் உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்.',
        'success' => 'இடம் வெற்றிகரமாக உருவாக்கப்பட்டது.'
    ),

    'update' => array(
        'error'   => 'இருப்பிடம் புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'இருப்பிடம் வெற்றிகரமாக புதுப்பிக்கப்பட்டது.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'இந்த இருப்பிடத்தை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error'   => 'இருப்பிடத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'இடம் வெற்றிகரமாக நீக்கப்பட்டது.'
    )

);
