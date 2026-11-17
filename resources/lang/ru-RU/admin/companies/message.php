<?php

return [
    'does_not_exist' => 'Компания не существует.',
    'deleted' => 'Удалённая компания',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Компания не была создана, попробуйте снова.',
        'success' => 'Компания создана.',
    ],
    'update' => [
        'error' => 'Компания не была обновлена, попробуйте снова',
        'success' => 'Компания обновлена.',
    ],
    'delete' => [
        'confirm' => 'Вы уверены, что хотите удалить эту компанию?',
        'error' => 'Возникла проблема при удалении компании. Попробуйте снова.',
        'success' => 'Компания удалена.',
        'bulk_success' => 'Компания успешно удалена.|:count компаний были успешно удалены.',
        'partial_success' => 'Компания успешно удалена. Смотрите дополнительную информацию ниже. | :count компаний были удалены. Смотрите дополнительную информацию ниже.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
