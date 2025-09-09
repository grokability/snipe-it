<?php

return array(

    'deleted' => 'Упаковуючий постачальник видалено',
    'does_not_exist' => 'Постачальник не існує.',


    'create' => array(
        'error'   => 'Постачальник не був створений, будь ласка, повторіть спробу.',
        'success' => 'Постачальник успішно створений.'
    ),

    'update' => array(
        'error'   => 'Постачальника не було оновлено, будь ласка, спробуйте ще раз',
        'success' => 'Постачальника успішно оновлено.'
    ),

    'delete' => array(
        'confirm'   => 'Ви дійсно хочете видалити цього постачальника?',
        'error'   => 'Виникла проблема при видаленні постачальника. Будь ласка, спробуйте ще раз.',
        'success' => 'Постачальника успішно видалено.',
        'assoc_assets'	 => 'Цей постачальник наразі асоціюється з :asset_count медіафайлів і не може бути видалений. Будь ласка, оновіть ваші активи, щоб більше не посилалися на цього постачальника і повторіть спробу. ',
        'assoc_licenses'	 => 'Цей постачальник наразі асоціюється з :licenses_count ліцензіями і не може бути видалений. Оновіть свої ліцензії, щоб більше не посилатися на цього постачальника і повторіть спробу. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
