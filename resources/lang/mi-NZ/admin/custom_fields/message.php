<?php

return [

    'field' => [
        'invalid' => 'Kāore te mara i te tīariari.',
        'already_added' => 'Kua tapirihia te mara',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Kāore i hanga te mara, tēnā whakamātau anō.',
            'success' => 'Kua angitu te hanganga o te mara.',
            'assoc_success' => 'Kua angitu te uru o te mara ki te raruraru.',
        ],

        'update' => [
            'error' => 'Kāore i te whakahou te mara, tēnā whakamātau anō',
            'success' => 'Kua oti te whakahoutanga o te mara.',
        ],

        'delete' => [
            'confirm' => 'Kei te hiahia koe ki te muku i tenei mara?',
            'error' => 'He raru kei te whakakore i te mara. Tena ngana ano.',
            'success' => 'Kua mukua te mara.',
            'in_use' => 'Kei te whakamahia tonu te mara.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Kāore i te tīariari te āpure',

        'create' => [
            'error' => 'Kaore i hangahia te papawai, me ngana ano.',
            'success' => 'I hanga angitu a Fieldset.',
        ],

        'update' => [
            'error' => 'Kāore i te whakahoutia a Fieldset, tēnā whakamātau anō',
            'success' => 'Kua pai te whakahōu o Fieldfield.',
        ],

        'delete' => [
            'confirm' => 'Kei te hiahia koe kia mukua tenei raruraru?',
            'error' => 'He raru kei te whakakore i te raruraru. Tena ngana ano.',
            'success' => 'Kua mukua te raruraru.',
            'in_use' => 'Kei te whakamahia tonu a Fieldset.',
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
