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

    'accepted' => 'Полето :attribute трябва да бъде прието.',
    'accepted_if' => 'Полето :attribute трябва да бъде прието, когато :other е :value.',
    'active_url' => 'Полето :attribute трябва да съдържа валиден URL.',
    'after' => 'Полето :attribute трябва да бъде дата след :date.',
    'after_or_equal' => 'Полето :attribute трябва да бъде дата след или равна на :date.',
    'alpha' => 'Полето :attribute трябва да съдържа само букви.',
    'alpha_dash' => 'Полето :attribute трябва да съдържа само букви, цифри, тирета и подчертавания.',
    'alpha_num' => 'Полето :attribute трябва да съдържа само букви и цифри.',
    'array' => 'Полето :attribute трябва да е масив.',
    'ascii' => 'Полето :attribute трябва да съдържа еднобайтови буквено-цифрови знаци и символи.',
    'before' => 'Полето :attribute трябва да бъде дата преди :date.',
    'before_or_equal' => 'Полето :attribute трябва да бъде дата преди или равна на :date.',
    'between' => [
        'array' => 'Полето :attribute трябва да има между :min и :max .',
        'file' => 'Полето :attribute трябва да бъде между :min и :max килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде между :min и :max .',
        'string' => 'Полето :attribute трябва да бъде между :min и :max символа.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Полето :attribute трябва да бъде Да или Не.',
    'can' => 'Полето :attribute съдържа неприемлива стойност.',
    'confirmed' => 'Полето :attribute не съвпада.',
    'contains' => 'Полето :attribute липсва необходимата стойност.',
    'current_password' => 'Паролата е грешна.',
    'date' => 'Полето :attribute трябва да съдържа валидна дата.',
    'date_equals' => 'Полето :attribute трябва да бъде дата равна на :date.',
    'date_format' => 'Полето :attribute трябва да има формат :format.',
    'decimal' => 'Полето :attribute трябва да има :decimal десетични знака.',
    'declined' => 'Полето :attribute трябва да бъде отказано.',
    'declined_if' => 'Полето :attribute трябва да бъде отказано, когато :other е :value.',
    'different' => 'Полето :attribute и :other трябва да са различни.',
    'digits' => 'Полето :attribute трябва да съдържа :digits цифри.',
    'digits_between' => 'Полето :attribute трябва да бъде между :min и :max цифри.',
    'dimensions' => 'Полето :attribute има невалидни размери на снимката.',
    'distinct' => 'Полето: atribut има дублираща се стойност.',
    'doesnt_end_with' => 'Полето :attribute трябва да не завършва на: :values.',
    'doesnt_start_with' => 'Полето :attribute трябва да не започва с: :values.',
    'email' => 'Полето :attribute трябва да съдържа валиден е-майл адрес.',
    'ends_with' => 'Полето :attribute трябва да завършва на: :values.',
    'enum' => 'Избраният :attribute е невалиден.',
    'exists' => 'Избраният :attribute е невалиден.',
    'extensions' => 'Полето :attribute трябва да има едно от следните разширения: :values.',
    'file' => 'Полето :attribute трябва да бъде файл.',
    'filled' => 'Полето на атрибута: трябва да има стойност.',
    'gt' => [
        'array' => 'Полето :attribute трябва да съдържа повече от :value стойности.',
        'file' => 'Полето :attribute трябва да бъде по-голямо от :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-голямо от :value.',
        'string' => 'Полето :attribute трябва да бъде по-голямо от :value символа.',
    ],
    'gte' => [
        'array' => 'Полето :attribute трябва да има :value или повече неща.',
        'file' => 'Полето :attribute трябва да бъде повече или равно на :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде повече или равно на :value.',
        'string' => 'Полето :attribute трябва да бъде повече или равно на :value символа.',
    ],
    'hex_color' => 'Полето :attribute трябва да съдържа валиден шестнадесетичен цвят.',
    'image' => 'Полето :attribute трябва да бъде снимка.',
    'import_field_empty'    => 'Стойността за :fieldname не може да бъде празна.',
    'in' => 'Избраният :attribute е невалиден.',
    'in_array' => 'Полето :attribute трябва да се съдържа в :other.',
    'integer' => 'Полето :attribute трябва да бъде цяло число.',
    'ip' => 'Полето :attribute трябва да бъде валиден IP адрес.',
    'ipv4' => 'Полето :attribute трябва да бъде валиден IPv4 адрес.',
    'ipv6' => 'Полето :attribute трябва да бъде валиден IPv6 адрес.',
    'json' => 'Полето :attribute трябва да бъде валиден JSON низ.',
    'list' => 'Полето :attribute трябва да бъде списък.',
    'lowercase' => 'Полето :attribute трябва да бъде с малки букви.',
    'lt' => [
        'array' => 'Полето :attribute трябва да бъде по-малко от :value .',
        'file' => 'Полето :attribute трябва да бъде по-малко от :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-малко от :value.',
        'string' => 'Полето :attribute трябва да бъде помалко от :value символа.',
    ],
    'lte' => [
        'array' => 'Полето :attribute не трябва да съдържа повече от :value стойности.',
        'file' => 'Полето :attribute трябва да бъде по-малко или равно на :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-малко или равно на :value.',
        'string' => 'Полето :attribute трябва да бъде по-малко или равно на :value символа.',
    ],
    'mac_address' => 'Полето :attribute трябва да бъде валиден MAC адрес.',
    'max' => [
        'array' => 'Полето :attribute не трябва да е повече от :max .',
        'file' => 'Полето :attribute не трябва да е повече от :max килобайта.',
        'numeric' => 'Полето :attribute не трябва да е повече от :max.',
        'string' => 'Полето :attribute не трябва да е повече от :max символа.',
    ],
    'max_digits' => 'Полето :attribute не трябва да съдържа повече от :max цифри.',
    'mimes' => 'Полето :attribute трябва да бъде файл от тип: :values.',
    'mimetypes' => 'Полето :attribute трябва да бъде файл от тип: :values.',
    'min' => [
        'array' => 'Полето :attribute трябва да съдържа най-малко :min стойности.',
        'file' => 'Полето :attribute трябва да бъде най-малко :min килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде най-малко :min.',
        'string' => 'Полето :attribute трябва да бъде минимум :min символа.',
    ],
    'min_digits' => 'Полето :attribute трябва да бъде минимум :min цифри.',
    'missing' => 'Полето :attribute трябва да липсва.',
    'missing_if' => 'Полето :attribute трябва да липсва, когато :other е :value.',
    'missing_unless' => 'Полето :attribute трябва да липсва, освен когато :other e :value.',
    'missing_with' => 'Полето :attribute трябва да липсва, когато :values е налице.',
    'missing_with_all' => 'Полето :attribute трябва да липсва, когато :values е налице.',
    'multiple_of' => 'Полето :attribute трябва да е кратно на :value.',
    'not_in' => 'Избраният :attribute е невалиден.',
    'not_regex' => 'Полето :attribute е с невалиден формат.',
    'numeric' => 'Полето :attribute трябва да бъде число.',
    'password' => [
        'letters' => 'Полето :attribute трябва да съдръжа поне една буква.',
        'mixed' => 'Полето :attribute трябва да съдържа поне една главна и една малка буква.',
        'numbers' => 'Полето :attribute трябва да съдържа поне една цифра.',
        'symbols' => 'Полето :attribute трябва да съдържа поне един символ.',
        'uncompromised' => 'Избраната :attribute e намерена в хакнати пароли. Моля изберете друга :attribute.',
    ],
    'percent'       => 'Минималната стойност на амортизацията трябва да бъде между 0 и 100, когато типът амортизация е процент.',

    'present' => 'Полето на атрибута трябва да е налице.',
    'present_if' => 'Полето :attribute трябва да е попълнено, когато :other е :value.',
    'present_unless' => 'Полето :attribute трябва да е попълнено, освен когато :other е :value.',
    'present_with' => 'Полето :attribute трябва да е попълнено, когато :values е налице.',
    'present_with_all' => 'Полето :attribute трябва да е попълнено, когато :values са на лице.',
    'prohibited' => 'Полето :attribute е забранено.',
    'prohibited_if' => 'Полето :attribute е забранено, когато :other е :value.',
    'prohibited_unless' => 'Полето :attribute е забранено, освен когато :other е в :values.',
    'prohibits' => 'Полето :attribute забранява :other да бъде налично.',
    'regex' => 'Полето :attribute е с невалиден формат.',
    'required' => 'Полето :attribute е задължително.',
    'required_array_keys' => 'Полето :attribute трябва да съдържа записи за: :values.',
    'required_if' => 'Полето :attribute е задължително, когато :other е :value.',
    'required_if_accepted' => 'Полето :attribute е задължително, когато :other e приет.',
    'required_if_declined' => 'Полето :attribute е задължително, когато :other e отхвърлен.',
    'required_unless' => 'Полето: атрибут се изисква, освен ако: другият не е в: стойности.',
    'required_with' => ':attribute е задължителен, когато са избрани :values.',
    'required_with_all' => 'Полето :attribute е задъклжително, когато :values имат стойност.',
    'required_without' => ':attribute е задължителен, когато не са избрани :values.',
    'required_without_all' => 'Полето: атрибут се изисква, когато няма стойности: стойности.',
    'same' => 'Полето :attribute трябва да съвпада с :other.',
    'size' => [
        'array' => 'Полето :attribute трябва да съдържа :size елементи.',
        'file' => 'Полето :attribute трябва да бъде с големина :size килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде :size.',
        'string' => 'Полето :attribute трябва да бъде с дължина :size символа.',
    ],
    'starts_with' => 'Полето :attribute трябва да започва с: :values.',
    'string'               => 'Атрибутът: трябва да е низ.',
    'two_column_unique_undeleted' => ':attribute трябва да бъде уникален за :table1 и :table2. ',
    'unique_undeleted'     => ':attribute трябва да бъде уникален.',
    'non_circular'         => ':attribute не трябва да създава препрадка към себе си.',
    'not_array'            => ':attribute не може да бъде масив.',
    'disallow_same_pwd_as_user_fields' => 'Паролата не може да бъде същата, като потребителското име.',
    'letters'              => 'Паролата трябва да съдържа поне една буква.',
    'numbers'              => 'Паролата трябва да съдържа поне една цифра.',
    'case_diff'            => 'Паролата трябва да съдържа главни и малки букви.',
    'symbols'              => 'Паролата трябва да съдържа символи.',
    'timezone' => 'Полето :attribute трябва да бъде валидна часова зона.',
    'unique' => ':attribute вече е вписан.',
    'uploaded' => 'Атрибутът: не успя да качи.',
    'uppercase' => 'Полето :attribute трябва да бъде с главни букви.',
    'url' => 'Полето :attribute трябва да съдържа валиден URL.',
    'ulid' => 'Полето :attribute трябва да бъде валиден ULID.',
    'uuid' => 'Полето :attribute трябва да съдържа валиден UUID.',
    'fmcs_location' => 'Пълна поддръжка за множество компаний и местоположения е включена в админ настройките, но избраната локация и компания не са съвместими.',


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

    'email_array'      => 'Един или повече email адреси е невалиден.',
    'checkboxes'           => ':attribute съдържа невалидни опции.',
    'radio_buttons'        => ':attribute е невалиден.',
    
    'custom' => [
        'alpha_space' => 'Полето атрибут: съдържа знак, който не е разрешен.',

        'hashed_pass'      => 'Текущата ви парола е неправилна',
        'dumbpwd'          => 'Тази парола е твърде често срещана.',
        'statuslabel_type' => 'Трябва да изберете валиден тип етикет на състоянието',
        'custom_field_not_found'          => 'Това поле не съществува, моля проверете името на полето по избор.',
        'custom_field_not_found_on_model' => 'Това поле вече съществува, но не е налично за избрания дълготраен актив.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':values трябва да бъде валидна дата в YYYY-MM-DD формат',
        'last_audit_date.date_format'   =>  ':attribute трябва да бъде валидна дата в YYYY-MM-DD hh:mm:ss формат',
        'expiration_date.date_format'   =>  ':attribute трябва да бъде валидна дата в YYYY-MM-DD формат',
        'termination_date.date_format'  =>  ':attribute трябва да бъде валидна дата в YYYY-MM-DD формат',
        'expected_checkin.date_format'  =>  ':attribute трябва да бъде валидна дата в YYYY-MM-DD формат',
        'start_date.date_format'        =>  ':attribute трябва да бъде валидна дата в YYYY-MM-DD формат',
        'end_date.date_format'          =>  ':attribute трябва да бъде валидна дата в YYYY-MM-DD формат',
        'invalid_value_in_field' => 'В това поле е включена невалидна стойност',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (mixed case) няма да работи. Трябва да използвате <code>samaccountname</code> (lowercase) вместо това.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> не е валиден филтър за автентикация. Ползвайте <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Стойността не трябва да е в кавички.'],

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
        'invalid_value_in_field' => 'В това поле е включена невалидна стойност',
        'required' => 'Това поле е задължително',
        'email' => 'Моля, въведете валиден имейл адрес',
    ],


];
