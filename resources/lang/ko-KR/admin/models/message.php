<?php

return array(

    'deleted' => '삭제된 자산 모델',
    'does_not_exist' => '모델이 존재하지 않습니다.',
    'no_association' => 'WARNING! The asset model for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => '이 모델은 현재 하나 이상의 자산들과 연결되어 있기에 삭제 할 수 없습니다. 자산들을 삭제하고 다시 삭제하길 시도하세요. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => '모델이 생성되지 않았습니다. 다시 시도하세요.',
        'success' => '모델이 생성되었습니다.',
        'duplicate_set' => '이름, 제조사 그리고 모델 번호가 같은 자산 모델이 존재합니다.',
    ),

    'update' => array(
        'error'   => '모델이 갱신되지 않았습니다. 다시 시도하세요.',
        'success' => '모델이 갱신되었습니다.',
    ),

    'delete' => array(
        'confirm'   => '이 자산 모델을 삭제 하시겠습니까?',
        'error'   => '모델을 삭제하는 중 문제가 발생했습니다. 다시 시도해 주세요.',
        'success' => '모델이 삭제되었습니다.'
    ),

    'restore' => array(
        'error'   		=> '모델이 복원되지 않았습니다. 다시 시도해 주세요.',
        'success' 		=> '모델이 복원되었습니다.'
    ),

    'bulkedit' => array(
        'error'   		=> '변경된 항목이 없어서, 갱신되지 않습니다.',
        'success' 		=> 'Model successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => '선택된 모델이 없기에, 삭제되지 않습니다.',
        'success' 		    => 'Model deleted!|:success_count models deleted!',
        'success_partial' 	=> ': success_count개의 모델이 삭제되었지만, fail_count 개는 관련된 자산이 있기에 삭제할 수 없습니다.'
    ),

);
