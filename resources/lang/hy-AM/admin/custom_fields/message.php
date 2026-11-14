<?php

return [

    'field' => [
        'invalid' => 'Այդ դաշտը գոյություն չունի։',
        'already_added' => 'Դաշտն արդեն ավելացված է։',
        'none_selected' => 'Որևէ դաշտ ընտրված չէ։',

        'create' => [
            'error' => 'Դաշտը չի ստեղծվել։ Խնդրում ենք կրկին փորձել։',
            'success' => 'Դաշտը հաջողությամբ ստեղծվել է։',
            'assoc_success' => 'Դաշտը հաջողությամբ ավելացվել է դաշտերի հավաքածուին։',
        ],

        'update' => [
            'error' => 'Դաշտը չի թարմացվել։ Խնդրում ենք կրկին փորձել։',
            'success' => 'Դաշտը հաջողությամբ թարմացվել է։',
        ],

        'delete' => [
            'confirm' => 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս դաշտը։',
            'error' => 'Դաշտը ջնջելիս խնդիր առաջացավ։ Խնդրում ենք կրկին փորձել։',
            'success' => 'Դաշտը հաջողությամբ ջնջվել է։',
            'in_use' => 'Դաշտը դեռ օգտագործվում է։',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Դաշտերի հավաքածուն գոյություն չունի։',

        'create' => [
            'error' => 'Դաշտերի հավաքածուն չի ստեղծվել։ Խնդրում ենք կրկին փորձել։',
            'success' => 'Դաշտերի հավաքածուն հաջողությամբ ստեղծվել է։',
        ],

        'update' => [
            'error' => 'Դաշտերի հավաքածուն չի թարմացվել։ Խնդրում ենք կրկին փորձել։',
            'success' => 'Դաշտերի հավաքածուն հաջողությամբ թարմացվել է։',
        ],

        'delete' => [
            'confirm' => 'Վստա՞հ եք, որ ցանկանում եք ջնջել այս դաշտերի հավաքածուն։',
            'error' => 'Դաշտերի հավաքածուն ջնջելիս խնդիր առաջացավ։ Խնդրում ենք կրկին փորձել։',
            'success' => 'Դաշտերի հավաքածուն հաջողությամբ ջնջվել է։',
            'in_use' => 'Դաշտերի հավաքածուն դեռ օգտագործվում է։',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Լռելյայն դաշտերի խմբի արժեքների վավերացման սխալ։',

    ],

];
