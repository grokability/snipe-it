<?php

return [

    'field' => [
        'invalid' => 'Šis lauks neeksistē.',
        'already_added' => 'Lauks jau ir pievienots',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Lauks nav izveidots, lūdzu, mēģiniet vēlreiz.',
            'success' => 'Lauks veiksmīgi izveidots.',
            'assoc_success' => 'Lauks veiksmīgi pievienots lauka laukam.',
        ],

        'update' => [
            'error' => 'Lauks nav atjaunināts, lūdzu, mēģiniet vēlreiz',
            'success' => 'Lauks ir veiksmīgi atjaunināts.',
        ],

        'delete' => [
            'confirm' => 'Vai tiešām vēlaties dzēst šo lauku?',
            'error' => 'Radās problēma, dzēšot lauku. Lūdzu mēģiniet vēlreiz.',
            'success' => 'Lauks bija veiksmīgi dzēsts.',
            'in_use' => 'Lauks joprojām tiek izmantots.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset nepastāv',

        'create' => [
            'error' => 'Fieldset nav izveidots, lūdzu, mēģiniet vēlreiz.',
            'success' => 'Fieldset izveidota veiksmīgi.',
        ],

        'update' => [
            'error' => 'Fieldset nebija atjaunināts, lūdzu, mēģiniet vēlreiz',
            'success' => 'Fieldset tika veiksmīgi atjaunināta.',
        ],

        'delete' => [
            'confirm' => 'Vai tiešām vēlaties dzēst šo lauka iestatījumu?',
            'error' => 'Radās problēma, novēršot lauka iestatījumu. Lūdzu mēģiniet vēlreiz.',
            'success' => 'Fieldset tika veiksmīgi dzēsts.',
            'in_use' => 'Fieldset joprojām tiek izmantots.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Kļūda lauku kopas noklusējuma vērtību validācijā.',

    ],

];
