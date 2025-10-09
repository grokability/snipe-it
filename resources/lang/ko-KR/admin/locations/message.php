<?php

return array(

    'does_not_exist' => '장소가 존재하지 않습니다.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => '이 장소는 현재 적어도 한명의 사용자와 연결되어 있어서 삭제할 수 없습니다. 사용자가 더 이상 이 장소를 참조하지 않게 갱신하고 다시 시도해주세요. ',
    'assoc_child_loc'	 => '이 장소는 현재 하나 이상의 하위 장소를 가지고 있기에 삭제 할 수 없습니다. 이 장소의 참조를 수정하고 다시 시도해 주세요. ',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => '현재 위치',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => '장소가 생성되지 않았습니다. 다시 시도해 주세요.',
        'success' => '장소가 생성되었습니다.'
    ),

    'update' => array(
        'error'   => '장소가 갱신되지 않았습니다. 다시 시도해 주세요.',
        'success' => '장소가 갱신되었습니다.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> '이 장소를 삭제하시겠습니까?',
        'error'   => '장소 삭제 중에 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '장소가 삭제되었습니다.'
    )

);
