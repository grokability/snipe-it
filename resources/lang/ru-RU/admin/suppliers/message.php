<?php

return [

    'deleted' => 'Удаленный поставщик',
    'does_not_exist' => 'Поставщик не существует.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Поставщик не был создан, попробуйте еще раз.',
        'success' => 'Поставщик успешно создан.',
    ],

    'update' => [
        'error' => 'Поставщик не был обновлен, попробуйте еще раз',
        'success' => 'Поставщик успешно обновлен.',
    ],

    'delete' => [
        'confirm' => 'Вы уверены, что хотите удалить данного поставщика?',
        'error' => 'При удалении поставщика произошла ошибка. Попробуйте еще раз.',
        'success' => 'Поставщик успешно удален.',
        'not_found' => 'Поставщик не найден.',
        'bulk_success' => 'Поставщик успешно удален.|:count поставщиков были успешно удалены.',
        'partial_success' => '.',
    ],

];
