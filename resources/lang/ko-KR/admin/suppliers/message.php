<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => '공급자가 존재하지 않습니다.',


    'create' => array(
        'error'   => '공급자가 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '공급자가 생성되었습니다.'
    ),

    'update' => array(
        'error'   => '공급자가 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '공급자가 갱신 되었습니다.'
    ),

    'delete' => array(
        'confirm'   => '이 공급자를 삭제 하시겠습니까?',
        'error'   => '공급자 삭제 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '공급자가 삭제되었습니다.',
        'assoc_assets'	 => '이 공급자는 현재 다음 :asset_count 자산과 연결되어 있기에 삭제할 수 없습니다. 이 공급자를 더 이상 참조하기 않게 자산을 수정하고 다시 시도해 주세요. ',
        'assoc_licenses'	 => '이 공급자는 현재 다음 :licenses_count 라이선스와 연결되어 있기에 삭제할 수 없습니다. 이 공급자를 더 이상 참조하기 않게 라이선스을 수정하고 다시 시도해 주세요. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
