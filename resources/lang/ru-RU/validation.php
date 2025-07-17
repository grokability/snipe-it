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

    'accepted' => 'Поле :attribute должно быть принято.',
    'accepted_if' => 'Поле :attribute должно быть принято, когда :other равно :value.',
    'active_url' => 'Поле :attribute должно быть действительным URL-адресом.',
    'after' => 'Поле :attribute должно содержать дату после :date.',
    'after_or_equal' => 'Поле :attribute должно содержать дату, которая позже или равна :date.',
    'alpha' => 'Поле :attribute должно содержать только буквы.',
    'alpha_dash' => 'Поле :attribute должно содержать только буквы, цифры, тире и знаки подчеркивания.',
    'alpha_num' => 'Поле :attribute должно содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'ascii' => 'Поле :attribute должно содержать только однобайтные буквенно-цифровые символы.',
    'before' => 'Поле :attribute должно содержать дату до :date.',
    'before_or_equal' => 'Дата в поле :attribute должна быть не позже :date.',
    'between' => [
        'array' => 'Поле :attribute должно быть между :min и :max элементов.',
        'file' => 'Поле :attribute должно быть между :min и :max килобайт.',
        'numeric' => 'Поле :attribute должно быть между :min и :max.',
        'string' => 'Поле :attribute должно содержать от :min до :max символов.',
    ],
    'valid_regex' => 'Регулярное выражение является недопустимым.',
    'boolean' => 'Поле :attribute должно быть true или false.',
    'can' => 'Поле :attribute содержит недопустимое значение.',
    'confirmed' => 'Подтверждение поля :attribute не совпадает.',
    'contains' => 'В поле :attribute отсутствует обязательное значение.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute должно содержать допустимую дату.',
    'date_equals' => 'Поле :attribute должно содержать значение даты :date.',
    'date_format' => 'Поле :attribute должно соответствовать формату :format.',
    'decimal' => 'Поле :attribute должно иметь :decimal десятичных знаков.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'declined_if' => 'Поле :attribute должно быть отклонено, когда :other равно :value.',
    'different' => 'Поле :attribute и :other должны различаться.',
    'digits' => 'Поле :attribute должно содержать :digits цифр.',
    'digits_between' => 'Поле :attribute должно содержать от :min до :max цифр.',
    'dimensions' => 'Поле :attribute имеет неверные размеры изображения.',
    'distinct' => 'Поле атрибута: имеет двойное значение.',
    'doesnt_end_with' => 'Поле :attribute не должно заканчиваться одним из следующих значений: :values.',
    'doesnt_start_with' => 'Поле :attribute не должно начинаться с одного из следующих значений: :values.',
    'email' => 'Поле :attribute должно быть действительным адресом электронной почты.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из следующих значений: :values.',
    'enum' => 'Выбранный :attribute неправильный.',
    'exists' => 'Выбранный :attribute неправильный.',
    'extensions' => 'Поле :attribute должно иметь одно из следующих расширений: :values.',
    'file' => 'Поле :attribute должно быть файлом.',
    'filled' => 'Поле атрибута: должно иметь значение.',
    'gt' => [
        'array' => 'Поле :attribute должно иметь более :value элементов.',
        'file' => 'Поле :attribute должно быть больше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть больше :value.',
        'string' => 'Поле :attribute должно содержать более :value символов.',
    ],
    'gte' => [
        'array' => 'Поле :attribute должно иметь :value или более элементов.',
        'file' => 'Поле :attribute должно быть больше или равно :value килобайт.',
        'numeric' => 'Поле :attribute должно быть больше или равно :value.',
        'string' => 'Поле :attribute должно содержать :value и более символов.',
    ],
    'hex_color' => 'Поле :attribute должно быть допустимым шестнадцатеричным цветом.',
    'image' => 'Поле :attribute должно быть изображением.',
    'import_field_empty'    => 'Значение :fieldname не может быть пустым.',
    'in' => 'Выбранный :attribute неправильный.',
    'in_array' => 'Поле :attribute должно существовать в :other.',
    'integer' => 'Поле :attribute должно быть целым числом.',
    'ip' => 'Поле :attribute должно быть действительным IP-адресом.',
    'ipv4' => 'Поле :attribute должно быть допустимым IPv4 адресом.',
    'ipv6' => 'Поле :attribute должно быть допустимым IPv6 адресом.',
    'json' => 'Поле :attribute должно быть действительной строкой JSON.',
    'list' => 'Поле :attribute должно быть списком.',
    'lowercase' => 'Поле :attribute должно быть указано строчными буквами.',
    'lt' => [
        'array' => 'Поле :attribute должно иметь менее :value элементов.',
        'file' => 'Поле :attribute должно быть меньше :value килобайт.',
        'numeric' => 'Поле :attribute должно быть меньше :value.',
        'string' => 'Поле :attribute должно содержать менее :value символов.',
    ],
    'lte' => [
        'array' => 'Поле :attribute не должно содержать более :value элементов.',
        'file' => 'Поле :attribute должно быть меньше или равно :value килобайт.',
        'numeric' => 'Поле :attribute должно быть меньше или равно :value.',
        'string' => 'Поле :attribute должно содержать :value или менее символов.',
    ],
    'mac_address' => 'Поле :attribute должно быть действительным MAC-адресом.',
    'max' => [
        'array' => 'Поле :attribute не должно содержать более :value элементов.',
        'file' => 'Поле :attribute не должно быть больше :max килобайт.',
        'numeric' => 'Поле :attribute не должно быть больше :max.',
        'string' => 'Поле :attribute должно быть не длиннее :max символов.',
    ],
    'max_digits' => 'Поле :attribute должно содержать не более :max цифр.',
    'mimes' => 'Поле :attribute должно быть файлом типа: :values.',
    'mimetypes' => 'Поле :attribute должно быть файлом типа: :values.',
    'min' => [
        'array' => 'Поле :attribute должно содержать не менее :min элементов.',
        'file' => 'Поле :attribute должно быть не менее :min килобайт.',
        'numeric' => 'Поле :attribute должно быть не менее :min.',
        'string' => 'Поле :attribute должно содержать не менее :min символов.',
    ],
    'min_digits' => 'Поле :attribute должно содержать не менее :min цифр.',
    'missing' => 'Поле :attribute должно отсутствовать.',
    'missing_if' => 'Поле :attribute должно быть пропущено, когда :other равно :value.',
    'missing_unless' => 'Поле :attribute должно отсутствовать, если только :other не равно :value.',
    'missing_with' => 'Поле :attribute должно быть пропущено, если присутствует :values.',
    'missing_with_all' => 'Поле :attribute должно быть пропущено, если присутствуют :values.',
    'multiple_of' => 'Поле :attribute должно быть кратно :value.',
    'not_in' => 'Выбранный :attribute неправильный.',
    'not_regex' => 'Поле :attribute имеет неверный формат.',
    'numeric' => 'Поле :attribute должно быть числом.',
    'password' => [
        'letters' => 'Поле :attribute должно содержать хотя бы одну букву.',
        'mixed' => 'Поле :attribute должно содержать хотя бы одну заглавную букву и одну строчную букву.',
        'numbers' => 'Поле :attribute должно содержать хотя бы одно число.',
        'symbols' => 'Поле :attribute должно содержать хотя бы один символ.',
        'uncompromised' => 'Данный :attribute появился при утечке данных. Пожалуйста, выберите другой :attribute.',
    ],
    'percent'       => 'Минимальная амортизация должна быть в пределах от 0 до 100, если тип амортизации — процентный.',

    'present' => 'Поле атрибута: должно присутствовать.',
    'present_if' => 'Поле :attribute должно присутствовать, когда :other равно :value.',
    'present_unless' => 'Поле :attribute должно присутствовать, если только :other не равно :value.',
    'present_with' => 'Поле :attribute должно присутствовать, если присутствует :values.',
    'present_with_all' => 'Поле :attribute должно присутствовать при наличии :values.',
    'prohibited' => 'Поле :attribute запрещено.',
    'prohibited_if' => 'Поле :attribute запрещено если :other равно :value.',
    'prohibited_unless' => 'Поле :attribute запрещено, если :other не находится в :values.',
    'prohibits' => 'Поле :attribute запрещает присутствие :other.',
    'regex' => 'Поле :attribute имеет неверный формат.',
    'required' => ':attribute обязательное поле.',
    'required_array_keys' => 'Поле :attribute должно содержать записи для: :values.',
    'required_if' => ':attribute обязательное поле, когда :other :value.',
    'required_if_accepted' => 'Поле :attribute является обязательным, если :other принят.',
    'required_if_declined' => 'Поле :attribute является обязательным, если :other отклонено.',
    'required_unless' => 'Поле атрибута: требуется, если: other находится в: значения.',
    'required_with' => ':attribute обязательное поле, когда присутствует :values.',
    'required_with_all' => 'Поле :attribute является обязательным, если присутствуют :values.',
    'required_without' => ':attribute обязательное поле, когда отсутствует :values.',
    'required_without_all' => 'Поле атрибута: требуется, если ни один из: значений не присутствует.',
    'same' => 'Поле :attribute должно соответствовать :other.',
    'size' => [
        'array' => 'Поле :attribute должно содержать :size элементов.',
        'file' => 'Поле :attribute должно быть :size килобайт.',
        'numeric' => 'Поле :attribute должно быть :size.',
        'string' => 'Поле :attribute должно содержать :size символов.',
    ],
    'starts_with' => 'Поле :attribute должно начинаться с одного из следующих значений: :values.',
    'string'               => 'Атрибут: должен быть строкой.',
    'two_column_unique_undeleted' => 'Поле :attribute должно быть уникальным для :table1 и :table2. ',
    'unique_undeleted'     => 'Свойство :attribute должно быть уникальным.',
    'non_circular'         => ':attribute не должен создавать циклическую ссылку.',
    'not_array'            => ':attribute не может быть массивом.',
    'disallow_same_pwd_as_user_fields' => 'Пароль не может совпадать с именем пользователя.',
    'letters'              => 'Пароль должен содержать хотя бы одну букву.',
    'numbers'              => 'Пароль должен содержать хотя бы одну цифру.',
    'case_diff'            => 'Пароль должен использовать смешанный регистр.',
    'symbols'              => 'Пароль должен содержать символы.',
    'timezone' => 'Поле :attribute должно содержать действительный часовой пояс.',
    'unique' => ':attribute уже занят.',
    'uploaded' => 'Атрибут: не удалось загрузить.',
    'uppercase' => 'Поле :attribute должно быть указано заглавными буквами.',
    'url' => 'Поле :attribute должно быть действительным URL-адресом.',
    'ulid' => 'Поле :attribute должно быть корректным значением UUID.',
    'uuid' => 'Поле :attribute должно быть корректным значением UUID.',
    'fmcs_location' => 'Полная поддержка и определение местоположения нескольких компаний включены в настройках администратора, а выбранное местоположение и выбранная компания не совместимы.',


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

    'email_array'      => 'Один или несколько адресов эл. почты являются недействительным.',
    'checkboxes'           => ':attribute содержит недопустимые параметры.',
    'radio_buttons'        => ':attribute не верно.',
    
    'custom' => [
        'alpha_space' => 'Поле атрибута: содержит символ, который не разрешен.',

        'hashed_pass'      => 'Ваш текущий пароль неверен',
        'dumbpwd'          => 'Этот пароль слишком распространен.',
        'statuslabel_type' => 'Вы должны выбрать допустимый тип метки статуса',
        'custom_field_not_found'          => 'Похоже, это поле не существует. Пожалуйста, проверьте еще раз имена ваших пользовательских полей.',
        'custom_field_not_found_on_model' => 'Это поле существует, но недоступно в наборе полей этой модели актива.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute должен быть допустимой датой в формате YYYY-MM-DD',
        'last_audit_date.date_format'   =>  ':attribute должен быть допустимой датой в формате YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  ':attribute должен быть допустимой датой в формате YYYY-MM-DD',
        'termination_date.date_format'  =>  ':attribute должен быть допустимой датой в формате YYYY-MM-DD',
        'expected_checkin.date_format'  =>  ':attribute должен быть допустимой датой в формате YYYY-MM-DD',
        'start_date.date_format'        =>  ':attribute должен быть допустимой датой в формате YYYY-MM-DD',
        'end_date.date_format'          =>  ':attribute должен быть допустимой датой в формате YYYY-MM-DD',
        'invalid_value_in_field' => 'Недопустимое значение в этом поле',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (mixed case) will likely not work. You should use <code>samaccountname</code> (lowercase) instead.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> is probably not a valid auth filter. You probably want <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Это значение, вероятно, не должно быть заключено в скобки.'],

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
        'invalid_value_in_field' => 'Недопустимое значение в этом поле',
        'required' => 'Это поле является обязательным',
        'email' => 'Пожалуйста, введите действительный адрес электронной почты',
    ],


];
