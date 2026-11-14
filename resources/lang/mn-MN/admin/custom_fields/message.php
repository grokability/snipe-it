<?php

return [

    'field' => [
        'invalid' => 'Энэ талбар байхгүй байна.',
        'already_added' => 'Талбай нэмэгдсэн байна',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Талбар үүсгэгдсэнгүй, дахин оролдоно уу.',
            'success' => 'Талбай амжилттай болсон.',
            'assoc_success' => 'Field талбарт амжилттай нэмсэн.',
        ],

        'update' => [
            'error' => 'Талбар шинэчлэгдсэнгүй, дахин оролдоно уу',
            'success' => 'Field амжилттай шинэчлэгдсэн.',
        ],

        'delete' => [
            'confirm' => 'Та энэ талбарыг устгахыг хүсч байна уу?',
            'error' => 'Энэ талбарыг устгах асуудал гарлаа. Дахин оролдоно уу.',
            'success' => 'Талбар амжилттай устгагдсан байна.',
            'in_use' => 'Талбар ашиглагдаж байна.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset байхгүй байна',

        'create' => [
            'error' => 'Fieldset үүсгээгүй байна, дахин оролдоно уу.',
            'success' => 'Fieldset амжилттай болсон.',
        ],

        'update' => [
            'error' => 'Fieldset шинэчлэгдсэнгүй, дахин оролдоно уу',
            'success' => 'Fieldset амжилттай шинэчлэгдсэн.',
        ],

        'delete' => [
            'confirm' => 'Та энэ талбарыг устгахыг хүсч байгаадаа итгэлтэй байна уу?',
            'error' => 'Энэ талбарыг устгах асуудал гарлаа. Дахин оролдоно уу.',
            'success' => 'Fields амжилттай устгагдсан байна.',
            'in_use' => 'Fieldset ашиглагдаж байна.',
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
