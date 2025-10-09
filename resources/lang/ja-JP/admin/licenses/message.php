<?php

return array(

    'does_not_exist' => 'ライセンスが存在しないか、表示する権限がありません。',
    'user_does_not_exist' => 'ユーザーが存在しないか、表示する権限がありません。',
    'asset_does_not_exist' 	=> 'このライセンスに関連付けられている資産が存在しません。',
    'owner_doesnt_match_asset' => 'ドロップダウンで割り当てられた以上の利用者にライセンスを関連付けようとしています。',
    'assoc_users'	 => 'このライセンスは利用者にチェックアウトされているため削除できません。ライセンスをチェックイン後、もう一度、やり直して下さい。 ',
    'select_asset_or_person' => 'アセットまたはユーザーを選択する必要がありますが、両方を選択する必要はありません。',
    'not_found' => 'ライセンスが見つかりません',
    'seats_available' => ':seat_count',


    'create' => array(
        'error'   => 'ライセンスが作成できませんでした。もう一度、やり直して下さい。',
        'success' => 'ライセンスが作成されました。'
    ),

    'deletefile' => array(
        'error'   => 'ファイルが削除できませんでした。もう一度、やり直して下さい。',
        'success' => 'ファイルは削除されました。',
    ),

    'upload' => array(
        'error'   => 'ファイルがアップロードできませんでした。もう一度、やり直して下さい。',
        'success' => 'ファイルがアップロードされました。',
        'nofiles' => 'アップロードするファイルが選択されていないか、アップロードしようとしているファイルが大き過ぎます。',
        'invalidfiles' => 'ファイルサイズが大きすぎるか、許可されていない形式です。(png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, lic)',
    ),

    'update' => array(
        'error'   => 'ライセンスが更新できませんでした。もう一度、やり直して下さい。',
        'success' => 'ライセンスが更新されました。'
    ),

    'delete' => array(
        'confirm'   => 'このライセンスを削除してもよろしいですか？',
        'error'   => 'ライセンスを削除する際に問題が発生しました。もう一度、やり直して下さい。',
        'success' => 'ライセンスが削除されました。'
    ),

    'checkout' => array(
        'error'   => 'ライセンスのチェックを外す際に問題が発生しました。もう一度、やり直して下さい。',
        'success' => 'ライセンスのチェックを外しました。',
        'not_enough_seats' => '購入可能なライセンスシートが不足しています',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'ライセンスのチェックを入れる際に問題が発生しました。もう一度、やり直して下さい。',
        'not_reassignable' => 'Seat has been used',
        'success' => 'ライセンスのチェックを入れました。'
    ),

);
