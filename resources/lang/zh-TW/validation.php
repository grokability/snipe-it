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

    'accepted' => ':attribute 欄位必須被接受。',
    'accepted_if' => '當 :other 為 :value 時，:attribute 欄位必須被接受。',
    'active_url' => ':attribute 欄位必須是有效的 URL。',
    'after' => ':attribute 欄位必須是 :date 之後的日期。',
    'after_or_equal' => ':attribute 欄位必須是 :date 或之後的日期。',
    'alpha' => ':attribute 欄位只能包含字母。',
    'alpha_dash' => ':attribute 欄位只能包含字母、數字、連字號和底線。',
    'alpha_num' => ':attribute 欄位只能包含字母和數字。',
    'array' => ':attribute 欄位必須是陣列。',
    'ascii' => ':attribute 欄位只能包含單位元組英數字元和符號。',
    'before' => ':attribute 欄位必須是 :date 之前的日期。',
    'before_or_equal' => ':attribute 欄位必須是 :date 或之前的日期。',
    'between' => [
        'array' => ':attribute 欄位必須包含 :min 到 :max 個項目。',
        'file' => ':attribute 欄位必須介於 :min 到 :max KB 之間。',
        'numeric' => ':attribute 欄位必須介於 :min 到 :max 之間。',
        'string' => ':attribute 欄位必須介於 :min 到 :max 個字元之間。',
    ],
    'valid_regex' => '正規表達式無效。',
    'boolean' => ':attribute 欄位必須為 true 或 false。',
    'can' => ':attribute 欄位包含未授權的值。',
    'confirmed' => ':attribute 欄位確認不符。',
    'contains' => ':attribute 欄位缺少必要的值。',
    'current_password' => '密碼不正確。',
    'date' => ':attribute 欄位必須是有效的日期。',
    'date_equals' => ':attribute 欄位必須是等於 :date 的日期。',
    'date_format' => ':attribute 欄位必須符合格式 :format。',
    'decimal' => ':attribute 欄位必須有 :decimal 位小數。',
    'declined' => ':attribute 欄位必須被拒絕。',
    'declined_if' => '當 :other 為 :value 時，:attribute 欄位必須被拒絕。',
    'different' => ':attribute 欄位和 :other 必須不同。',
    'digits' => ':attribute 欄位必須是 :digits 位數字。',
    'digits_between' => ':attribute 欄位必須介於 :min 到 :max 位數字之間。',
    'dimensions' => ':attribute 欄位的圖片尺寸無效。',
    'distinct' => ':attribute 具有重複值',
    'doesnt_end_with' => ':attribute 欄位不能以下列之一結尾：:values。',
    'doesnt_start_with' => ':attribute 欄位不能以下列之一開頭：:values。',
    'email' => ':attribute 欄位必須是有效的電子郵件地址。',
    'ends_with' => ':attribute 欄位必須以下列之一結尾：:values。',
    'enum' => '選擇的 :attribute 無效',
    'exists' => '選擇的 :attribute 無效',
    'extensions' => ':attribute 欄位必須有以下副檔名之一：:values。',
    'file' => ':attribute 欄位必須是檔案。',
    'filled' => ':attribute 欄位必須有值。',
    'gt' => [
        'array' => ':attribute 欄位必須多於 :value 個項目。',
        'file' => ':attribute 欄位必須大於 :value KB。',
        'numeric' => ':attribute 欄位必須大於 :value。',
        'string' => ':attribute 欄位必須多於 :value 個字元。',
    ],
    'gte' => [
        'array' => ':attribute 欄位必須有 :value 個或更多項目。',
        'file' => ':attribute 欄位必須大於或等於 :value KB。',
        'numeric' => ':attribute 欄位必須大於或等於 :value。',
        'string' => ':attribute 欄位必須大於或等於 :value 個字元。',
    ],
    'hex_color' => ':attribute 欄位必須是有效的十六進位色碼。',
    'image' => ':attribute 欄位必須是圖片。',
    'import_field_empty' => ':fieldname 的值不能為空。',
    'in' => '選擇的 :attribute 無效',
    'in_array' => ':attribute 欄位必須存在於 :other 中。',
    'integer' => ':attribute 欄位必須是整數。',
    'ip' => ':attribute 欄位必須是有效的 IP 位址。',
    'ipv4' => ':attribute 欄位必須是有效的 IPv4 位址。',
    'ipv6' => ':attribute 欄位必須是有效的 IPv6 位址。',
    'json' => ':attribute 欄位必須是有效的 JSON 字串。',
    'list' => ':attribute 欄位必須是清單。',
    'lowercase' => ':attribute 欄位必須為小寫。',
    'lt' => [
        'array' => ':attribute 欄位必須少於 :value 個項目。',
        'file' => ':attribute 欄位必須小於 :value KB。',
        'numeric' => ':attribute 欄位必須小於 :value。',
        'string' => ':attribute 欄位必須少於 :value 個字元。',
    ],
    'lte' => [
        'array' => ':attribute 欄位不能超過 :value 個項目。',
        'file' => ':attribute 欄位必須小於或等於 :value KB。',
        'numeric' => ':attribute 欄位必須小於或等於 :value。',
        'string' => ':attribute 欄位必須小於或等於 :value 個字元。',
    ],
    'mac_address' => ':attribute 欄位必須是有效的 MAC 位址。',
    'max' => [
        'array' => ':attribute 欄位不能超過 :max 個項目。',
        'file' => ':attribute 欄位不能超過 :max KB。',
        'numeric' => ':attribute 欄位不能超過 :max。',
        'string' => ':attribute 欄位不能超過 :max 個字元。',
    ],
    'max_digits' => ':attribute 欄位不能超過 :max 位數字。',
    'mimes' => ':attribute 欄位必須是以下類型的檔案：:values。',
    'mimetypes' => ':attribute 欄位必須是以下類型的檔案：:values。',
    'min' => [
        'array' => ':attribute 欄位至少必須有 :min 個項目。',
        'file' => ':attribute 欄位至少必須有 :min KB。',
        'numeric' => ':attribute 欄位至少必須為 :min。',
        'string' => ':attribute 欄位至少必須有 :min 個字元。',
    ],
    'min_digits' => ':attribute 欄位至少必須有 :min 位數字。',
    'missing' => ':attribute 欄位必須不存在。',
    'missing_if' => '當 :other 為 :value 時，:attribute 欄位必須不存在。',
    'missing_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須不存在。',
    'missing_with' => '當 :values 存在時，:attribute 欄位必須不存在。',
    'missing_with_all' => '當 :values 均存在時，:attribute 欄位必須不存在。',
    'multiple_of' => ':attribute 欄位必須是 :value 的倍數。',
    'not_in' => '選擇的 :attribute 無效',
    'not_regex' => ':attribute 欄位格式無效。',
    'numeric' => ':attribute 欄位必須是數字。',
    'password' => [
        'letters' => ':attribute 欄位必須包含至少一個字母。',
        'mixed' => ':attribute 欄位必須包含至少一個大寫字母和一個小寫字母。',
        'numbers' => ':attribute 欄位必須包含至少一個數字。',
        'symbols' => ':attribute 欄位必須包含至少一個符號。',
        'uncompromised' => '所提供的 :attribute 已出現在資料外洩事件中。請選擇其他 :attribute。',
    ],
    'percent' => '當折舊類型為百分比時，折舊最低值必須介於 0 到 100 之間。',

    'present' => '：屬性字段必須存在。',
    'present_if' => '當 :other 為 :value 時，:attribute 欄位必須存在。',
    'present_unless' => '除非 :other 為 :value，否則 :attribute 欄位必須存在。',
    'present_with' => '當 :values 存在時，:attribute 欄位必須存在。',
    'present_with_all' => '當 :values 均存在時，:attribute 欄位必須存在。',
    'prohibited' => ':attribute 欄位是禁止的。',
    'prohibited_if' => '當 :other 為 :value 時，:attribute 欄位是禁止的。',
    'prohibited_unless' => '除非 :other 在 :values 中，否則 :attribute 欄位是禁止的。',
    'prohibits' => ':attribute 欄位禁止 :other 出現。',
    'regex' => ':attribute 欄位格式無效。',
    'required' => ':attribute 欄位必填',
    'required_array_keys' => ':attribute 欄位必須包含以下條目：:values。',
    'required_if' => ':attribute 欄位在 :other 是 :value 時是必填的',
    'required_if_accepted' => '當 :other 被接受時，:attribute 欄位必填。',
    'required_if_declined' => '當 :other 被拒絕時，:attribute 欄位必填。',
    'required_unless' => '需要：屬性字段，除非：other is in：values。',
    'required_with' => '當設定 :value 時，:attribute 欄位必填',
    'required_with_all' => '當 :values 均存在時，:attribute 欄位必填。',
    'required_without' => '當設定非 :value 時，:attribute 欄位必填',
    'required_without_all' => '當不存在：值時，需要：屬性字段。',
    'same' => ':attribute 欄位必須與 :other 相符。',
    'size' => [
        'array' => ':attribute 欄位必須包含 :size 個項目。',
        'file' => ':attribute 欄位必須為 :size KB。',
        'numeric' => ':attribute 欄位必須為 :size。',
        'string' => ':attribute 欄位必須為 :size 個字元。',
    ],
    'starts_with' => ':attribute 欄位必須以下列之一開頭：:values。',
    'string' => ':attribute 必須是字串',
    'two_column_unique_undeleted' => ':attribute 在 :table1 和 :table2 中必須是唯一的。',
    'unique_undeleted' => ':attribute 必須是唯一值',
    'non_circular' => ':attribule 屬性不能建立一個循環參考',
    'not_array' => ':attribute 不能是陣列。',
    'disallow_same_pwd_as_user_fields' => '密碼不可以和使用者名稱相同',
    'letters' => '密碼至少必須包含 1 個字母。',
    'numbers' => '密碼至少必須包含 1 個數字。',
    'case_diff' => '密碼必須使用大小寫混合',
    'symbols' => '密碼必須包含符號',
    'timezone' => ':attribute 欄位必須是有效的時區。',
    'unique' => ':attribute 已被採用',
    'uploaded' => ':attribute 上傳失敗',
    'uppercase' => ':attribute 欄位必須為大寫。',
    'url' => ':attribute 欄位必須是有效的 URL。',
    'ulid' => ':attribute 欄位必須是有效的 ULID。',
    'uuid' => ':attribute 欄位必須是有效的 UUID。',
    'valid_css_color' => ':attribute 欄位必須是有效的 CSS 顏色 (hex、rgb、rgba、hsl 或 hsla)。',
    'fmcs_location' => '管理員設定中已啟用完整多公司支援和位置範圍限定，所選位置與所選公司不相容。',
    'is_unique_across_company_and_location' => ':attribute 在所選公司和位置中必須是唯一的。',

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

    'email_array' => '一個或多個郵件地址不正確',
    'checkboxes' => ':attribute 包含無效選項。',
    'radio_buttons' => ':attribute 無效。',

    'custom' => [
        'alpha_space' => ':attribute 含有無效字元',

        'hashed_pass' => '當前密碼不正確！',
        'dumbpwd' => '該密碼太常見。',
        'statuslabel_type' => '您必須選擇一個有效的狀態標籤',
        'custom_field_not_found' => '此欄位似乎不存在，請再次確認您的自訂欄位名稱。',
        'custom_field_not_found_on_model' => '此欄位似乎存在，但在此資產型號的欄位集中不可用。',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => ':attribute 必須是 YYYY-MM-DD 格式的有效日期',
        'last_audit_date.date_format' => ':attribute 必須是 YYYY-MM-DD hh:mm:ss 格式的有效日期',
        'expiration_date.date_format' => ':attribute 必須是 YYYY-MM-DD 格式的有效日期',
        'termination_date.date_format' => ':attribute 必須是 YYYY-MM-DD 格式的有效日期',
        'expected_checkin.date_format' => ':attribute 必須是 YYYY-MM-DD 格式的有效日期',
        'start_date.date_format' => ':attribute 必須是 YYYY-MM-DD 格式的有效日期',
        'end_date.date_format' => ':attribute 必須是 YYYY-MM-DD 格式的有效日期',
        'invalid_value_in_field' => '此欄位包含無效值',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code>（大小寫混合）可能無法使用。建議改用 <code>samaccountname</code>（全小寫）。',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> 可能不是有效的驗證篩選器。您可能需要使用 <code>uid=</code> '],
        'ldap_filter' => ['regex' => '此值可能不應被括號包覆。'],

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
        'serials.*' => '序號',
        'asset_tags.*' => '資產標籤',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => '此欄位包含無效值',
        'required' => '此欄位為必填',
        'email' => '請輸入有效的電子郵件地址',
    ],

];
