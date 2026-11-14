<?php

return [
    'does_not_exist' => '회사가 없습니다.',
    'deleted' => '삭제된 회사',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => '회사를 만들지 못했습니다. 재시도해 주십시오.',
        'success' => '회사를 만들었습니다.',
    ],
    'update' => [
        'error' => '회사를 갱신하지 못했습니다. 재시도해 주십시오.',
        'success' => '회사를 갱신했습니다.',
    ],
    'delete' => [
        'confirm' => '이 회사를 삭제하고자 하는게 맞습니까?',
        'error' => '회사 삭제 도중 이슈가 발생했습니다. 재시도해 주십시오.',
        'success' => '회사를 삭제했습니다.',
        'bulk_success' => '회사가 삭제되었습니다.|:count개 회사가 삭제되었습니다.',
        'partial_success' => '회사가 삭제되었습니다. 아래 추가 정보를 확인하세요. | :count개 회사가 삭제되었습니다. 아래 추가 정보를 확인하세요.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
