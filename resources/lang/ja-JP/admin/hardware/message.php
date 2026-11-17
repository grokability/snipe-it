<?php

return [

    'undeployable' => '次の資産は配備できないため、チェックアウト対象から除外されました: :asset_tags',
    'does_not_exist' => '資産が存在しません。',
    'does_not_exist_var' => 'タグ:asset_tag を持つアセットが見つかりません。',
    'no_tag' => 'アセットタグが提供されていません。',
    'does_not_exist_or_not_requestable' => 'その資産は存在しないか要求可能ではありません。',
    'assoc_users' => 'この資産はユーザーに貸し出されているため削除できません。資産を返却後、もう一度、やり直して下さい。 ',
    'warning_audit_date_mismatch' => 'この資産の次の監査日 (:next_audit_date) は最終監査日 (:last_audit_date) より前です。次の監査日を更新してください。',
    'labels_generated' => 'ラベルの生成に成功しました。',
    'error_generating_labels' => 'ラベルを生成中にエラーが発生しました。',
    'no_assets_selected' => '資産が選択されていません。',

    'create' => [
        'error' => '資産は作成されませんでした。もう一度、やり直して下さい。',
        'success' => '資産は作成されました。',
        'success_no_checkout' => '資産を作成しましたが、資産をチェックアウトする権限がないため、チェックアウトはしていません。',
        'checkout_skipped_no_permission' => '資産を作成しましたが、資産をチェックアウトする権限がないため、指定した割り当て先へのチェックアウトはしていません。',
        'success_linked' => ':tag を持つアセットは正常に作成されました。 <strong><a href=":link" style="color: white;"></a></strong> を表示するにはここをクリックしてください。',
        'multi_success_linked' => 'タグ:links のアセットが正常に作成されました。|:count アセットが正常に作成されました。',
        'partial_failure' => '資産を作成できませんでした。理由: :failures|資産を:count件作成できませんでした。理由: :failures',
        'target_not_found' => [
            'user' => '割り当てられたユーザーが見つかりません。',
            'asset' => '割り当てられた資産が見つかりません。',
            'location' => '割り当てられた場所が見つかりません。',
        ],
    ],

    'update' => [
        'error' => '資産は更新されませんでした。もう一度、やり直して下さい。',
        'success' => '資産は正常に更新されました。',
        'encrypted_warning' => '資産は正常に更新されましたが、権限が原因で暗号化されたカスタム項目がありませんでした',
        'nothing_updated' => 'フィールドが選択されていないため、更新されませんでした。',
        'no_assets_selected' => '資産が選択されていないため、何も更新されませんでした。',
        'assets_do_not_exist_or_are_invalid' => '選択したアセットは更新できません。',
    ],

    'bulk_update' => [
        'success' => '資産を更新しました。|資産を:count件更新しました。',
        'partial' => '資産を:success件更新し、:failed件は失敗しました。詳しくは結果をご確認ください。',
        'error' => '資産を更新できませんでした。詳しくは結果をご確認ください。',
    ],

    'restore' => [
        'error' => '資産は復元されませんでした。もう一度、やり直して下さい。',
        'success' => '資産は正常に復元されました。',
        'bulk_success' => '資産は正常に復元されました。',
        'nothing_updated' => '資産が選択されていないため、何も復元されませんでした。',
    ],

    'audit' => [
        'error' => '資産監査に失敗しました: :error ',
        'success' => '資産の監査ログに記録しました。',
    ],

    'deletefile' => [
        'error' => 'ファイルが削除できませんでした。もう一度、やり直して下さい。',
        'success' => 'ファイルは正常に削除されました。',
    ],

    'upload' => [
        'error' => 'ファイルがアップロードできませんでした。もう一度、やり直して下さい。',
        'success' => 'ファイルが正常にアップロードされました。',
        'nofiles' => 'アップロードするファイルが選択されていないか、アップロードしようとしているファイルが大き過ぎます。',
        'invalidfiles' => 'いずれかのファイルが大き過ぎるか、ファイルタイプが許可されていません。許可されているファイルタイプ（png, gif, jpg, doc, docx, pdf, and txt）',
    ],

    'import' => [
        'import_button' => 'インポートを実行',
        'error' => 'いくつかの項目は正しくインポートされませんでした。',
        'errorDetail' => '以下のアイテムはエラーのためインポートできませんでした',
        'success' => 'ファイルはインポートされました。',
        'file_delete_success' => 'ファイルを削除しました。',
        'file_delete_error' => 'ファイルが削除出来ませんでした。',
        'file_missing' => '選択されたファイルがありません',
        'file_already_deleted' => '選択したファイルは既に削除されています',
        'file_missing_on_disk' => 'このインポートのファイルがディスク上にありません。Snipe-IT以外から削除された可能性があります。この履歴を削除してファイルを再アップロードしてください。',
        'file_empty' => 'このファイルにはデータ行がないため、インポートできません。',
        'already_processing' => 'このインポートは別のユーザーが処理中です。完了してからもう一度お試しください。',
        'header_row_missing' => 'このファイルには認識できる見出し行がありません。この履歴を削除してファイルを再アップロードしてください。',
        'header_row_has_malformed_characters' => 'ヘッダー行の1つ以上の属性に不正な形式のUTF-8文字が含まれています',
        'content_row_has_malformed_characters' => 'コンテンツの最初の行の1つまたは複数の属性に不正な形式のUTF-8文字が含まれています',
        'transliterate_failure' => ':encoding から UTF-8 への変換に失敗しました。入力中の無効な文字が原因です。',
        'bulk_delete' => [
            'button' => '選択したファイルを削除（:count件）',
            'confirm_title' => '選択したインポートファイルを削除しますか？',
            'confirm_body' => 'インポートファイルを:count件完全に削除します。この操作は取り消せません。',
            'confirm_button' => '削除',
            'success' => 'インポートファイルを削除しました。|インポートファイルを:count件削除しました。',
            'skipped' => '削除権限がないため、:count件のファイルをスキップしました。',
            'select_all' => 'このページの全ファイルを選択',
            'select_row' => ':fileを一括削除の対象にする',
        ],
        'row_count' => '{0} このファイルにデータ行はありません|{1} インポートするデータ: :count行|[2,*] インポートするデータ: :count行',
        'summary' => [
            'created' => '作成',
            'updated' => '更新',
            'skipped' => '重複のためスキップ',
            'errored' => 'エラー',
            'no_changes' => 'インポートは完了しましたが、作成または更新されたデータはありません。通常は、対象のデータがすでに存在するため全行がスキップされた場合に発生します。想定と異なる場合は、以下の件数を確認し、CSVまたはインポート種別を見直してください。',
        ],
        'type_required' => '続行する前にインポート種別を選択してください。',
        'processing' => 'インポートを処理しています。完了するまでこのページを閉じないでください。',
        'backup_running' => 'インポート前のバックアップを作成しています。ファイルが大きい場合は時間がかかることがあります。',
        'backup_label' => 'インポート前のバックアップ',
        'backup_complete' => 'バックアップ完了',
        'import_label' => 'インポート',
        'required_fields_missing' => '次の必須項目が割り当てられていません: :fields',
        'history' => [
            'missing_asset_tag_identity' => '（資産タグなし）',
            'missing_asset_tag_message' => '資産タグが入力されていないため、この行をスキップしました。',
            'asset_not_found_message' => 'このタグの資産が存在しません。先に資産をインポートしてから、履歴のインポートをやり直してください。',
            'target_not_matched_message' => '「:name」に一致する:target_typeがありません。ユーザーの場合は、手順1で照合方法を切り替えるか、先にユーザーを作成してください。場所の場合は、CSVの場所名が既存の場所と完全に一致しているか確認してください。',
            'invalid_target_type_message' => '割り当て先の種類「:value」は認識できません。「user」（ユーザー）または「location」（場所）を指定するか、空欄にしてユーザーとして扱ってください。',
        ],
        'wizard' => [
            'step_type' => '種別を選択',
            'step_map' => '項目を割り当て',
            'step_preview' => 'プレビュー',
            'back' => '戻る',
            'next' => '次へ',
            'preview_button' => 'プレビュー',
            'process' => 'インポートを実行',
            'preview_intro' => '項目の割り当てを適用した先頭:count行を表示しています。インポート前に割り当てを修正する場合は「戻る」を選択してください。',
        ],
    ],

    'delete' => [
        'confirm' => 'この資産を削除してもよろしいですか？',
        'error' => '資産を削除する際に問題が発生しました。もう一度やり直して下さい。',
        'assigned_to_error' => '{1}アセットタグ: :asset_tag は現在チェックアウトされています。削除する前にこのデバイスを確認してください。 [2,*]アセットタグ: :asset_tag は現在チェックアウトされています。削除する前にこれらのデバイスを確認してください。',
        'nothing_updated' => '資産が選択されていないため、削除されませんでした。',
        'success' => '資産は正常に削除されました。',
    ],

    'checkout' => [
        'error' => '資産はチェックアウトされませんでした。もう一度、やり直して下さい。',
        'success' => '資産は正常にチェックアウトされました。',
        'user_does_not_exist' => 'その利用者は不正です。もう一度、やり直して下さい。',
        'not_available' => 'この資産はチェックアウトできません!',
        'no_assets_selected' => 'リストから少なくとも1つの資産を選択する必要があります',
    ],

    'multi-checkout' => [
        'error' => '資産をチェックアウトできませんでした。もう一度お試しください。|資産をチェックアウトできませんでした。もう一度お試しください。',
        'success' => '資産をチェックアウトしました。|資産をチェックアウトしました。',
    ],

    'multi-checkin' => [
        'error' => '資産をチェックインできませんでした。もう一度お試しください。|資産をチェックインできませんでした。もう一度お試しください。',
        'success' => '資産をチェックインしました。|資産をチェックインしました。',
        'no_assets_selected' => '資産を1件以上選択してください。',
    ],

    'multi-audit' => [
        'success' => '資産を:count件監査しました。|資産を:count件監査しました。',
        'partial_error' => '資産を:success件監査し、:failed件は失敗しました。以下のエラーを確認して、もう一度お試しください。|資産を:success件監査し、:failed件は失敗しました。以下のエラーを確認して、もう一度お試しください。',
        'no_assets_selected' => '資産を1件以上選択してください。',
    ],

    'checkin' => [
        'error' => '資産はチェックインされませんでした。もう一度、やり直して下さい。',
        'success' => '資産は正常にチェックインされました。',
        'updated_without_checkin' => 'Asset was already checked in, but the form fields you set (status, location, name, notes) were applied anyway.',
        'user_does_not_exist' => 'その利用者は不正です。もう一度、やり直して下さい。',
        'already_checked_in' => 'その資産はすでにチェックインしています。',
        'force_checkin_orphaned_success' => '不正な割り当てを解除しました。',
        'force_checkin_not_orphaned' => 'このアイテムは不正な割り当て状態ではありません。',
        'force_checkin_error' => '不正な割り当てを解除できませんでした。',

    ],

    'requests' => [
        'error' => 'リクエストに失敗しました。もう一度やり直してください。',
        'success' => 'リクエストは正常に送信されました。',
        'canceled' => 'リクエストをキャンセルしました。',
        'cancel' => 'このアイテムのリクエストをキャンセル',
        'duplicate' => 'このアイテムはすでに申請中です。',
        'no_active' => 'キャンセルできる申請はありません。',
        'insufficient_stock' => '在庫が足りないため、この申請に対応できません。先に補充してください。',
        'confirm_cancel_by_admin' => ":userさんの:itemの申請をキャンセルしますか？",
        'no_selection' => '対応する行が選択されていません。',
        'row_stale' => '申請 #:id はすでに保留中ではないため、スキップしました。',
        'row_qty_invalid' => '申請 #:id の数量が正しくないため、スキップしました。',
        'row_user_missing' => '申請 #:id には有効な対象ユーザーがいないため、スキップしました。',
        'row_asset_missing' => '申請 #:id には有効な対象資産がないため、スキップしました。',
        'row_asset_not_requesters' => '申請 #:id をスキップしました：選択した資産は申請者のものではありません。',
        'row_asset_not_available' => '申請 #:id をスキップしました：選択した資産は、このモデルの利用可能な資産ではありません。',
        'row_asset_taken' => '申請 #:id をスキップしました：選択した資産は、送信前に別のユーザーに割り当てられました。',
        'row_company_mismatch' => '申請 #:id をスキップしました：:userはこの会社のアイテムを受け取れません。',
        'row_over_allocated' => '申請 #:id をスキップしました：送信時点で在庫が足りませんでした。',
        'no_target_assets_for_user' => ':userには取り付け先の資産がありません。',
        'no_available_units' => 'このモデルには、貸し出せる資産がありません。',
        'bulk_summary' => ':total件中:fulfilled件の申請に対応しました。',
        'bulk_fulfill_notification_intro' => 'チェックを入れた各申請に対応するとき、次の内容が適用されます：',
    ],

];
