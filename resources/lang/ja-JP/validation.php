<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeには有効なURLを入力してください。',
    'after' => ':attributeには:dateより後の日付を入力してください。',
    'after_or_equal' => ':attributeには:date以降の日付を入力してください。',
    'alpha' => ':attributeには英字のみ使用できます。',
    'alpha_dash' => ':attributeには英字、数字、ハイフン、アンダースコアのみ使用できます。',
    'alpha_num' => ':attributeには英字と数字のみ使用できます。',
    'array' => ':attributeは配列で指定してください。',
    'ascii' => ':attributeには半角英数字と半角記号のみ使用できます。',
    'before' => ':attributeには:dateより前の日付を入力してください。',
    'before_or_equal' => ':attributeには:date以前の日付を入力してください。',
    'between' => [
        'array' => ':attributeの項目数は:min～:max件にしてください。',
        'file' => ':attributeのファイルサイズは:min～:max KBにしてください。',
        'numeric' => ':attributeは:min～:maxの範囲で入力してください。',
        'string' => ':attributeは:min～:max文字で入力してください。',
    ],
    'valid_regex' => '正規表現が正しくありません。',
    'boolean' => ':attributeにはtrueまたはfalseを指定してください。',
    'can' => ':attributeに許可されていない値が含まれています。',
    'confirmed' => ':attributeと確認用の入力が一致しません。',
    'contains' => ':attributeに必要な値が含まれていません。',
    'current_password' => '現在のパスワードが正しくありません。',
    'date' => ':attributeには有効な日付を入力してください。',
    'date_equals' => ':attributeには:dateと同じ日付を入力してください。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'decimal' => ':attributeは小数点以下:decimal桁で入力してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeには:otherと異なる値を入力してください。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min～:max桁で入力してください。',
    'dimensions' => ':attributeの画像サイズが正しくありません。',
    'distinct' => ':attribute フィールドに重複する値があります。',
    'doesnt_end_with' => ':attributeの末尾には次の値を使用できません: :values',
    'doesnt_start_with' => ':attributeの先頭には次の値を使用できません: :values',
    'email' => ':attributeには有効なメールアドレスを入力してください。',
    'ends_with' => ':attributeの末尾は次のいずれかにしてください: :values',
    'enum' => '選択された :attribute は不正です。',
    'exists' => '選択された :attribute は不正です。',
    'extensions' => ':attributeの拡張子は次のいずれかにしてください: :values',
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attribute フィールドは空に出来ません。',
    'gt' => [
        'array' => ':attributeの項目数は:value件より多くしてください。',
        'file' => ':attributeのファイルサイズは:value KBより大きくしてください。',
        'numeric' => ':attributeは:valueより大きい値にしてください。',
        'string' => ':attributeは:value文字より長くしてください。',
    ],
    'gte' => [
        'array' => ':attributeの項目数は:value件以上にしてください。',
        'file' => ':attributeのファイルサイズは:value KB以上にしてください。',
        'numeric' => ':attributeは:value以上にしてください。',
        'string' => ':attributeは:value文字以上にしてください。',
    ],
    'hex_color' => ':attributeには有効な16進カラーコードを入力してください。',
    'image' => ':attributeには画像ファイルを指定してください。',
    'import_field_empty' => ':fieldname の値は null にはできません。',
    'in' => '選択された :attribute は不正です。',
    'in_array' => ':attributeは:otherに含まれる値にしてください。',
    'integer' => ':attributeには整数を入力してください。',
    'ip' => ':attributeには有効なIPアドレスを入力してください。',
    'ipv4' => ':attributeには有効なIPv4アドレスを入力してください。',
    'ipv6' => ':attributeには有効なIPv6アドレスを入力してください。',
    'json' => ':attributeには有効なJSON文字列を入力してください。',
    'list' => ':attributeはリスト形式で指定してください。',
    'lowercase' => ':attributeには小文字を使用してください。',
    'lt' => [
        'array' => ':attributeの項目数は:value件未満にしてください。',
        'file' => ':attributeのファイルサイズは:value KB未満にしてください。',
        'numeric' => ':attributeは:value未満にしてください。',
        'string' => ':attributeは:value文字未満にしてください。',
    ],
    'lte' => [
        'array' => ':attributeの項目数は:value件以下にしてください。',
        'file' => ':attributeのファイルサイズは:value KB以下にしてください。',
        'numeric' => ':attributeは:value以下にしてください。',
        'string' => ':attributeは:value文字以下にしてください。',
    ],
    'mac_address' => ':attributeには有効なMACアドレスを入力してください。',
    'max' => [
        'array' => ':attributeの項目数は:max件以下にしてください。',
        'file' => ':attributeのファイルサイズは:max KB以下にしてください。',
        'numeric' => ':attributeは:max以下にしてください。',
        'string' => ':attributeは:max文字以下にしてください。',
    ],
    'max_digits' => ':attributeは:max桁以下で入力してください。',
    'mimes' => ':attributeのファイル形式は次のいずれかにしてください: :values',
    'mimetypes' => ':attributeのファイル形式は次のいずれかにしてください: :values',
    'min' => [
        'array' => ':attributeの項目数は:min件以上にしてください。',
        'file' => ':attributeのファイルサイズは:min KB以上にしてください。',
        'numeric' => ':attributeは:min以上にしてください。',
        'string' => ':attributeは:min文字以上にしてください。',
    ],
    'min_digits' => ':attributeは:min桁以上で入力してください。',
    'missing' => ':attributeは指定しないでください。',
    'missing_if' => ':otherが:valueの場合、:attributeは指定しないでください。',
    'missing_unless' => ':otherが:valueでない場合、:attributeは指定しないでください。',
    'missing_with' => ':valuesが存在する場合、:attributeは指定しないでください。',
    'missing_with_all' => ':valuesがすべて存在する場合、:attributeは指定しないでください。',
    'multiple_of' => ':attributeには:valueの倍数を入力してください。',
    'not_in' => '選択された :attribute は不正です。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeには数値を入力してください。',
    'password' => [
        'letters' => ':attributeには英字を1文字以上含めてください。',
        'mixed' => ':attributeには大文字と小文字を1文字以上ずつ含めてください。',
        'numbers' => ':attributeには数字を1文字以上含めてください。',
        'symbols' => ':attributeには記号を1文字以上含めてください。',
        'uncompromised' => '入力した:attributeは漏えいしたデータに含まれています。別の:attributeを使用してください。',
    ],
    'percent' => '減価償却方法がパーセントの場合、残存価額は0～100の範囲で入力してください。',

    'present' => ':attribute フィールドは必須です。',
    'present_if' => ':otherが:valueの場合、:attributeを指定してください。',
    'present_unless' => ':otherが:valueでない場合、:attributeを指定してください。',
    'present_with' => ':valuesが存在する場合、:attributeを指定してください。',
    'present_with_all' => ':valuesがすべて存在する場合、:attributeを指定してください。',
    'prohibited' => ':attributeは指定できません。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは指定できません。',
    'prohibited_unless' => ':otherが:valuesのいずれかでない場合、:attributeは指定できません。',
    'prohibits' => ':attributeが指定されている場合、:otherは指定できません。',
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attribute フィールドは、必須です。',
    'required_array_keys' => ':attributeには次のキーを含めてください: :values',
    'required_if' => ':other が :value の時、:attribute フィールドは必須です。',
    'required_if_accepted' => ':otherが承認されている場合、:attributeは必須です。',
    'required_if_declined' => ':otherが拒否されている場合、:attributeは必須です。',
    'required_unless' => ':other が :value の時でなければ、:attribute フィールドは必須です。',
    'required_with' => ':value が存在する場合、:attribute フィールドは必須です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeは必須です。',
    'required_without' => ':value が存在しな場合、:attribute フィールドは必須です。',
    'required_without_all' => ':value が存在しない場合、:attribute フィールドは必須です。',
    'same' => ':attributeは:otherと同じ値にしてください。',
    'size' => [
        'array' => ':attributeの項目数は:size件にしてください。',
        'file' => ':attributeのファイルサイズは:size KBにしてください。',
        'numeric' => ':attributeには:sizeを入力してください。',
        'string' => ':attributeは:size文字で入力してください。',
    ],
    'starts_with' => ':attributeの先頭は次のいずれかにしてください: :values',
    'string' => ':attribute は文字列にして下さい。',
    'two_column_unique_undeleted' => ':attributeは:table1と:table2で一意である必要があります。 ',
    'unique_undeleted' => ':attribute は 一意の値である必要があります。',
    'non_circular' => ':attribute は、循環参照を作成してはいけません。',
    'parent_must_be_top_level' => '選択した:attributeは最上位の項目にしてください。階層は1階層までです。',
    'must_have_no_children' => 'このアイテムにはすでに子項目があるため、親を設定できません。',
    'parent_within_scope' => '選択した:attributeは、あなたの担当範囲外です。',
    'not_array' => ':attributeには配列を指定できません。',
    'disallow_same_pwd_as_user_fields' => 'パスワードはユーザー名と同じにすることはできません。',
    'letters' => 'パスワードには英字が1文字以上必要です。',
    'numbers' => 'パスワードには数字が1つ以上必要です。',
    'case_diff' => 'パスワードは英字と数字を混在させる必要があります。',
    'symbols' => 'パスワードには記号を含める必要があります',
    'timezone' => ':attributeには有効なタイムゾーンを指定してください。',
    'unique' => ':attribute は、取得済みです。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'uppercase' => ':attributeには大文字を使用してください。',
    'url' => ':attributeには有効なURLを入力してください。',
    'external_url' => ':attributeにはプライベートまたはローカルアドレスを指さない、有効な外部URL（http://またはhttps://）を入力してください。',
    'asset_tag_pattern_placeholder' => '資産タグのパターンには、:placeholdersのプレースホルダーを1つ以上含めてください。含めないと、同期されるすべての資産に同じタグが付き、最初の1件しか保存されません。',
    'custom_http_extras_json' => [
        'not_json' => 'カスタム追加フィールドは、有効なJSONで指定してください。解析エラー：:error',
        'not_array' => 'カスタム追加フィールドは、JSONの配列（[ で始まり ] で終わる）で指定してください。',
        'entry_not_object' => 'カスタム追加フィールドの #:index 番目の項目は、key、label、pathのフィールドを持つJSONオブジェクトにしてください。',
        'missing_field' => 'カスタム追加フィールドの #:index 番目の項目に、必須のフィールド「:field」がありません。',
    ],
    'ulid' => ':attributeには有効なULIDを入力してください。',
    'uuid' => ':attributeには有効なUUIDを入力してください。',
    'valid_css_color' => ':attributeには有効なCSSカラー（hex、rgb、rgba、hsl、hsla）を入力してください。',
    'fmcs_company' => '完全な複数会社対応が有効で、会社未所属のユーザーは許可されていないため、:attributeは必須です。',
    'fmcs_location' => '場所「:location」は:location_companyに属しており、選択した会社と一致しません。',
    'is_unique_across_company_and_location' => ':attributeは、選択した会社と場所の組み合わせ内で一意にしてください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'email_array' => '1 つまたは複数の電子メール アドレスが無効です。',
    'checkboxes' => ':attribute に無効なオプションが含まれています。',
    'radio_buttons' => ':attribute は不正です。',
    'image_file_too_large' => ':attributeは:max以下にしてください。',

    'custom' => [
        'alpha_space' => ':attribute フィールドに、禁止文字列が含まれています。',

        'hashed_pass' => '現在のパスワードが正しくありません。',
        'dumbpwd' => 'そのパスワードはあまりにも脆弱です。',
        'statuslabel_type' => '有効なステータスラベルの種類を選択する必要があります。',
        'custom_field_not_found' => 'このフィールドは存在しません。カスタムフィールド名をご確認ください。',
        'custom_field_not_found_on_model' => 'このフィールドは存在しますが、この資産モデルのフィールドセットでは使用できません。',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => ':attribute は YYYY-MM-DD 形式の有効な日付にして下さい',
        'last_audit_date.date_format' => ':attribute は YYYY-MM-DD hh:mm:ss 形式の有効な日時にして下さい',
        'expiration_date.date_format' => ':attribute は YYYY-MM-DD 形式の有効な日付にして下さい',
        'termination_date.date_format' => ':attribute は YYYY-MM-DD 形式の有効な日付にして下さい',
        'expected_checkin.date_format' => ':attribute は YYYY-MM-DD 形式の有効な日付にして下さい',
        'start_date.date_format' => ':attribute は YYYY-MM-DD 形式の有効な日付にして下さい',
        'end_date.date_format' => ':attribute は YYYY-MM-DD 形式の有効な日付にして下さい',
        'invalid_value_in_field' => 'このフィールドに含まれる値が無効です',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code>（大文字と小文字の混在）は動作しない可能性があります。代わりに小文字の<code>samaccountname</code>を使用してください。',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code>は有効な認証フィルターではない可能性があります。通常は<code>uid=</code>を使用します。'],
        'ldap_filter' => ['regex' => 'この値は通常、括弧で囲まずに入力します。'],

    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'serials.*' => 'シリアル番号',
        'asset_tags.*' => '資産タグ',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'このフィールドに含まれる値が無効です',
        'required' => 'この項目は必須です。',
        'email' => '有効なメールアドレスを入力してください。',
    ],

];
