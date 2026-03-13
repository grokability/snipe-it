<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'மாதிரி இல்லை.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'தற்போது இந்த மாதிரி ஒன்று ஒன்று அல்லது அதற்கு மேற்பட்ட சொத்துக்களுடன் தொடர்புடையது மற்றும் நீக்கப்பட முடியாது. சொத்துக்களை நீக்கிவிட்டு மீண்டும் நீக்குவதற்கு முயற்சிக்கவும்.',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'மாதிரி உருவாக்கப்பட்டது இல்லை, மீண்டும் முயற்சிக்கவும்.',
        'success' => 'மாதிரி வெற்றிகரமாக உருவாக்கப்பட்டது.',
        'duplicate_set' => 'அந்த பெயர், உற்பத்தியாளர் மற்றும் மாதிரி எண்ணுடன் ஏற்கனவே ஒரு சொத்து மாதிரி உள்ளது.',
    ),

    'update' => array(
        'error'   => 'மாதிரி புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
        'success' => 'மாடல் வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
    ),

    'delete' => array(
        'confirm'   => 'இந்த சொத்து மாடலை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
        'error'   => 'மாதிரியை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
        'success' => 'மாதிரி வெற்றிகரமாக நீக்கப்பட்டது.'
    ),

    'restore' => array(
        'error'   		=> 'மாடல் மீட்டமைக்கப்படவில்லை, தயவு செய்து மீண்டும் முயற்சிக்கவும்',
        'success' 		=> 'மாடல் வெற்றிகரமாக மீட்கப்பட்டது.'
    ),

    'bulkedit' => array(
        'error'   		=> 'எந்த துறைகளும் மாற்றப்படவில்லை, அதனால் எதுவும் புதுப்பிக்கப்படவில்லை.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No models were selected, so nothing was deleted.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
