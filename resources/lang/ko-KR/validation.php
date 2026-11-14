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

    'accepted' => ':attribute 필드는 동의함으로 표시되어야 합니다.',
    'accepted_if' => ':other이(가) :value일 때 :attribute 필드는 동의함으로 표시되어야 합니다.',
    'active_url' => ':attribute 필드는 올바른 URL이어야 합니다.',
    'after' => ':attribute 필드는 :date 이후 날짜여야 합니다.',
    'after_or_equal' => ':attribute 필드는 :date 이후이거나 같은 날짜여야 합니다.',
    'alpha' => ':attribute 필드는 문자만 포함해야 합니다.',
    'alpha_dash' => ':attribute 필드는 문자, 숫자, 대시(-), 밑줄(_)만 포함해야 합니다.',
    'alpha_num' => ':attribute 필드는 문자와 숫자만 포함해야 합니다.',
    'array' => ':attribute 필드는 배열이어야 합니다.',
    'ascii' => ':attribute 필드는 싱글바이트 영숫자와 기호만 포함해야 합니다.',
    'before' => ':attribute 필드는 :date 이전 날짜여야 합니다.',
    'before_or_equal' => ':attribute 필드는 :date 이전이거나 같은 날짜여야 합니다.',
    'between' => [
        'array' => ':attribute 필드는 항목 수가 :min개에서 :max개 사이여야 합니다.',
        'file' => ':attribute 필드는 크기가 :min에서 :max 킬로바이트 사이여야 합니다.',
        'numeric' => ':attribute 필드는 :min에서 :max 사이여야 합니다.',
        'string' => ':attribute 필드는 :min자에서 :max자 사이여야 합니다.',
    ],
    'valid_regex' => '정규 표현식이 잘못되었습니다.',
    'boolean' => ':attribute 필드는 true 또는 false여야 합니다.',
    'can' => ':attribute 필드에 허용되지 않은 값이 포함되어 있습니다.',
    'confirmed' => ':attribute 필드 확인 값이 일치하지 않습니다.',
    'contains' => ':attribute 필드에 필수 값이 누락되었습니다.',
    'current_password' => '비밀번호가 올바르지 않습니다.',
    'date' => ':attribute 필드는 유효한 날짜여야 합니다.',
    'date_equals' => ':attribute 필드는 :date와 동일한 날짜여야 합니다.',
    'date_format' => ':attribute 필드는 :format 형식과 일치해야 합니다.',
    'decimal' => ':attribute 필드는 소수점 :decimal자리여야 합니다.',
    'declined' => ':attribute 필드는 거부되어야 합니다.',
    'declined_if' => ':other가 :value인 경우 :attribute 필드는 거부되어야 합니다.',
    'different' => ':attribute 필드와 :other는 서로 달라야 합니다.',
    'digits' => ':attribute 필드는 :digits자리여야 합니다.',
    'digits_between' => ':attribute 필드는 :min자에서 :max자 사이여야 합니다.',
    'dimensions' => ':attribute 필드의 이미지 크기가 올바르지 않습니다.',
    'distinct' => ':attribute 항목은 중복된 값입니다.',
    'doesnt_end_with' => ':attribute 필드는 다음으로 끝나면 안 됩니다: :values.',
    'doesnt_start_with' => ':attribute 필드는 다음으로 시작하면 안 됩니다: :values.',
    'email' => ':attribute 필드는 유효한 이메일 주소여야 합니다.',
    'ends_with' => ':attribute 필드는 다음 중 하나로 끝나야 합니다: :values.',
    'enum' => '선택한 :attribute 가 부정확 합니다.',
    'exists' => '선택한 :attribute 가 부정확 합니다.',
    'extensions' => ':attribute 필드는 다음 확장자 중 하나여야 합니다: :values.',
    'file' => ':attribute 필드는 파일이어야 합니다.',
    'filled' => ':attribute 항목은 값이 있어야 합니다.',
    'gt' => [
        'array' => ':attribute 필드는 :value개보다 많은 항목이 있어야 합니다.',
        'file' => ':attribute 필드는 :value킬로바이트보다 커야 합니다.',
        'numeric' => ':attribute 필드는 :value보다 커야 합니다.',
        'string' => ':attribute 필드는 :value자보다 커야 합니다.',
    ],
    'gte' => [
        'array' => ':attribute 필드는 :value개 이상의 항목이 있어야 합니다.',
        'file' => ':attribute 필드는 :value킬로바이트 이상이어야 합니다.',
        'numeric' => ':attribute 필드는 :value 이상이어야 합니다.',
        'string' => ':attribute 필드는 :value자 이상이어야 합니다.',
    ],
    'hex_color' => ':attribute 필드는 유효한 16진수 색상이어야 합니다.',
    'image' => ':attribute 필드는 이미지여야 합니다.',
    'import_field_empty' => ':fieldname 값은 비워둘 수 없음',
    'in' => '선택한 :attribute 가 부정확 합니다.',
    'in_array' => ':attribute 필드는 :other에 존재해야 합니다.',
    'integer' => ':attribute 필드는 정수여야 합니다.',
    'ip' => ':attribute 필드는 유효한 IP 주소여야 합니다.',
    'ipv4' => ':attribute 필드는 유효한 IPv4 주소여야 합니다.',
    'ipv6' => ':attribute 필드는 유효한 IPv6 주소여야 합니다.',
    'json' => ':attribute 필드는 유효한 JSON 문자열이어야 합니다.',
    'list' => ':attribute 필드는 목록이어야 합니다.',
    'lowercase' => ':attribute 필드는 소문자여야 합니다.',
    'lt' => [
        'array' => ':attribute 필드는 :value개 미만의 항목을 가져야 합니다.',
        'file' => ':attribute 필드는 :value킬로바이트 미만이어야 합니다.',
        'numeric' => ':attribute 필드는 :value 미만이어야 합니다.',
        'string' => ':attribute 필드는 :value자 미만이어야 합니다.',
    ],
    'lte' => [
        'array' => ':attribute 필드는 :value개를 초과할 수 없습니다.',
        'file' => ':attribute 필드는 :value킬로바이트 이하여야 합니다.',
        'numeric' => ':attribute 필드는 :value 이하여야 합니다.',
        'string' => ':attribute 필드는 :value자 이하여야 합니다.',
    ],
    'mac_address' => ':attribute 필드는 유효한 MAC 주소여야 합니다.',
    'max' => [
        'array' => ':attribute 필드는 :max개를 초과할 수 없습니다.',
        'file' => ':attribute 필드는 :max킬로바이트를 초과할 수 없습니다.',
        'numeric' => ':attribute 필드는 :max를 초과할 수 없습니다.',
        'string' => ':attribute 필드는 :max자를 초과할 수 없습니다.',
    ],
    'max_digits' => ':attribute 필드는 :max자리를 초과할 수 없습니다.',
    'mimes' => ':attribute 필드는 다음 형식의 파일이어야 합니다: :values.',
    'mimetypes' => ':attribute 필드는 다음 형식의 파일이어야 합니다: :values.',
    'min' => [
        'array' => ':attribute 필드는 최소 :min개 이상이어야 합니다.',
        'file' => ':attribute 필드는 최소 :min킬로바이트 이상이어야 합니다.',
        'numeric' => ':attribute 필드는 최소 :min 이상이어야 합니다.',
        'string' => ':attribute 필드는 최소 :min자 이상이어야 함.',
    ],
    'min_digits' => ':attribute 필드는 최소 :min자리 숫자여야 함.',
    'missing' => ':attribute 필드가 없어야 함.',
    'missing_if' => ':other이(가) :value일 때 :attribute 필드가 없어야 함.',
    'missing_unless' => ':other이(가) :value이(가) 아니면 :attribute 필드가 없어야 함.',
    'missing_with' => ':values이(가) 있을 때 :attribute 필드가 없어야 함.',
    'missing_with_all' => ':values이(가) 있을 때 :attribute 필드가 없어야 함.',
    'multiple_of' => ':attribute 필드는 :value의 배수여야 함.',
    'not_in' => '선택한 :attribute 가 부정확 합니다.',
    'not_regex' => ':attribute 필드 형식이 올바르지 않음.',
    'numeric' => ':attribute 필드는 숫자여야 함.',
    'password' => [
        'letters' => ':attribute 필드는 최소 하나 이상의 문자를 포함해야 함.',
        'mixed' => ':attribute 필드는 대문자와 소문자를 각각 하나 이상 포함해야 함.',
        'numbers' => ':attribute 필드는 최소 하나 이상의 숫자를 포함해야 함.',
        'symbols' => ':attribute 필드는 최소 하나 이상의 기호를 포함해야 함.',
        'uncompromised' => '입력한 :attribute이(가) 데이터 유출에 노출되었습니다. 다른 :attribute을(를) 선택하십시오.',
    ],
    'percent' => '감가상각 유형이 백분율인 경우 감가상각 최소값은 0에서 100 사이여야 합니다.',

    'present' => ':attribute 항목이 있어야 합니다.',
    'present_if' => ':other가 :value일 때 :attribute 필드가 있어야 합니다.',
    'present_unless' => ':other가 :value가 아닌 경우 :attribute 필드가 있어야 합니다.',
    'present_with' => ':values가 있을 때 :attribute 필드가 있어야 합니다.',
    'present_with_all' => ':values가 모두 있을 때 :attribute 필드가 있어야 합니다.',
    'prohibited' => ':attribute 필드는 사용할 수 없습니다.',
    'prohibited_if' => ':other가 :value일 때 :attribute 필드는 사용할 수 없습니다.',
    'prohibited_unless' => ':other가 :values에 없는 경우 :attribute 필드는 사용할 수 없습니다.',
    'prohibits' => ':attribute 필드가 있으면 :other를 사용할 수 없습니다.',
    'regex' => ':attribute 필드 형식이 올바르지 않습니다.',
    'required' => ':attribute 항목을 입력해 주세요.',
    'required_array_keys' => ':attribute 필드에는 :values 항목이 포함되어야 합니다.',
    'required_if' => ':attribute 항목은 :other가 :value 일때 필요합니다.',
    'required_if_accepted' => ':other가 수락될 때 :attribute 필드는 필수입니다.',
    'required_if_declined' => ':other가 거부될 때 :attribute 필드는 필수입니다.',
    'required_unless' => ':values에 :other가 있는 경우 : attribute 항목은 필요하지 않습니다.',
    'required_with' => ':attribute 항목은 :values 가 존재할 때 필요합니다.',
    'required_with_all' => ':values가 모두 있을 때 :attribute 필드는 필수입니다.',
    'required_without' => ':attribute 항목은 :values 가 존재하지 않을 때 필요합니다.',
    'required_without_all' => ':attribute 항목은 :values 가 전혀 없다면 필수입니다.',
    'same' => ':attribute 필드는 :other와 일치해야 합니다.',
    'size' => [
        'array' => ':attribute 필드에는 :size개의 항목이 있어야 합니다.',
        'file' => ':attribute 필드는 :size 킬로바이트여야 합니다.',
        'numeric' => ':attribute 필드는 :size여야 합니다.',
        'string' => ':attribute 필드는 :size자여야 합니다.',
    ],
    'starts_with' => ':attribute 필드는 다음 중 하나로 시작해야 합니다: :values.',
    'string' => ':attribute는 글자여야 합니다.',
    'two_column_unique_undeleted' => ':attribute은(는) :table1과(와) :table2 전체에서 고유해야 함',
    'unique_undeleted' => ':attribute 는 고유의 값만 가져야 합니다.',
    'non_circular' => '​:attribute은(는) 순환 참조를 생성할 수 없습니다.',
    'parent_must_be_top_level' => '선택한 :attribute은(는) 최상위 항목이어야 합니다. 한 단계의 중첩만 허용됩니다.',
    'must_have_no_children' => '이 항목은 이미 하위 항목이 있으므로 상위 항목을 지정할 수 없습니다.',
    'parent_within_scope' => 'The selected :attribute is outside your assigned scope.',
    'not_array' => ':attribute은(는) 배열일 수 없습니다.',
    'disallow_same_pwd_as_user_fields' => '비밀번호는 사용자 이름과 같을 수 없음',
    'letters' => '비밀번호는 최소 하나의 문자를 포함해야 함',
    'numbers' => '비밀번호는 최소 하나의 숫자를 포함해야 함',
    'case_diff' => '비밀번호는 대소문자를 혼합해야 함',
    'symbols' => '비밀번호는 기호를 포함해야 함',
    'timezone' => ':attribute 필드는 유효한 시간대여야 합니다.',
    'unique' => ':attribute 는 이미 획득하였습니다.',
    'uploaded' => ':attribute는 업로드 하지 못했습니다.',
    'uppercase' => ':attribute 필드는 대문자여야 합니다.',
    'url' => ':attribute 필드는 올바른 URL이어야 합니다.',
    'external_url' => ':attribute 필드는 사설 또는 로컬 주소를 가리키지 않는 유효한 외부 URL(http:// 또는 https://)이어야 합니다.',
    'asset_tag_pattern_placeholder' => 'The asset tag pattern must include at least one placeholder from :placeholders, otherwise every synced asset would receive the same tag and only the first would save.',
    'custom_http_extras_json' => [
        'not_json' => 'Custom Extras must be valid JSON. Parser error: :error',
        'not_array' => 'Custom Extras must be a JSON array (starting with [ and ending with ]).',
        'entry_not_object' => 'Custom Extras entry #:index must be a JSON object with key, label, and path fields.',
        'missing_field' => 'Custom Extras entry #:index is missing the required ":field" field.',
    ],
    'ulid' => ':attribute 필드는 유효한 ULID여야 합니다.',
    'uuid' => ':attribute 필드는 유효한 UUID여야 합니다.',
    'valid_css_color' => ':attribute 필드는 유효한 CSS 색상(hex, rgb, rgba, hsl 또는 hsla)이어야 합니다.',
    'eloquent_model_class' => 'The :attribute field must be the fully-qualified class name of an Eloquent model.',
    'fmcs_company' => 'The :attribute field is required because full multiple companies support is enabled and floaters are not allowed.',
    'fmcs_location' => '위치 ":location"은(는) :location_company에 속하며, 선택한 회사와 일치하지 않습니다.',
    'is_unique_across_company_and_location' => ':attribute은(는) 선택한 회사 및 위치 내에서 고유해야 합니다.',

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

    'email_array' => '하나 이상의 이메일 주소가 유효하지 않습니다.',
    'checkboxes' => ':attribute에 잘못된 옵션이 포함되어 있습니다.',
    'radio_buttons' => ':attribute이(가) 잘못되었습니다.',
    'image_file_too_large' => 'The :attribute may not be larger than :max.',

    'custom' => [
        'alpha_space' => ':attribute 항목에는 문자를 입력할 수 없습니다.',

        'hashed_pass' => '현재 비밀번호가 잘못되었습니다.',
        'dumbpwd' => '그 비밀번호는 너무 일반적입니다.',
        'statuslabel_type' => '유효한 상태 라벨 형식을 선택해 주셔야 합니다',
        'custom_field_not_found' => '이 필드가 존재하지 않는 것 같습니다. 사용자 정의 필드 이름을 다시 확인하세요.',
        'custom_field_not_found_on_model' => '이 필드는 존재하지만 이 자산 모델의 필드셋에서 사용할 수 없습니다.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => ':attribute은(는) YYYY-MM-DD 형식의 유효한 날짜여야 함',
        'last_audit_date.date_format' => ':attribute은(는) YYYY-MM-DD hh:mm:ss 형식의 유효한 날짜여야 함',
        'expiration_date.date_format' => ':attribute은(는) YYYY-MM-DD 형식의 유효한 날짜여야 함',
        'termination_date.date_format' => ':attribute은(는) YYYY-MM-DD 형식의 유효한 날짜여야 함',
        'expected_checkin.date_format' => ':attribute은(는) YYYY-MM-DD 형식의 유효한 날짜여야 함',
        'start_date.date_format' => ':attribute은(는) YYYY-MM-DD 형식의 유효한 날짜여야 함',
        'end_date.date_format' => ':attribute은(는) YYYY-MM-DD 형식의 유효한 날짜여야 함',
        'invalid_value_in_field' => '이 필드에 유효하지 않은 값이 포함되어 있습니다',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code>(대소문자 혼합)은 작동하지 않을 수 있습니다. 대신 <code>samaccountname</code>(소문자)을 사용하세요.',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code>은 유효한 인증 필터가 아닐 수 있습니다. <code>uid=</code>를 사용하는 것이 좋습니다.'],
        'ldap_filter' => ['regex' => '이 값은 괄호로 감싸지 않는 것이 좋습니다.'],

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
        'serials.*' => '일련번호',
        'asset_tags.*' => '자산 태그',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => '이 필드에 유효하지 않은 값이 포함되어 있습니다',
        'required' => '이 필드는 필수입니다',
        'email' => '유효한 이메일 주소를 입력하세요',
    ],

];
