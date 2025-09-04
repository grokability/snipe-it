<?php

return array(

    'accepted'                  => '이 자산이 승인되었습니다.',
    'declined'                  => '이 자산이 거부되었습니다.',
    'bulk_manager_warn'	        => '사용자가 성공적으로 갱신되었지만, 선택한 관리자가 편집할 사용자 목록에도 있었고, 사용자가 자신의 관리자가 아니 어서 관리자 항목이 저장되지 않았습니다. 관리자를 제외한 사용자를 다시 선택하십시오.',
    'user_exists'               => '사용자가 이미 존재합니다!',
    'user_not_found'            => '사용자가 존재하지 않거나, 볼수 있는 권한이 없습니다.',
    'user_login_required'       => '로그인 항목을 입력해 주세요.',
    'user_has_no_assets_assigned' => 'No assets currently assigned to user.',
    'user_password_required'    => '비밀번호를 입력해 주세요.',
    'insufficient_permissions'  => '승인 불충분.',
    'user_deleted_warning'      => '이 사용자는 삭제되었습니다. 그것들을 수정하려면 이 사용자를 복원하던가 새 자산들을 생성하세요.',
    'ldap_not_configured'        => '이 설치의 LDAP 통합이 구성이 되지 않았습니다.',
    'password_resets_sent'      => 'The selected users who are activated and have a valid email addresses have been sent a password reset link.',
    'password_reset_sent'       => ':email 로 패스워드 재설정 링크가 발송되었습니다.',
    'user_has_no_email'         => 'This user does not have an email address in their profile.',
    'log_record_not_found'        => 'A matching log record for this user could not be found.',


    'success' => array(
        'create'    => '사용자가 생성되었습니다.',
        'update'    => '사용자가 갱신 되었습니다.',
        'update_bulk'    => '사용자가 갱신 되었습니다.',
        'delete'    => '사용자가 삭제 되었습니다.',
        'ban'       => '사용자가 금지 처리 되었습니다.',
        'unban'     => '사용자의 금지 처리가 해제 되었습니다.',
        'suspend'   => '사용자를 대기 시켰습니다.',
        'unsuspend' => '사용자의 대기를 해제하였습니다.',
        'restored'  => '사용자를 복원하였습니다.',
        'import'    => '사용자를 내보냈습니다.',
    ),

    'error' => array(
        'create' => '사용자를 생성하는 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'update' => '사용자를 갱신하는 중 오류가 발생했습니다. 다시 시도해 주세요.',
        'delete' => '사용자를 삭제하는 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'delete_has_assets' => '이 사용자에게 품목이 할당되었고 삭제할 수 없습니다.',
        'delete_has_assets_var' => 'This user still has an asset assigned. Please check it in first.|This user still has :count assets assigned. Please check their assets in first.',
        'delete_has_licenses_var' => 'This user still has a license seats assigned. Please check it in first.|This user still has :count license seats assigned. Please check them in first.',
        'delete_has_accessories_var' => 'This user still has an accessory assigned. Please check it in first.|This user still has :count accessories assigned. Please check their assets in first.',
        'delete_has_locations_var' => 'This user still manages a location. Please select another manager first.|This user still manages :count locations. Please select another manager first.',
        'delete_has_users_var' => 'This user still manages another user. Please select another manager for that user first.|This user still manages :count users. Please select another manager for them first.',
        'unsuspend' => '사용자의 대기 해제 중 문제가 발생했습니다. 다시 시도하세요.',
        'import'    => '사용자를 내보내기 할 때 문제가 발생했습니다. 다시 시도하세요.',
        'asset_already_accepted' => '이 자산은 이미 수락되었습니다.',
        'accept_or_decline' => '이 자산을 승인 하거나 거부 하셔야 합니다.',
        'cannot_delete_yourself' => 'We would feel really bad if you deleted yourself, please reconsider.',
        'incorrect_user_accepted' => '승인하려는 자산이 체크아웃되지 않았습니다.',
        'ldap_could_not_connect' => 'LDAP 서버에 접속 할 수 없습니다. LDAP 설정 파일의 LDAP 서버 구성을 확인해 보세요.<br>LDAP 서버 오류:',
        'ldap_could_not_bind' => 'LDAP 서버와 동기화 할 수 없습니다. LDAP 설정 파일의 LDAP 서버 구성을 확인해 보세요.<br>LDAP 서버 오류: ',
        'ldap_could_not_search' => 'LDAP 서버를 찾을 수 없습니다. LDAP 설정 파일의 LDAP 서버 구성을 확인해 보세요.<br>LDAP 서버 오류:',
        'ldap_could_not_get_entries' => 'LDAP 서버 목록을 가져올 수 없습니다. LDAP 설정 파일의 LDAP 서버 구성을 확인해 보세요.<br>LDAP 서버 오류:',
        'password_ldap' => '이 계정의 비밀번호는 LDAP/Active 디렉토리에 의해 관리됩니다. 비밀번호를 변경하려면 IT 부서에 문의하세요. ',
        'multi_company_items_assigned' => 'This user has items assigned that belong to a different company. Please check them in or edit their company.'
    ),

    'deletefile' => array(
        'error'   => '파일이 삭제되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일이 삭제되었습니다.',
    ),

    'upload' => array(
        'error'   => '파일(들) 이 업로드 되지 않았습니다. 다시 시도해 주세요.',
        'success' => '파일(들) 이 업로드 되었습니다.',
        'nofiles' => '업로드 할 파일을 선택해 주세요',
        'invalidfiles' => '하나 이상의 파일이 너무 크거나 허용되지 않는 형식입니다. 허용되는 형식은 png, gif, jpg, doc, docx, pdf, txt 입니다.',
    ),

    'inventorynotification' => array(
        'error'   => '해당 유저는 이메일이 설정되어 있지 않습니다.',
        'success' => 'The user has been notified about their current inventory.'
    )
);