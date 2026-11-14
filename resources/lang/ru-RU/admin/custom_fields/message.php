<?php

return [

    'field' => [
        'invalid' => 'Это поле не существует.',
        'already_added' => 'Поле уже добавлено',
        'none_selected' => 'Ни одно поле не выбрано',

        'create' => [
            'error' => 'Поле не было создано, попробуйте снова.',
            'success' => 'Поле создано.',
            'assoc_success' => 'Поле добавлено в набор.',
        ],

        'update' => [
            'error' => 'Поле не было обновлено, попробуйте снова',
            'success' => 'Поле обновлено.',
        ],

        'delete' => [
            'confirm' => 'Вы уверены, что хотите удалить это поле?',
            'error' => 'При удалении поля возникла ошибка. Попробуйте снова.',
            'success' => 'Поле удалено.',
            'in_use' => 'Поле всё ещё используется.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Набор полей не существует',

        'create' => [
            'error' => 'Набор полей не был создан, попробуйте снова.',
            'success' => 'Набор полей создан.',
        ],

        'update' => [
            'error' => 'Набор полей не был обновлен, попробуйте снова',
            'success' => 'Набор полей обновлён.',
        ],

        'delete' => [
            'confirm' => 'Вы уверены, что хотите удалить этот набор полей?',
            'error' => 'При удалении набора полей возникла ошибка. Попробуйте снова.',
            'success' => 'Набор полей удален.',
            'in_use' => 'Набор полей всё ещё используется.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Ошибка при проверке значений полей по умолчанию.',

    ],

];
