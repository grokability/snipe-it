<?php

return [

    'field' => [
        'invalid' => '그 항목은 없습니다.',
        'already_added' => '이미 추가한 항목입니다.',
        'none_selected' => '항목이 선택되지 않았습니다.',

        'create' => [
            'error' => '항목을 생성하지 못했습니다. 재시도해 주십시오.',
            'success' => '항목을 생성했습니다.',
            'assoc_success' => '항목세트에 항목을 추가했습니다.',
        ],

        'update' => [
            'error' => '항목을 생성하지 못했습니다. 재시도해 주십시오.',
            'success' => '항목을 갱신했습니다.',
        ],

        'delete' => [
            'confirm' => '이 항목을 삭제하는 것이 맞습니까?',
            'error' => '항목 삭제 도중에 이슈가 발생했습니다. 재시도해 주십시오.',
            'success' => '항목을 삭제했습니다.',
            'in_use' => '아직까지 항목을 사용하고 있습니다.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => '항목세트가 존재하지 않습니다',

        'create' => [
            'error' => '항목세트를 만들지 못했습니다. 재시도해 주십시오.',
            'success' => '항목세트를 생성했습니다.',
        ],

        'update' => [
            'error' => '항목세트를 갱신하지 못했습니다. 재시도해 주십시오.',
            'success' => '항목세트를 갱신했습니다.',
        ],

        'delete' => [
            'confirm' => '이 항목세트를 삭제하는 것이 맞습니까?',
            'error' => '항목세트 삭제 도중에 이슈가 발생했습니다. 재시도해 주십시오.',
            'success' => '항목세트를 삭제했습니다.',
            'in_use' => '항목세트를 사용 중입니다.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => '기본 필드셋 값 검증 오류.',

    ],

];
