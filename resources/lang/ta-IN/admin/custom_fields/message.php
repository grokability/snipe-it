<?php

return [

    'field' => [
        'invalid' => 'அந்த புலம் இல்லை.',
        'already_added' => 'புலம் ஏற்கனவே சேர்க்கப்பட்டது',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'புலம் உருவாக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்.',
            'success' => 'புலம் வெற்றிகரமாக உருவாக்கப்பட்டது.',
            'assoc_success' => 'Fieldet க்கு புலம் வெற்றிகரமாக சேர்க்கப்பட்டது.',
        ],

        'update' => [
            'error' => 'புலம் புதுப்பிக்கப்படவில்லை, மீண்டும் முயற்சிக்கவும்',
            'success' => 'புலம் வெற்றிகரமாக புதுப்பிக்கப்பட்டது.',
        ],

        'delete' => [
            'confirm' => 'நிச்சயமாக இந்த புலம் நீக்க வேண்டுமா?',
            'error' => 'களத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
            'success' => 'புலம் வெற்றிகரமாக நீக்கப்பட்டது.',
            'in_use' => 'புலம் இன்னும் பயன்படுத்தப்படுகிறது.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'புலத்தில் இல்லை',

        'create' => [
            'error' => 'புலத்தை உருவாக்கவில்லை, தயவு செய்து மீண்டும் முயற்சி செய்க.',
            'success' => 'புலத்தில் வெற்றிகரமாக உருவாக்கப்பட்டது.',
        ],

        'update' => [
            'error' => 'புலத்தை புதுப்பிக்க முடியவில்லை, மீண்டும் முயற்சிக்கவும்',
            'success' => 'புலத்தில் வெற்றிகரமாக மேம்படுத்தப்பட்டது.',
        ],

        'delete' => [
            'confirm' => 'இந்த துறையை நிச்சயமாக நீக்க விரும்புகிறீர்களா?',
            'error' => 'களஞ்சியத்தை நீக்குவதில் ஒரு சிக்கல் இருந்தது. தயவு செய்து மீண்டும் முயற்சிக்கவும்.',
            'success' => 'Fieldset வெற்றிகரமாக நீக்கப்பட்டது.',
            'in_use' => 'புலத்தில் இன்னும் பயன்படுத்தப்படுகிறது.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
