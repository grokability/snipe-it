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

    'accepted' => '必须接受 :attribute 字段。',
    'accepted_if' => '当 :other 是 :value 时必须接受 :attribute 字段。',
    'active_url' => ':attribute 字段必须是一个有效的 URL。',
    'after' => ':attribute 字段必须在 :date 之后。',
    'after_or_equal' => ':attribute 字段必须是晚于或等于 :date 的日期。',
    'alpha' => ':attribute 字段只能包含字母',
    'alpha_dash' => ':attribute 字段只能包含字母、数字、破折号和下划线。',
    'alpha_num' => ':attribute 字段只能包含字母和数字。',
    'array' => ':attribute 字段必须是一个数组。',
    'ascii' => ':attribute 字段只能包含单字节字母和符号。',
    'before' => ':attribute 字段必须在 :date 之前。',
    'before_or_equal' => ':attribute 字段必须早于或等于 :date 的日期。',
    'between' => [
        'array' => ':attribute 字段必须在 :min 与 :max 之间。',
        'file' => ':attribute 字段必须介于 :min 到 :max kb 之间。',
        'numeric' => ':attribute 字段必须在 :min 到 :max 之间。',
        'string' => ':attribute 字段必须在 :min 和 :max 字符之间。',
    ],
    'valid_regex' => '正则表达式无效。',
    'boolean' => '：属性字段必须为true或false。',
    'can' => ':attribute 字段包含一个未授权的值。',
    'confirmed' => ':attribute 字段确认不匹配。',
    'contains' => ':attribute 字段缺少必需的值。',
    'current_password' => '密码不正确。',
    'date' => ':attribute 字段必须是一个有效的日期。',
    'date_equals' => ':attribute 字段必须等于 :date 的日期。',
    'date_format' => ':attribute 字段必须与格式 :format 匹配。',
    'decimal' => ':attribute 字段必须有 :decimal 十进制小数位。',
    'declined' => ':attribute 字段必须被拒绝。',
    'declined_if' => '当 :other 是 :value 时必须拒绝 :attribute 字段。',
    'different' => ':attribute 字段和 :other 必须不同。',
    'digits' => ':attribute 字段必须是 :digits 数字',
    'digits_between' => ':attribute 字段必须介于 :min 到 :max 位数字之间。',
    'dimensions' => ':attribute 字段的图像尺寸无效。',
    'distinct' => ':attribute 字段有一个重复的值。',
    'doesnt_end_with' => ':attribute 字段不能以下列之一结尾：:values 。',
    'doesnt_start_with' => ':attribute 字段不能以下列之一开始：:values 。',
    'email' => ':attribute 字段必须是一个有效的电子邮件地址。',
    'ends_with' => ':attribute 字段必须以下列之一结尾：:values 。',
    'enum' => '选择的 :attribute 无效',
    'exists' => '选择的 :attribute 无效',
    'extensions' => ':attribute 字段必须有以下扩展之一： :values 。',
    'file' => ':attribute 字段必须是一个文件。',
    'filled' => ':attribute 必须有一个值。',
    'gt' => [
        'array' => ':attribute 字段必须超过 :value 项。',
        'file' => ':attribute 字段必须大于 :value kb。',
        'numeric' => ':attribute 字段必须大于 :value 。',
        'string' => ':attribute 字段必须大于 :value 字符。',
    ],
    'gte' => [
        'array' => ':attribute 字段必须包含 :value 项或更多项。',
        'file' => ':attribute 字段必须大于或等于 :value kb。',
        'numeric' => ':attribute 字段必须大于或等于 :value 。',
        'string' => ':attribute 必须大于或等于 :value 字符。',
    ],
    'hex_color' => ':attribute 必须是一个有效的十六进制颜色。',
    'image' => ':attribute 字段必须是一个图像。',
    'import_field_empty'    => ':fieldname 的值不能为空。',
    'in' => '选择的 :attribute 无效',
    'in_array' => ':attribute 字段必须在 :other 中存在。',
    'integer' => ':attribute 字段必须是整数。',
    'ip' => ':attribute 字段必须是一个有效的 IP 地址。',
    'ipv4' => ':attribute 字段必须是有效的 IPv4 地址。',
    'ipv6' => ':attribute 字段必须是有效的 IPv6 地址。',
    'json' => ':attribute 字段必须是有效的 JSON 字符串。',
    'list' => ':attribute 字段必须是一个列表。',
    'lowercase' => ':attribute 字段必须是小写。',
    'lt' => [
        'array' => ':attribute 字段必须小于 :value 项。',
        'file' => ':attribute 字段必须小于 :value kb。',
        'numeric' => ':attribute 字段必须小于 :value 。',
        'string' => ':attribute 字段必须小于 :value 个字符。',
    ],
    'lte' => [
        'array' => ':attribute 字段不能超过 :value 项。',
        'file' => ':attribute 字段必须小于或等于 :value kb。',
        'numeric' => ':attribute 字段必须小于或等于 :value。',
        'string' => ':attribute 字段必须小于或等于 :value 个字符。',
    ],
    'mac_address' => ':attribute 字段必须是一个有效的 MAC 地址。',
    'max' => [
        'array' => ':attribute 字段不能超过 :max 项。',
        'file' => ':attribute 字段不能大于 :max kb。',
        'numeric' => ':attribute 字段不能大于 :max 。',
        'string' => ':attribute 字段不能大于 :max 个字符。',
    ],
    'max_digits' => ':attribute 字段不能超过 :max 位数。',
    'mimes' => ':attribute 字段必须是类型为: :values 的文件。',
    'mimetypes' => ':attribute 字段必须是类型为: :values 的文件。',
    'min' => [
        'array' => ':attribute 字段必须至少包含 :min 项。',
        'file' => ':attribute 字段必须至少 :min kb。',
        'numeric' => ':attribute 字段必须至少 :min。',
        'string' => ':attribute 字段必须至少 :min 字符。',
    ],
    'min_digits' => ':attribute 字段必须至少有 :min 个数字。',
    'missing' => ':attribute 字段必须缺失。',
    'missing_if' => '当 :other 是 :value 时 :attribute 字段必须缺失。',
    'missing_unless' => ':attribute 字段必须确实，除非 :other 是 :value 。',
    'missing_with' => '当 :values 存在时，:attribute 字段必须缺失。',
    'missing_with_all' => '当 :values 存在时，:attribute 字段必须缺失。',
    'multiple_of' => ':attribute 字段必须是 :value 的倍数。',
    'not_in' => '选择的 :attribute 无效',
    'not_regex' => ':attribute 字段格式无效。',
    'numeric' => ':attribute 字段必须是一个数字。',
    'password' => [
        'letters' => ':attribute 字段必须包含至少一个字母。',
        'mixed' => ':attribute 字段必须至少包含一个大写字母和一个小写字母。',
        'numbers' => ':attribute 字段必须包含至少一个数字。',
        'symbols' => ':attribute 字段必须至少包含一个符号。',
        'uncompromised' => '给定的 :attribute 字段出现在数据泄漏中。请选择一个不同的 :attribute 。',
    ],
    'percent'       => '当折旧类型为百分比时，折旧的最小值必须在0到100之间。',

    'present' => ':attribute 字段必填',
    'present_if' => '当 :other 为 :value 时，:attribute 字段必须存在。',
    'present_unless' => ':attribute 字段必须存在，除非 :other 是 :value 。',
    'present_with' => '当 :values 存在时，:attribute 字段必须存在。',
    'present_with_all' => '当 :values 存在时，:attribute 字段必须存在。',
    'prohibited' => ':attribute 字段被禁止。',
    'prohibited_if' => '当:other 是 :value 时，:attribute 字段被禁止。',
    'prohibited_unless' => ':attribute 字段被禁止，除非 :other 在 :values 。',
    'prohibits' => ':attribute 字段禁止 :other 出现。',
    'regex' => ':attribute 字段格式无效。',
    'required' => ':attribute 字段必填',
    'required_array_keys' => ':attribute 字段必须包含：:values 。',
    'required_if' => ':attribute 字段在 :other 是 :value 时是必须的',
    'required_if_accepted' => '当 :other 被接受时，:attribute 字段是必需的。',
    'required_if_declined' => '当 :other 被拒绝时，:attribute 字段是必需的。',
    'required_unless' => '需要：属性字段，除非：other is in：values。',
    'required_with' => ' 当:values 是现在 :attribute 是必需的',
    'required_with_all' => '当 :values 存在时 :attribute 字段是必需的。',
    'required_without' => '当:values 是现在 :attribute 是必需的',
    'required_without_all' => '当不存在：值时，需要：属性字段。',
    'same' => ':attribute 字段必须匹配 :other 。',
    'size' => [
        'array' => ':attribute 字段必须包含 :size 项。',
        'file' => ':attribute 字段必须是 :size kb。',
        'numeric' => ':attribute 字段必须是 :size 。',
        'string' => ':attribute 字段必须是 :size 个字符。',
    ],
    'starts_with' => ':attribute 字段必须以下列之一开始：:values 。',
    'string'               => ':attribute 必须是字符串。',
    'two_column_unique_undeleted' => ':attribute 在 :table1 和 :table2 中必须是唯一的。 ',
    'unique_undeleted'     => ':attribute 属性必须唯一。',
    'non_circular'         => ':attribute 不能创建循环引用。',
    'not_array'            => ':attribute 不能是一个数组。',
    'disallow_same_pwd_as_user_fields' => '密码不能和用户名相同。',
    'letters'              => '密码必须包含至少一个字母。',
    'numbers'              => '密码必须包含至少一个数字。',
    'case_diff'            => '密码必须使用混合大小写。',
    'symbols'              => '密码必须包含符号。',
    'timezone' => ':attribute 字段必须是一个有效的时区。',
    'unique' => ':attribute 已经被采用',
    'uploaded' => ':attribute 无法上传。',
    'uppercase' => ':attribute 字段必须是大写。',
    'url' => ':attribute 字段必须是一个有效的 URL。',
    'ulid' => ':attribute 字段必须是个有效的 ULID。',
    'uuid' => ':attribute 字段必须是一个有效的 UUID。',
    'fmcs_location' => '管理设置中已启用完整的多公司支持及位置范围限定功能，但当前选择的位置与公司不兼容。',


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

    'email_array'      => '一个或多个电子邮件地址无效。',
    'checkboxes'           => ':attribute 包含无效的选项。',
    'radio_buttons'        => ':attribute 无效。',
    
    'custom' => [
        'alpha_space' => ':attribute 字段包含不允许的字符。',

        'hashed_pass'      => '您当前的密码不正确',
        'dumbpwd'          => '那个密码太常见了。',
        'statuslabel_type' => '您必须选择有效的状态标签类型',
        'custom_field_not_found'          => '此字段似乎不存在，请重新检查您的自定义字段名称。',
        'custom_field_not_found_on_model' => '此字段似乎存在，但在此资产型号的字段集上不可用。',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute 必须是 YYYY-MM-DD 格式的有效日期',
        'last_audit_date.date_format'   =>  ':attribute 必须是 YYYY-MM-DD hh:mm:ss 格式的有效日期',
        'expiration_date.date_format'   =>  ':attribute 必须是 YYYY-MM-DD 格式的有效日期',
        'termination_date.date_format'  =>  ':attribute 必须是 YYYY-MM-DD 格式的有效日期',
        'expected_checkin.date_format'  =>  ':attribute 必须是 YYYY-MM-DD 格式的有效日期',
        'start_date.date_format'        =>  ':attribute 必须是 YYYY-MM-DD 格式的有效日期',
        'end_date.date_format'          =>  ':attribute 必须是 YYYY-MM-DD 格式的有效日期',
        'invalid_value_in_field' => '此字段中包含的值无效',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code>(混合大小写) 可能不起作用。您应该改用<code>samaccountname</code>(小写)。'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code>可能不是有效的身份验证过滤器。您可能需要<code>uid=</code> '],
        'ldap_filter' => ['regex' => '这个值可能不应该用括号括起来。'],

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

    'attributes' => [],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => '此字段中包含的值无效',
        'required' => '此字段是必填项',
        'email' => '请输入一个有效的电子邮件地址',
    ],


];
