<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => '資産が存在しません。',
    'does_not_exist_var' => 'タグ:asset_tag を持つアセットが見つかりません。',
    'no_tag' 	         => 'アセットタグが提供されていません。',
    'does_not_exist_or_not_requestable' => 'その資産は存在しないか要求可能ではありません。',
    'assoc_users'	 	 => 'この資産はユーザーに貸し出されているため削除できません。資産を返却後、もう一度、やり直して下さい。 ',
    'warning_audit_date_mismatch' 	=> 'この資産の次の監査日 (:next_audit_date) は最終監査日 (:last_audit_date) より前です。次の監査日を更新してください。',
    'labels_generated'   => 'ラベルの生成に成功しました。',
    'error_generating_labels' => 'ラベルを生成中にエラーが発生しました。',
    'no_assets_selected' => '資産が選択されていません。',

    'create' => [
        'error'   		=> '資産は作成されませんでした。もう一度、やり直して下さい。',
        'success' 		=> '資産は作成されました。',
        'success_linked' => ':tag を持つアセットは正常に作成されました。 <strong><a href=":link" style="color: white;"></a></strong> を表示するにはここをクリックしてください。',
        'multi_success_linked' => 'タグ:links のアセットが正常に作成されました。|:count アセットが正常に作成されました。',
        'partial_failure' => 'An asset was unable to be created. Reason: :failures|:count assets were unable to be created. Reasons: :failures',
        'target_not_found' => [
            'user' => '割り当てられたユーザーが見つかりません。',
            'asset' => '割り当てられた資産が見つかりません。',
            'location' => '割り当てられた場所が見つかりません。',
        ],
    ],

    'update' => [
        'error'   			=> '資産は更新されませんでした。もう一度、やり直して下さい。',
        'success' 			=> '資産は正常に更新されました。',
        'encrypted_warning' => '資産は正常に更新されましたが、権限が原因で暗号化されたカスタム項目がありませんでした',
        'nothing_updated'	=>  'フィールドが選択されていないため、更新されませんでした。',
        'no_assets_selected'  =>  '資産が選択されていないため、何も更新されませんでした。',
        'assets_do_not_exist_or_are_invalid' => '選択したアセットは更新できません。',
    ],

    'restore' => [
        'error'   		=> '資産は復元されませんでした。もう一度、やり直して下さい。',
        'success' 		=> '資産は正常に復元されました。',
        'bulk_success' 		=> '資産は正常に復元されました。',
        'nothing_updated'   => '資産が選択されていないため、何も復元されませんでした。', 
    ],

    'audit' => [
        'error'   		=> '資産監査に失敗しました: :error ',
        'success' 		=> '資産の監査ログに記録しました。',
    ],


    'deletefile' => [
        'error'   => 'ファイルが削除できませんでした。もう一度、やり直して下さい。',
        'success' => 'ファイルは正常に削除されました。',
    ],

    'upload' => [
        'error'   => 'ファイルがアップロードできませんでした。もう一度、やり直して下さい。',
        'success' => 'ファイルが正常にアップロードされました。',
        'nofiles' => 'アップロードするファイルが選択されていないか、アップロードしようとしているファイルが大き過ぎます。',
        'invalidfiles' => 'いずれかのファイルが大き過ぎるか、ファイルタイプが許可されていません。許可されているファイルタイプ（png, gif, jpg, doc, docx, pdf, and txt）',
    ],

    'import' => [
        'import_button'         => 'Process Import',
        'error'                 => 'いくつかの項目は正しくインポートされませんでした。',
        'errorDetail'           => '以下のアイテムはエラーのためインポートできませんでした',
        'success'               => 'ファイルはインポートされました。',
        'file_delete_success'   => 'ファイルを削除しました。',
        'file_delete_error'      => 'ファイルが削除出来ませんでした。',
        'file_missing' => '選択されたファイルがありません',
        'file_already_deleted' => '選択したファイルは既に削除されています',
        'header_row_has_malformed_characters' => 'ヘッダー行の1つ以上の属性に不正な形式のUTF-8文字が含まれています',
        'content_row_has_malformed_characters' => 'コンテンツの最初の行の1つまたは複数の属性に不正な形式のUTF-8文字が含まれています',
        'transliterate_failure' => ':encoding から UTF-8 への変換に失敗しました。入力中の無効な文字が原因です。'
    ],


    'delete' => [
        'confirm'   	=> 'この資産を削除してもよろしいですか？',
        'error'   		=> '資産を削除する際に問題が発生しました。もう一度やり直して下さい。',
        'assigned_to_error' => '{1}アセットタグ: :asset_tag は現在チェックアウトされています。削除する前にこのデバイスを確認してください。 [2,*]アセットタグ: :asset_tag は現在チェックアウトされています。削除する前にこれらのデバイスを確認してください。',
        'nothing_updated'   => '資産が選択されていないため、削除されませんでした。',
        'success' 		=> '資産は正常に削除されました。',
    ],

    'checkout' => [
        'error'   		=> '資産はチェックアウトされませんでした。もう一度、やり直して下さい。',
        'success' 		=> '資産は正常にチェックアウトされました。',
        'user_does_not_exist' => 'その利用者は不正です。もう一度、やり直して下さい。',
        'not_available' => 'この資産はチェックアウトできません!',
        'no_assets_selected' => 'リストから少なくとも1つの資産を選択する必要があります',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> '資産はチェックインされませんでした。もう一度、やり直して下さい。',
        'success' 		=> '資産は正常にチェックインされました。',
        'user_does_not_exist' => 'その利用者は不正です。もう一度、やり直して下さい。',
        'already_checked_in'  => 'その資産はすでにチェックインしています。',

    ],

    'requests' => [
        'error'   		=> 'リクエストに失敗しました。もう一度やり直してください。',
        'success' 		=> 'リクエストは正常に送信されました。',
        'canceled'      => 'リクエストをキャンセルしました。',
        'cancel'        => 'このアイテムのリクエストをキャンセル',
    ],

];
