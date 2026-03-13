<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> '이 라이선스와 연결하려는 자산이 존재하지 않습니다.',
    'owner_doesnt_match_asset' => '이 라이센스와 연결하려고하는 자산은 드롭 다운 목록에서 선택한 사람이 아닌 다른 누군가가 소유하고 있습니다.',
    'assoc_users'	 => '이 자산은 현재 사용자에게 반출 중이어서 삭제 할 수 없습니다. 먼저 자산을 확인해 보고 다시 삭제를 시도해 주세요. ',
    'select_asset_or_person' => '애셋이나 사용자 중 하나만 선택해야하며 둘 다 선택할 수는 없습니다.',
    'not_found' => '라이센스를 찾을 수 없음',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => '라이선스가 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '라이선스가 생성되었습니다.'
    ),

    'deletefile' => array(
        'error'   => '파일이 삭제되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일이 삭제되었습니다.',
    ),

    'upload' => array(
        'error'   => '파일(들)이 업로드 되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일(들)이 업로드 되었습니다.',
        'nofiles' => '업로드 하기 위한 파일이 선택되지 않았거나, 업로드 할 파일이 너무 큽니다.',
        'invalidfiles' => '하나 이상의 파일이 너무 크거나 허용되지 않는 형식입니다. 허용되는 형식은 png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, lic 입니다.',
    ),

    'update' => array(
        'error'   => '라이선스가 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '라이선스가 갱신되었습니다.'
    ),

    'delete' => array(
        'confirm'   => '이 라이선스를 삭제하시겠습니까?',
        'error'   => '라이선스 삭제 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '라이선스가 삭제되었습니다.'
    ),

    'checkout' => array(
        'error'   => '라이선스 반출 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '라이선스가 반출 되었습니다.',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => '라이선스 반입 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'not_reassignable' => 'Seat has been used',
        'success' => '라이선스가 반입 되었습니다.'
    ),

);
