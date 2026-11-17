<?php

return [

    'field' => [
        'invalid' => 'Това поле не съществува.',
        'already_added' => 'Вече е добавено поле',
        'none_selected' => 'Не е избрано поле',

        'create' => [
            'error' => 'Поле не беше създадено, моля опитайте отново.',
            'success' => 'Поле създадено успешно.',
            'assoc_success' => 'Поле успешно добавено към fieldset.',
        ],

        'update' => [
            'error' => 'Полето не беше актуализирано, моля опитайте отново',
            'success' => 'Поле актуализирано успешно.',
        ],

        'delete' => [
            'confirm' => 'Сигурни ли сте, че желаете да изтриете това поле?',
            'error' => 'Възникна проблем при изтриването на това поле. Моля, опитайте отново.',
            'success' => 'Полето бе изтрито успешно.',
            'in_use' => 'Полето все още се използва.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Полето не съществува',

        'create' => [
            'error' => 'Fieldset не беше създаден, моля опитайте отново.',
            'success' => 'Fieldset създаден успешно.',
        ],

        'update' => [
            'error' => 'Fieldset не беше актуализиран, моля опитайте отново',
            'success' => 'Fieldset актуализиран успешно.',
        ],

        'delete' => [
            'confirm' => 'Сигурни ли сте, че желаете да изтриете този fieldset?',
            'error' => 'Възникна проблем при изтриването на този fieldset. Моля, опитайте отново.',
            'success' => 'Fieldset бе изтрит успешно.',
            'in_use' => 'Fieldset все още се използва.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Грешка при валидиране на стойностите по подразбиране за набор от полета.',

    ],

];
