<?php

return array(

    'deleted' => 'Изтрит доставчик',
    'does_not_exist' => 'Несъществуващ доставчик.',


    'create' => array(
        'error'   => 'Доставчикът не беше създаден. Моля, опитайте отново.',
        'success' => 'Доставчикът е създаден.'
    ),

    'update' => array(
        'error'   => 'Достъвчикът не беше обновен. Моля, опитайте отново',
        'success' => 'Доставчикът е обновен.'
    ),

    'delete' => array(
        'confirm'   => 'Сигурни ли сте, че искате да изтриете този доставчик?',
        'error'   => 'Възникна проблем при изтриване на доставчика. Моля, опитайте отново.',
        'success' => 'Доставчикът е изтрит.',
        'assoc_assets'	 => 'Този доставчик е текущо асоцииран с :asset_count актива и не може да бъде изтрит. Моля, променете връзките на моделите по отношение на този доставчик и опитайте отново. ',
        'assoc_licenses'	 => 'Този доставчик е текущо асоцииран с :licenses_count лиценза и не може да бъде изтрит. Моля, променете връзките на лицензите по отношение на този доставчик и опитайте отново. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
