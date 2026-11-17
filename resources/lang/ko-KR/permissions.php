<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    | The following language lines are used in the user permissions system.
    | Each permission has a 'name' and a 'note' that describes
    | the permission in detail.
    |
    | DO NOT edit the keys (left-hand side) of each permission as these are
    | used throughout the system for translations.
    |---------------------------------------------------------------------------
    */

    'superuser' => [
        'name' => '슈퍼 유저',
        'note' => '관리자의 모든 측면에 대한 전체 액세스 권한을 사용자에게 부여할지 결정합니다. 이 설정은 시스템 전체의 더 구체적이고 제한적인 모든 권한보다 우선합니다. ',
    ],
    'admin' => [
        'name' => '관리자 액세스',
        'note' => '시스템 관리자 설정을 제외한 시스템 대부분의 측면에 대한 액세스 권한을 사용자에게 부여할지 결정합니다. 이 사용자는 사용자, 위치, 카테고리 등을 관리할 수 있지만, 다중 회사 전체 지원이 활성화된 경우 이에 제한됩니다.',
    ],

    'import' => [
        'name' => 'CSV 가져오기',
        'note' => '다른 곳에서 사용자, 자산 등에 대한 액세스가 거부되더라도 사용자가 가져오기를 할 수 있도록 허용합니다.',
    ],

    'reports' => [
        'name' => '보고서 액세스',
        'note' => '애플리케이션의 보고서 섹션에 대한 액세스 권한을 사용자에게 부여할지 결정합니다.',
    ],

    'assets' => [
        'name' => '자산',
        'note' => '애플리케이션의 자산 섹션에 대한 액세스 권한을 부여합니다. ',
    ],

    'assetsview' => [
        'name' => '자산 보기',
        'note' => 'Note that users with this permission will also be able to see (not modify or delete) files uploaded to the asset model as well. This is to make it easier to share common documents like user manuals across assets without having to upload them to every asset, and to avoid having to grant the user permission to modify asset files. Users with this permission will also be able to view edit and checkin history.',
    ],

    'assetscreate' => [
        'name' => '새 자산 생성',
    ],

    'assetsedit' => [
        'name' => '자산 편집',
    ],

    'assetsdelete' => [
        'name' => '자산 삭제',
    ],

    'assetscheckin' => [
        'name' => '반입',
        'note' => '현재 반출된 자산을 재고로 다시 반입합니다.',
    ],

    'assetscheckout' => [
        'name' => '반출',
        'note' => '재고 자산을 반출하여 할당',
    ],

    'assetsaudit' => [
        'name' => '자산 감사',
        'note' => '사용자가 자산을 실물 재고로 표시할 수 있도록 허용',
    ],

    'assetsviewrequestable' => [
        'name' => 'View Requestable Items',
        'note' => 'Allows the user to view items that are marked as requestable.',
    ],

    'assetsviewencrypted-custom-fields' => [
        'name' => '암호화된 사용자 정의 필드 보기',
        'note' => '사용자가 자산의 암호화된 사용자 정의 필드를 보고 수정할 수 있도록 허용',
    ],

    'accessories' => [
        'name' => '부속품들',
        'note' => '애플리케이션의 액세서리 섹션에 대한 접근 권한 부여',
    ],

    'accessoriesview' => [
        'name' => '액세서리 보기',
    ],
    'accessoriescreate' => [
        'name' => '새 액세서리 생성',
    ],
    'accessoriesedit' => [
        'name' => '액세서리 편집',
    ],
    'accessoriesdelete' => [
        'name' => '액세서리 삭제',
    ],
    'accessoriescheckout' => [
        'name' => '액세서리 반출',
        'note' => '재고 액세서리를 반출하여 할당',
    ],
    'accessoriescheckin' => [
        'name' => '액세서리 반입',
        'note' => '현재 반출된 액세서리를 재고로 다시 반입.',
    ],
    'accessoriesfiles' => [
        'name' => '액세서리 파일 관리',
        'note' => '사용자가 액세서리와 연결된 파일을 업로드, 다운로드, 삭제할 수 있도록 허용 (보기 권한 이상에서만 유효)',
    ],

    'assetsfiles' => [
        'name' => '자산 파일 관리',
        'note' => '사용자가 자산과 연결된 파일을 업로드, 다운로드, 삭제할 수 있도록 허용 (보기 권한 이상에서만 유효)',
    ],

    'usersfiles' => [
        'name' => '사용자 파일 관리',
        'note' => '사용자가 사용자와 연결된 파일을 업로드, 다운로드, 삭제할 수 있도록 허용 (보기 권한 이상에서만 유효)',
    ],

    'modelsfiles' => [
        'name' => '모델 파일 관리',
        'note' => '사용자가 모델 보기 화면과 자산 보기 화면 모두에서 자산 모델과 연결된 파일을 업로드, 다운로드, 삭제할 수 있음. (보기 권한 이상에서만 의미 있음.)',
    ],

    'departmentsfiles' => [
        'name' => '부서 파일 관리',
        'note' => '사용자가 부서와 연결된 파일을 업로드, 다운로드, 삭제할 수 있음. (보기 권한 이상에서만 의미 있음.)',
    ],

    'suppliersfiles' => [
        'name' => '공급업체 파일 관리',
        'note' => '사용자가 공급업체와 연결된 파일을 업로드, 다운로드, 삭제할 수 있음. (보기 권한 이상에서만 의미 있음.)',
    ],

    'locationsfiles' => [
        'name' => '위치 파일 관리',
        'note' => '사용자가 위치와 연결된 파일을 업로드, 다운로드, 삭제할 수 있음. (보기 권한 이상에서만 의미 있음.)',
    ],

    'companiesfiles' => [
        'name' => '회사 파일 관리',
        'note' => '사용자가 회사와 연결된 파일을 업로드, 다운로드, 삭제할 수 있음. (보기 권한 이상에서만 의미 있음.)',
    ],

    'consumablesfiles' => [
        'name' => '소모품 파일 관리',
        'note' => '사용자가 소모품과 연결된 파일을 업로드, 다운로드, 삭제할 수 있음. (보기 권한 이상에서만 의미 있음.)',
    ],

    'consumables' => [
        'name' => '소모품',
        'note' => '애플리케이션의 소모품 섹션에 접근 권한 부여.',
    ],
    'consumablesview' => [
        'name' => '소모품 보기',
    ],
    'consumablescreate' => [
        'name' => '새 소모품 생성',
    ],
    'consumablesedit' => [
        'name' => '소모품 편집',
    ],
    'consumablesdelete' => [
        'name' => '소모품 삭제',
    ],
    'consumablescheckout' => [
        'name' => '소모품 반출',
        'note' => '재고의 소모품을 반출하여 할당.',
    ],

    'licenses' => [
        'name' => '라이선스',
        'note' => '애플리케이션의 라이선스 섹션에 접근 권한 부여.',
    ],
    'licensesview' => [
        'name' => '라이선스 보기',
    ],
    'licensescreate' => [
        'name' => '새 라이선스 생성',
    ],
    'licensesedit' => [
        'name' => '라이선스 편집',
    ],
    'licensesdelete' => [
        'name' => '라이선스 삭제',
    ],
    'licensescheckout' => [
        'name' => '라이선스 할당',
        'note' => '사용자가 자산 또는 사용자에게 라이선스를 할당할 수 있도록 허용',
    ],
    'licensescheckin' => [
        'name' => '라이선스 할당 해제',
        'note' => '사용자가 자산 또는 사용자로부터 라이선스 할당을 해제할 수 있도록 허용',
    ],
    'licensesfiles' => [
        'name' => '라이선스 파일 관리',
        'note' => '사용자가 라이선스와 연결된 파일을 업로드, 다운로드 및 삭제할 수 있도록 허용',
    ],
    'componentsfiles' => [
        'name' => '부품 파일 관리',
        'note' => '부품에 연결된 파일의 업로드, 다운로드, 삭제를 허용',
    ],

    'licenseskeys' => [
        'name' => '라이선스 키 관리',
        'note' => '사용자가 라이선스와 연결된 제품 키를 볼 수 있도록 허용',
    ],
    'components' => [
        'name' => '구성 요소',
        'note' => '애플리케이션의 부품 섹션에 대한 접근 권한 부여',
    ],
    'componentsview' => [
        'name' => '부품 보기',
    ],
    'componentscreate' => [
        'name' => '새 부품 생성',
    ],
    'componentsedit' => [
        'name' => '부품 편집',
    ],
    'componentsdelete' => [
        'name' => '부품 삭제',
    ],

    'componentscheckout' => [
        'name' => '부품 반출',
        'note' => '재고 부품을 반출하여 할당',
    ],
    'componentscheckin' => [
        'name' => '부품 반입',
        'note' => '현재 반출된 부품을 재고로 반입',
    ],
    'kits' => [
        'name' => '사전 정의된 키트',
        'note' => '애플리케이션의 사전 정의 키트 섹션 접근 권한 부여',
    ],
    'kitsview' => [
        'name' => '사전 정의 키트 보기',
    ],
    'kitscreate' => [
        'name' => '새 사전 정의 키트 생성',
    ],
    'kitsedit' => [
        'name' => '사전 정의 키트 편집',
    ],
    'kitsdelete' => [
        'name' => '사전 정의 키트 삭제',
    ],
    'users' => [
        'name' => '사용자',
        'note' => '애플리케이션의 사용자 섹션 접근 권한 부여',
    ],
    'usersview' => [
        'name' => '사용자 보기',
        'note' => 'Users with this permission will also be able to view edit and checkin history. Access to files uploaded to a user is gated separately by the Manage User Files permission.',
    ],
    'userscreate' => [
        'name' => '새 사용자 생성',
    ],
    'usersedit' => [
        'name' => '사용자 편집',
    ],
    'usersdelete' => [
        'name' => '사용자 삭제',
    ],
    'models' => [
        'name' => '모델',
        'note' => '애플리케이션의 모델 섹션에 대한 접근 권한 부여.',
    ],
    'modelsview' => [
        'name' => '모델 보기',
    ],

    'modelscreate' => [
        'name' => '새 모델 생성',
    ],
    'modelsedit' => [
        'name' => '모델 편집',
    ],
    'modelsdelete' => [
        'name' => '모델 삭제',
    ],
    'categories' => [
        'name' => '분류',
        'note' => '애플리케이션의 카테고리 섹션에 대한 접근 권한 부여.',
    ],
    'categoriesview' => [
        'name' => '카테고리 보기',
    ],
    'categoriescreate' => [
        'name' => '새 카테고리 생성',
    ],
    'categoriesedit' => [
        'name' => '카테고리 편집',
    ],
    'categoriesdelete' => [
        'name' => '카테고리 삭제',
    ],
    'departments' => [
        'name' => '부서',
        'note' => '애플리케이션의 부서 섹션에 대한 접근 권한 부여.',
    ],
    'departmentsview' => [
        'name' => '부서 보기',
    ],
    'departmentscreate' => [
        'name' => '새 부서 생성',
    ],
    'departmentsedit' => [
        'name' => '부서 편집',
    ],
    'departmentsdelete' => [
        'name' => '부서 삭제',
    ],
    'locations' => [
        'name' => '위치',
        'note' => '애플리케이션의 위치 섹션에 대한 접근 권한 부여.',
    ],
    'locationsview' => [
        'name' => '위치 보기',
    ],
    'locationscreate' => [
        'name' => '새 위치 생성',
    ],
    'locationsedit' => [
        'name' => '위치 편집',
    ],
    'locationsdelete' => [
        'name' => '위치 삭제',
    ],
    'status-labels' => [
        'name' => '상태 꼬리표',
        'note' => '자산에서 사용하는 애플리케이션의 상태 라벨 섹션에 대한 접근 권한을 부여',
    ],
    'statuslabelsview' => [
        'name' => '상태 라벨 보기',
    ],
    'statuslabelscreate' => [
        'name' => '새 상태 라벨 생성',
    ],
    'statuslabelsedit' => [
        'name' => '상태 라벨 편집',
    ],
    'statuslabelsdelete' => [
        'name' => '상태 라벨 삭제',
    ],
    'custom-fields' => [
        'name' => '사용자 정의 항목들',
        'note' => '자산에서 사용하는 애플리케이션의 사용자 정의 필드 섹션에 대한 접근 권한을 부여',
    ],
    'customfieldsview' => [
        'name' => '사용자 정의 필드 보기',
    ],
    'customfieldscreate' => [
        'name' => '새 사용자 정의 필드 생성',
    ],
    'customfieldsedit' => [
        'name' => '사용자 정의 필드 편집',
    ],
    'customfieldsdelete' => [
        'name' => '사용자 정의 필드 삭제',
    ],
    'suppliers' => [
        'name' => '공급자',
        'note' => '애플리케이션의 공급업체 섹션에 대한 접근 권한을 부여',
    ],
    'suppliersview' => [
        'name' => '공급업체 보기',
    ],
    'supplierscreate' => [
        'name' => '새 공급업체 생성',
    ],
    'suppliersedit' => [
        'name' => '공급업체 편집',
    ],
    'suppliersdelete' => [
        'name' => '공급업체 삭제',
    ],
    'manufacturers' => [
        'name' => '제조업체',
        'note' => '애플리케이션의 제조사 섹션에 대한 접근 권한을 부여합니다.',
    ],
    'manufacturersview' => [
        'name' => '제조사 보기',
    ],
    'manufacturerscreate' => [
        'name' => '새 제조사 생성',
    ],
    'manufacturersedit' => [
        'name' => '제조사 편집',
    ],
    'manufacturersdelete' => [
        'name' => '제조사 삭제',
    ],
    'companies' => [
        'name' => '회사들',
        'note' => '애플리케이션의 회사 섹션에 대한 접근 권한을 부여합니다.',
    ],
    'companiesview' => [
        'name' => '회사 보기',
    ],
    'companiescreate' => [
        'name' => '새 회사 생성',
    ],
    'companiesedit' => [
        'name' => '회사 편집',
    ],
    'companiesdelete' => [
        'name' => '회사 삭제',
    ],
    'user-self-accounts' => [
        'name' => '사용자 본인 계정',
        'note' => '관리자가 아닌 사용자에게 자신의 사용자 계정 일부를 관리할 수 있는 권한 부여',
    ],
    'selftwo-factor' => [
        'name' => '2단계 인증 관리',
        'note' => '사용자가 자신의 계정에 대해 2단계 인증을 활성화, 비활성화 및 관리할 수 있도록 허용',
    ],
    'selfapi' => [
        'name' => 'API 토큰 관리',
        'note' => '사용자가 자신의 API 토큰을 생성, 조회 및 취소할 수 있도록 허용. 사용자 토큰은 이를 생성한 사용자와 동일한 권한을 가짐',
    ],
    'selfedit-location' => [
        'name' => '위치 편집',
        'note' => '사용자가 자신의 사용자 계정에 연결된 위치를 편집할 수 있도록 허용',
    ],
    'selfcheckout-assets' => [
        'name' => '자산 셀프 반출',
        'note' => '사용자가 관리자 개입 없이 자신에게 자산을 반출할 수 있도록 허용',
    ],
    'selfview-purchase-cost' => [
        'name' => '구매 비용 조회',
        'note' => '사용자가 계정 화면에서 항목의 구매 비용을 조회할 수 있도록 허용',
    ],

    'depreciations' => [
        'name' => '감가상각 관리',
        'note' => '사용자가 자산 감가상각 세부 정보를 관리하고 조회할 수 있도록 허용',
    ],
    'depreciationsview' => [
        'name' => '감가상각 세부 정보 조회',
    ],
    'depreciationsedit' => [
        'name' => '감가상각 설정 편집',
    ],
    'depreciationsdelete' => [
        'name' => '감가상각 기록 삭제',
    ],
    'depreciationscreate' => [
        'name' => '감가상각 기록 생성',
    ],

    'grant_all' => ':area에 대한 모든 권한 부여',
    'deny_all' => ':area에 대한 모든 권한 거부',
    'inherit_all' => '권한 그룹에서 :area에 대한 모든 권한 상속',
    'grant' => ':area에 대한 권한 부여',
    'deny' => ':area에 대한 권한 거부',
    'inherit' => '권한 그룹에서 :area에 대한 권한 상속',
    'use_groups' => '손쉬운 관리를 위해 개별 권한을 할당하는 대신 권한 그룹 사용을 강력히 권장합니다.',

];
