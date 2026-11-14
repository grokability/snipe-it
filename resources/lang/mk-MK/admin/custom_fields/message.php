<?php

return [

    'field' => [
        'invalid' => 'Poleto не постои.',
        'already_added' => 'Полето веќе е додадено',
        'none_selected' => 'Нема избрани полиња',

        'create' => [
            'error' => 'Полето не е креирано, обидете се повторно.',
            'success' => 'Полето е успешно создадено.',
            'assoc_success' => 'Полето е успешно додадено во групата со полиња.',
        ],

        'update' => [
            'error' => 'Полето не е ажурирано, обидете се повторно',
            'success' => 'Полето е успешно ажурирано.',
        ],

        'delete' => [
            'confirm' => 'Дали сте сигурни дека сакате да го избришете ова поле?',
            'error' => 'Имаше проблем со бришење на полето. Обидете се повторно.',
            'success' => 'Полето беше успешно избришано.',
            'in_use' => 'Полето е сеуште во употреба.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset не постои',

        'create' => [
            'error' => 'Fieldset не е креиран, обидете се повторно.',
            'success' => 'Fieldset создаден успешно.',
        ],

        'update' => [
            'error' => 'Fieldset не е ажуриран, обидете се повторно',
            'success' => 'Fieldset ажуриран успешно.',
        ],

        'delete' => [
            'confirm' => 'Дали сте сигурни дека сакате да го избришете овој Fieldset?',
            'error' => 'Се појави проблем при бришењето на овој Fieldset. Обидете се повторно.',
            'success' => 'Fieldset успешно избришан.',
            'in_use' => 'Fieldset е сеуште во употреба.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Грешка при потврдување на основните вредности на групните полиња.',

    ],

];
