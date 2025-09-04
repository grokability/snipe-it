<?php

return array(

    'deleted' => '削除されたサプライヤー',
    'does_not_exist' => '仕入先が存在していません。',


    'create' => array(
        'error'   => '仕入先が作成できませんでした。もう一度試して下さい。',
        'success' => '仕入先が作成されました。'
    ),

    'update' => array(
        'error'   => '仕入先は更新できませんでした。もう一度試して下さい。',
        'success' => '仕入先が更新されました。'
    ),

    'delete' => array(
        'confirm'   => '本当にこの仕入先を削除してよいですか？',
        'error'   => '仕入先を削除する際に問題が発生しました。もう一度試して下さい。',
        'success' => '仕入先が削除されました。',
        'assoc_assets'	 => 'この仕入先は現在:asset_count個の資産に関連付けされているため削除できません。この仕入先を参照しないように更新した上で、もう一度試して下さい。 ',
        'assoc_licenses'	 => 'この仕入先は現在:licences_count個のライセンスに関連付けされているため削除できません。この仕入先を参照しないように更新したうえで、もう一度試してください。 ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
