<?php

return array(

    'deleted' => 'Удаленный поставщик',
    'does_not_exist' => 'Поставщик не существует.',


    'create' => array(
        'error'   => 'Поставщик не был создан, попробуйте еще раз.',
        'success' => 'Поставщик успешно создан.'
    ),

    'update' => array(
        'error'   => 'Поставщик не был обновлен, попробуйте еще раз',
        'success' => 'Поставщик успешно обновлен.'
    ),

    'delete' => array(
        'confirm'   => 'Вы уверены, что хотите удалить данного поставщика?',
        'error'   => 'При удалении поставщика произошла ошибка. Попробуйте еще раз.',
        'success' => 'Поставщик успешно удален.',
        'assoc_assets'	 => 'Этот поставщик в настоящее время связан с :asset_count активом(активами) и не может быть удален. Обновите свои активы, чтобы больше не ссылаться на этого поставщика и повторите попытку. ',
        'assoc_licenses'	 => 'Этот поставщик в настоящее время связан с: license_count лицензией(лицензиями) и не может быть удален. Обновите свои лицензии, чтобы больше не ссылаться на этого поставщика и повторите попытку. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
