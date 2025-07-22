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

    'accepted' => 'Nitelik hanesi kabul edilmelidir.',
    'accepted_if' => ':other alanı :value olduğunda, :attribute alanı onaylanmalıdır',
    'active_url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'after' => ':attribute alanı, :date tarihinden sonraki bir tarih olmalıdır.',
    'after_or_equal' => ':attribute alanı, :date tarihinden sonra veya bu tarihle aynı bir tarih olmalıdır.',
    'alpha' => ':attribute alanı yalnızca harfler içermelidir.',
    'alpha_dash' => ':attribute alanı yalnızca harfler, rakamlar, tire ve alt çizgi karakterleri içermelidir.',
    'alpha_num' => ':attribute alanı yalnızca harf ve rakamlar içermelidir.',
    'array' => ':attribute alanı bir dizi olmalıdır.',
    'ascii' => ':attribute alanı yalnızca tek baytlık alfasayısal karakterler ve semboller içermelidir.',
    'before' => ':attribute alanı, :date tarihinden önceki bir tarih olmalıdır.',
    'before_or_equal' => ':attribute alanı, :date tarihinden önceki veya bu tarihle aynı bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute alanı :min ve :max öğeleri arasında olmalıdır.',
        'file' => ':attribute alanı :min ve :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute alanı :min ve :max arasında olmalıdır.',
        'string' => ':attribute alanı :min ve :max karakterleri arasında olmalıdır.',
    ],
    'valid_regex' => 'Regular expression geçersiz.',
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'can' => ':attribute alanı yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute alanı onayı eşleşmiyor.',
    'contains' => ':attribute alanında gerekli bir değer eksik.',
    'current_password' => 'Şifre yanlış.',
    'date' => ':attribute alanı geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute alanı :date ile aynı tarih olmalıdır.',
    'date_format' => ':attribute alanı :format biçimiyle eşleşmelidir.',
    'decimal' => ':attribute alanı :decimal ondalık basamak sayısına sahip olmalıdır.',
    'declined' => ':attribute alanı reddedilmelidir.',
    'declined_if' => ':attribute alanı, :other :value olduğunda reddedilmelidir.',
    'different' => ':attribute alanı ve :other farklı olmalıdır.',
    'digits' => ':attribute alanı :digits rakamlarından oluşmalıdır.',
    'digits_between' => ':attribute alanı :min ve :max rakamları arasında olmalıdır.',
    'dimensions' => ':attribute alanı geçersiz resim boyutlarına sahip.',
    'distinct' => ': Öznitelik alanı yinelenen bir değere sahip.',
    'doesnt_end_with' => ':attribute alanı :values değerlerinden biriyle bitmemelidir.',
    'doesnt_start_with' => ':attribute alanı :values değerlerinden biriyle başlamamalıdır.',
    'email' => ':attribute alanı geçerli bir e-posta adresi olmalıdır.',
    'ends_with' => ':attribute alanı  :values değerlerinden biriyle bitmelidir.',
    'enum' => ':attribute geçersiz.',
    'exists' => ':attribute seçim geçersiz.',
    'extensions' => ':attribute alanı :values uzantılardan birine sahip olmalıdır',
    'file' => ':attribute alanı bir dosya olmalıdır.',
    'filled' => ': Attribute alanının bir değeri olmalıdır.',
    'gt' => [
        'array' => ':attribute alanı :value öğelerinden daha fazla öğeye sahip olmalıdır.',
        'file' => ':attribute alanı :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute alanı :value alanından büyük olmalıdır.',
        'string' => ':attribute alanı :value karakterden daha büyük olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute alanı :value öğesi veya daha fazlasını içermelidir.',
        'file' => ':attribute alanı :value kilobayttan büyük veya eşit olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden büyük veya eşit olmalıdır.',
        'string' => ':attribute alanı :value karakterinden büyük veya eşit olmalıdır.',
    ],
    'hex_color' => ':attribute alanı geçerli bir onaltılık renk olmalıdır.',
    'image' => ':attribute alanı bir resim olmalıdır.',
    'import_field_empty'    => 'Bu değer için :alan adı boş olamaz.',
    'in' => ':attribute geçersiz.',
    'in_array' => ':attribute alanı :other içinde bulunmalıdır.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'list' => 'The :attribute field must be a list.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => ':attribute geçersiz.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'percent'       => 'The depreciation minimum must be between 0 and 100 when depreciation type is percentage.',

    'present' => ': Attribute alanı bulunmalıdır.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => ':attribute alanı zorunludur.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => ':attribute :other :value geçersiz.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_if_declined' => 'The :attribute field is required when :other is declined.',
    'required_unless' => ': Attribute alanı, aşağıdaki koşullar haricinde: other is in: values.',
    'required_with' => ':attribute :values geçersiz.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => ':attribute :values geçersiz.',
    'required_without_all' => ': Özellik alanının hiçbiri: değerleri mevcut değilse gereklidir.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string'               => ': Özniteliği bir dize olmalıdır.',
    'two_column_unique_undeleted' => ':attribute :table1 ve :table2 genelinde benzersiz olmalıdır. ',
    'unique_undeleted'     => ':attribute benzersiz olmalıdır.',
    'non_circular'         => ':attribute döngüsel bir başvuru oluşturmamalıdır.',
    'not_array'            => ':attribute bir dizi olamaz.',
    'disallow_same_pwd_as_user_fields' => 'Şifre kullanıcı adı ile aynı olamaz.',
    'letters'              => 'Şifre en az bir harf içermelidir.',
    'numbers'              => 'Şifre en az bir rakam içermelidir.',
    'case_diff'            => 'Şifre hem büyük hem küçük harf içermelidir.',
    'symbols'              => 'Şifre sembol içermelidir.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ': Özniteliği yüklenemedi.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',
    'fmcs_location' => 'Full multiple company support and location scoping is enabled in the Admin Settings, and the selected location and selected company are not compatible.',


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

    'email_array'      => 'Bir veya daha fazla e-posta adresi geçersiz.',
    'checkboxes'           => ':attribute contains invalid options.',
    'radio_buttons'        => ':attribute is invalid.',
    
    'custom' => [
        'alpha_space' => ': Attribute alanı, izin verilmeyen bir karakter içeriyor.',

        'hashed_pass'      => 'Geçerli şifre yanlış',
        'dumbpwd'          => 'Bu şifre çok yaygındır.',
        'statuslabel_type' => 'Geçerli bir durum etiketi türü seçmelisiniz',
        'custom_field_not_found'          => 'This field does not seem to exist, please double check your custom field names.',
        'custom_field_not_found_on_model' => 'This field seems to exist, but is not available on this Asset Model\'s fieldset.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute YYYY-MM-DD tarih formatında olmalıdır',
        'last_audit_date.date_format'   =>  ':attribute YYYY-MM-DD hh:mm:ss tarih formatında olmalıdır',
        'expiration_date.date_format'   =>  ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'termination_date.date_format'  =>  ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'expected_checkin.date_format'  =>  ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'start_date.date_format'        =>  ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'end_date.date_format'          =>  ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'invalid_value_in_field' => 'Invalid value included in this field',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (mixed case) will likely not work. You should use <code>samaccountname</code> (lowercase) instead.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> is probably not a valid auth filter. You probably want <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'This value should probably not be wrapped in parentheses.'],

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
        'invalid_value_in_field' => 'Invalid value included in this field',
        'required' => 'This field is required',
        'email' => 'Please enter a valid email address',
    ],


];
