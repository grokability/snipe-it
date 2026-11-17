<?php

return [

    'deleted' => 'Изтрит доставчик',
    'does_not_exist' => 'Несъществуващ доставчик.',
    'assoc_items' => 'This supplier is currently associated with at least one asset, accessory, consumable, component, license, or maintenance record and cannot be deleted. Please update those items to no longer reference this supplier and try again. ',

    'create' => [
        'error' => 'Доставчикът не беше създаден. Моля, опитайте отново.',
        'success' => 'Доставчикът е създаден.',
    ],

    'update' => [
        'error' => 'Достъвчикът не беше обновен. Моля, опитайте отново',
        'success' => 'Доставчикът е обновен.',
    ],

    'delete' => [
        'confirm' => 'Сигурни ли сте, че искате да изтриете този доставчик?',
        'error' => 'Възникна проблем при изтриване на доставчика. Моля, опитайте отново.',
        'success' => 'Доставчикът е изтрит.',
        'not_found' => 'Доставчика не е намерен.',
        'bulk_success' => 'Supplier deleted successfully.|:count suppliers were deleted successfully.',
        'partial_success' => '.',
    ],

];
