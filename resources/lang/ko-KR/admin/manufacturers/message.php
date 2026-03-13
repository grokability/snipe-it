<?php

return array(

    'support_url_help' => 'Variables <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, and <code>{MODEL_NAME}</code> may be used in your URL to have those values auto-populate when viewing assets - for example https://checkcoverage.apple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => '제조업체가 존재하지 않습니다.',
    'assoc_users'	 => '이 제조업체는 현재 적어도 하나의 모델에 연결되어 있기에 삭제 할 수 없습니다. 이 제조업체가 모델을 참조 하지 않게 수정하고 다시 시도 해 보세요. ',

    'create' => array(
        'error'   => '제조업체가 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '제조업체가 생성되었습니다.'
    ),

    'update' => array(
        'error'   => '제조업체가 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '제조업체가 갱신되었습니다.'
    ),

    'restore' => array(
        'error'   => '제조업체가 복원되지 않았습니다. 다시 시도해 주세요',
        'success' => '제조업체가 복원되었습니다.'
    ),

    'delete' => array(
        'confirm' => '이 제조업체를 삭제하시겠습니까?',
        'error'   => '제조업체를 삭제하는 중에 문제가 생겼습니다. 다시 시도해 주세요.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
