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

    'accepted' => 'Полето :attribute мора да биде прифатено.',
    'accepted_if' => 'Полето :attribute мора да биде прифатено кога полето :other е :value.',
    'active_url' => 'Полето :attribute мора да биде валидна URL.',
    'after' => 'Полето :attribute мора да биде датум после :date.',
    'after_or_equal' => 'Полето :attribute мора да биде датум после или еднаков на :date.',
    'alpha' => 'Полето :attribute мора да содржи само букви.',
    'alpha_dash' => 'Полето :attribute мора да содржи само букви, бројки, средни црти и долни црти.',
    'alpha_num' => 'Полето :attribute мора да содржи само букви и бројки.',
    'array' => 'Полето :attribute мора да биде низа.',
    'ascii' => 'Полето :attribute мора да содржи само алфанумерички карактери и симболи од еден бајт.',
    'before' => 'Полето :attribute мора да биде датум пред :date.',
    'before_or_equal' => 'Полето :attribute мора да биде пред или еднакво на :date.',
    'between' => [
        'array' => 'Полето :attribute мора да биде помеѓу :min и :max средства.',
        'file' => 'Полето :attribute мора да биде помеѓу :min и :max килобајти.',
        'numeric' => 'Полето :attribute мора да биде помеѓу :min и :max.',
        'string' => 'Полето :attribute мора да биде помеѓу :min и :max карактери.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Полето :attribute мора да биде точно или грешно.',
    'can' => 'Полето :attribute содржи неавторизирана вредност.',
    'confirmed' => 'Полето :attribute потврдата не соодветствува.',
    'contains' => 'Полето :attribute недостасува задолќителна вредност.',
    'current_password' => 'Лозинката не е точна.',
    'date' => 'Полето :attribute мора да биде валиден датум.',
    'date_equals' => 'Полето :attribute мора да биде датум еднаков на :date.',
    'date_format' => 'Полето :attribute мора да биде во формат :format.',
    'decimal' => 'Полето :attribute мора да има :decimal децимални места.',
    'declined' => 'Полето :attribute мора да биде одбиено.',
    'declined_if' => 'Полето :attribute мора да биде одбиено кога :other е :value.',
    'different' => 'Полето :attribute и полето :other мора да бидат различни.',
    'digits' => 'Полето :attribute мора да биде :digits цифри.',
    'digits_between' => 'Полето :attribute мора да биде помеѓѕ :min и :max цифри.',
    'dimensions' => 'Полето :attribute има невалидни димензии на сликата.',
    'distinct' => 'Полето :attribute има дупликат вредност.',
    'doesnt_end_with' => 'Полето :attribute не смее да завршува со една од следните: :values.',
    'doesnt_start_with' => 'Полето :attribute не смее да почнува со една од следните: :values.',
    'email' => 'Полето :attribute мора да биде валидна адреса на Е-пошта.',
    'ends_with' => 'Полето :attribute мора да завршува со една од следните: :values.',
    'enum' => 'Избраниот :attribute не е валиден.',
    'exists' => 'Избраниот :attribute не е валиден.',
    'extensions' => 'Полето :attribute мора да содржи една од следните екстензии: :values.',
    'file' => 'Полето :attribute мора да биде датотека.',
    'filled' => 'Полето :attribute мора да има дупликат.',
    'gt' => [
        'array' => 'Полето :attribute мора да има повеќе од :value предмети.',
        'file' => 'Полето :attribute мора да биде поголемо од :value kilobytes.',
        'numeric' => 'Полето :attribute мора да биде поголемо од :value.',
        'string' => 'Полето :attribute мора да биде поголемо од :value карактери.',
    ],
    'gte' => [
        'array' => 'Полето :attribute мора да има :value предмети или повеќе.',
        'file' => 'Полето :attribute мора да биде поголемо или еднакво на :value kilobytes.',
        'numeric' => 'Полето :attribute мора да биде поголемо или еднакво на :value.',
        'string' => 'Полето :attribute мора да биде поголемо или еднакво на :value карактери.',
    ],
    'hex_color' => 'Полето :attribute мора да биде валидна хексадецимална боја.',
    'image' => 'Полето :attribute мора да биде слика.',
    'import_field_empty'    => 'Вредноста :fieldname неможе да биде нула.',
    'in' => 'Избраниот :attribute не е валиден.',
    'in_array' => 'Полето :attribute мора да се содржи во :other.',
    'integer' => 'Полето :attribute мора да биде цел број.',
    'ip' => 'Полето :attribute мора да биде валидна IP адреса.',
    'ipv4' => 'Полето :attribute мора да биде валидна IPv4 адреса.',
    'ipv6' => 'Полето :attribute мора да биде валидна IPv6 адреса.',
    'json' => 'Полето :attribute мора да биде валиден JSON стринг.',
    'list' => 'Полето :attribute мора да биде листа.',
    'lowercase' => 'Полето :attribute мора да биди мали букви.',
    'lt' => [
        'array' => 'Полето :attribute мора да има помалку од :value предмети.',
        'file' => 'Полето :attribute мора да биде помалку од :value килобајти.',
        'numeric' => 'Полето :attribute мора да биде помалку од :value.',
        'string' => 'Полето :attribute мора да биде помалку од :value карактери.',
    ],
    'lte' => [
        'array' => 'Полето :attribute не смее да има повеќе од :value предмети.',
        'file' => 'Полето :attribute мора да биде помалку или еднакво на :value килобајти.',
        'numeric' => 'Полето :attribute мора да биде помало или еднакво на :value.',
        'string' => 'Полето :attribute мора да биде помалку или еднакво на :value characters.',
    ],
    'mac_address' => 'Полето :attribute мора да биде валидна MAC адреса.',
    'max' => [
        'array' => 'Полето :attribute не смее да има повеќе од :max предмети.',
        'file' => 'Полето :attribute не смее да биде поголемо од :max килобајти.',
        'numeric' => 'Полето :attribute не смее да биде поголемо од :max.',
        'string' => 'Полето :attribute не смее да биде поголемо од :max карактери.',
    ],
    'max_digits' => 'Полето :attribute не смее да има повеќе од :max цифри.',
    'mimes' => 'Полето :attribute мора да биде датотека од тип: :values.',
    'mimetypes' => 'Полето :attribute мора да биде датотека од тип: :values.',
    'min' => [
        'array' => 'Полето :attribute мора да има најмалку :min предмети.',
        'file' => 'Полето :attribute field мора да биде најмалку :min килобајти.',
        'numeric' => 'Полето :attribute мора да биде најмалку :min.',
        'string' => 'Полето :attribute мора да биде најмалку :min карактери.',
    ],
    'min_digits' => 'Полето :attribute мора да има најмалку :min цифри.',
    'missing' => 'Полето :attribute мора да недостасува.',
    'missing_if' => 'Полето :attribute мора да недостасува кога :other е :value.',
    'missing_unless' => 'Полето :attribute мора да недостасува освен :other е :value.',
    'missing_with' => 'Полето :attribute мора да недостасува кога :values е присутна.',
    'missing_with_all' => 'Полето :attribute мора да недостасува кога :values е присутна.',
    'multiple_of' => 'Полето :attribute мора да биди множество од :value.',
    'not_in' => 'Избраниот :attribute не е валиден.',
    'not_regex' => 'Форматот на полето :attribute не е валиден.',
    'numeric' => 'Полето :attribute мора да биде број.',
    'password' => [
        'letters' => 'Полето :attribute мора да содржи најмалку една буква.',
        'mixed' => 'Полето :attribute мора да содржи најмалку една голема и една мала буква.',
        'numbers' => 'Полето :attribute мора да содржи најмалку еден број.',
        'symbols' => 'Полето :attribute мора да содржи најмалку еден симбол.',
        'uncompromised' => 'Полето во дадениот :attribute се појавува во протекување на податоци. Ве молиме изберете различен :attribute.',
    ],
    'percent'       => 'Полето амортизација минимум мора да биде помеѓу 0 и 100 кога амортизацијата е во проценти.',

    'present' => 'Полето :attribute е задолжително.',
    'present_if' => 'Полето :attribute мора да биде присутно кога :other е :value.',
    'present_unless' => 'Полето :attribute мора да биде присутно освен ако :other е :value.',
    'present_with' => 'Полето :attribute мора да биде присутно кога :values е присутна.',
    'present_with_all' => 'Полето :attribute мора да биде присутно кога :values е присутна.',
    'prohibited' => 'Полето :attribute е забрането.',
    'prohibited_if' => 'Полето :attribute е забрането кога :other е :value.',
    'prohibited_unless' => 'Полето :attribute забрането освен ако :other е во :values.',
    'prohibits' => 'Полето :attribute забранува :other да бидат присутни.',
    'regex' => 'Форматот на полето :attribute не е валиден.',
    'required' => 'Полето за :attribute е задолжително.',
    'required_array_keys' => 'Полето :attribute мора да содржи податоци за: :values.',
    'required_if' => 'Полето :attribute е задолжително, кога :other е :values.',
    'required_if_accepted' => 'Полето :attribute е задолжително кога :other е прифатено.',
    'required_if_declined' => 'Полето :attribute е задолжително кога :other е одбиено.',
    'required_unless' => 'Полето :attribute е задолжително, освен ако :other е :values.',
    'required_with' => 'Полето :attribute е задолжително кога постојат :values.',
    'required_with_all' => 'Полето :attribute задолжително кога :values се присутни.',
    'required_without' => 'Полето :attribute е задолжително кога не постојат :values.',
    'required_without_all' => 'Полето :attribute е задолжително кога не постои ниту една :values.',
    'same' => 'Полето :attribute мора да е одговара на :other.',
    'size' => [
        'array' => 'Полето :attribute мора да содржи :size предмети.',
        'file' => 'Полето :attribute мора да биде :size килобајти.',
        'numeric' => 'Полето :attribute мора да биде :size.',
        'string' => 'Полето :attribute мора да биде :size карактери.',
    ],
    'starts_with' => 'Полето :attribute мора да почнува со една од следните: :values.',
    'string'               => ':attribute мора да биде стринг.',
    'two_column_unique_undeleted' => 'Полето :attribute мора да биде уникатно низ :table1 и :table2. ',
    'unique_undeleted'     => ':attribute мора да биде уникатен.',
    'non_circular'         => 'Полето :attribute не смее да создава циркуларна референца.',
    'not_array'            => ':attribute не смее да биде низа.',
    'disallow_same_pwd_as_user_fields' => 'Лозинката не смее да биде иста со корисничкото име.',
    'letters'              => 'Лозинката мора да содржи најмалку една буква.',
    'numbers'              => 'Лозинката мора да содржи најмалку еден број.',
    'case_diff'            => 'Лозинката мора да содржи мали и големи букви.',
    'symbols'              => 'Лозинката мора да содржи симболи.',
    'timezone' => 'Полето :attribute мора да биде валидна временска зона.',
    'unique' => ':attribute е веќе зафатен.',
    'uploaded' => ':attribute не е прикачен.',
    'uppercase' => 'Полето :attribute мора да биде големи букви.',
    'url' => 'Полето :attribute мора да биде валидна URL.',
    'ulid' => 'Полето :attribute мора да биде валидна ULID.',
    'uuid' => 'Полето :attribute мора да биде валидна UUID.',
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

    'email_array'      => 'Една или повеќе е-пошти не се валидни.',
    'checkboxes'           => ':attribute содржи невалидни опции.',
    'radio_buttons'        => ':attribute не е валиден.',
    
    'custom' => [
        'alpha_space' => 'Полето :attribute содржи знак што не е дозволен.',

        'hashed_pass'      => 'Вашата тековна лозинка е неточна',
        'dumbpwd'          => 'Таа лозинка е премногу честа.',
        'statuslabel_type' => 'Мора да изберете валидна етикета за статус',
        'custom_field_not_found'          => 'Полето изгледа дека непостои, ве молиме проверете го името на корисничкото поле.',
        'custom_field_not_found_on_model' => 'Полето изгледа дека постои, но не е достапно во овој модел на средство\\a.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => 'Полето :attribute мора да биде валиден датум во YYYY-MM-DD формат',
        'last_audit_date.date_format'   =>  'Полето :attribute мора да биде валиден датум во YYYY-MM-DD hh:mm:ss формат',
        'expiration_date.date_format'   =>  'Полето :attribute мора да биде валиден датум во YYYY-MM-DD формат',
        'termination_date.date_format'  =>  'Полето :attribute мора да биде валиден датум во YYYY-MM-DD формат',
        'expected_checkin.date_format'  =>  'Полето :attribute мора да биде валиден датум во YYYY-MM-DD формат',
        'start_date.date_format'        =>  'Полето :attribute мора да биде валиден датум во YYYY-MM-DD формат',
        'end_date.date_format'          =>  'Полето :attribute мора да биде валиден датум во YYYY-MM-DD fформат',
        'invalid_value_in_field' => 'Невалидна вредност вклучена во полето',

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
        'invalid_value_in_field' => 'Невалидна вредност вклучена во полето',
        'required' => 'Полето е задолжително',
        'email' => 'Ве молиме внесете валидна адреса на Е-пошта',
    ],


];
