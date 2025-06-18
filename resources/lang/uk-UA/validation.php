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

    'accepted' => 'Поле :attribute повинно бути заповнене.',
    'accepted_if' => 'Поле :attribute є обов\'язковим для заповнення, коли :other є :value.',
    'active_url' => 'Поле :attribute має містити правильний URL.',
    'after' => 'Поле :attribute має містити дату не раніше :date.',
    'after_or_equal' => 'Поле :attribute має містити дату не раніше або дорівнювати :date.',
    'alpha' => 'Поле :attribute має містити лише літери.',
    'alpha_dash' => 'Поле :attribute має містити лише літери, цифри та тире.',
    'alpha_num' => 'Поле :attribute має містити лише літери та цифри.',
    'array' => 'Поле :attribute має бути масивом.',
    'ascii' => 'Поле :attribute повинне містити лише літерно-цифрові символи та символи.',
    'before' => 'Поле :attribute має містити дату не пізніше :date.',
    'before_or_equal' => 'Поле :attribute має містити дату не пізніше або дорівнювати :date.',
    'between' => [
        'array' => 'Поле :attribute має містити від :min до :max елементів.',
        'file' => 'Поле :attribute має бути в межах від :min до :max кілобайт.',
        'numeric' => 'Поле :attribute має бути в межах від :min до :max.',
        'string' => 'Поле :attribute має містити від :min до :max символів.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Поле :attribute повинне містити логічний тип.',
    'can' => 'Поле :attribute містить недозволене значення.',
    'confirmed' => 'Поле підтвердження :attribute не збігається.',
    'contains' => 'Поле :attribute не заповнене обов’язковим значенням.',
    'current_password' => 'Неправильний пароль.',
    'date' => 'Поле :attribute має містити коректну дату.',
    'date_equals' => 'Поле :attribute має бути датою довжиною від :date.',
    'date_format' => 'Поле :attribute має відповідати формату :format.',
    'decimal' => 'Поле :attribute повинне мати :decimal знаків.',
    'declined' => 'Поле :attribute має бути відхилене.',
    'declined_if' => 'Поле :attribute повинно бути відхилено, коли :other є :value.',
    'different' => 'Поле :attribute та :other має бути різним.',
    'digits' => 'Поле :attribute має складатися з цифр :digits.',
    'digits_between' => 'Довжина поля :attribute має бути в межах від :min до :max цифр.',
    'dimensions' => 'Поле :attribute містить неприпустимі розміри зображення.',
    'distinct' => 'Значення поля :attribute вже існує.',
    'doesnt_end_with' => 'Поле :attribute не повинне закінчуватись одним з наступних параметрів: :values.',
    'doesnt_start_with' => 'Поле :attribute не повинне починатися з одного з наступних: :values.',
    'email' => 'Поле :attribute має містити коректну електронну адресу.',
    'ends_with' => 'Поле :attribute повинне закінчуватися одним з наступних параметрів: :values.',
    'enum' => 'Обрана валюта недійсна.',
    'exists' => 'Обрана валюта недійсна.',
    'extensions' => 'Поле :attribute повинне містити одне з наступних розширень: :values.',
    'file' => 'Поле :attribute має містити файл.',
    'filled' => 'Поле :attribute повинно бути заповнене.',
    'gt' => [
        'array' => 'Кількість елементів поля :attribute повинно бути більше :value.',
        'file' => 'Поле :attribute має бути більше :value кілобайт.',
        'numeric' => 'Поле :attribute має бути більше :value.',
        'string' => 'Кількість символів поля :attribute повинно бути більше :value.',
    ],
    'gte' => [
        'array' => 'Поле :attribute повинне містити :value або більше учасників.',
        'file' => 'Поле :attribute має бути більше або дорівнювати :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути більше або дорівнювати :value.',
        'string' => 'Кількість символів поля :attribute повинно бути більше або дорівнювати :value.',
    ],
    'hex_color' => 'Поле :attribute має бути коректним hexadecimal кольором.',
    'image' => 'Поле :attribute має бути зображенням.',
    'import_field_empty'    => 'Значення для :fieldname не може бути null.',
    'in' => 'Обрана валюта недійсна.',
    'in_array' => 'Поле :attribute повинне існувати в :other.',
    'integer' => 'Поле :attribute має бути цілим числом.',
    'ip' => 'Поле :attribute має бути коректною IP-адресою.',
    'ipv4' => 'Поле :attribute має бути коректною адресою IPv4.',
    'ipv6' => 'Поле :attribute має бути коректною адресою IPv6.',
    'json' => 'Поле :attribute має бути коректним JSON рядком.',
    'list' => 'Поле :attribute має містити список.',
    'lowercase' => 'Поле :attribute має бути нижнім рядком.',
    'lt' => [
        'array' => 'Кількість елементів поля :attribute повинно бути менше ніж :value.',
        'file' => 'Поле :attribute має бути менше :value кілобайт.',
        'numeric' => 'Поле :attribute має бути менше :value.',
        'string' => 'Кількість символів поля :attribute повинно бути менше ніж :value.',
    ],
    'lte' => [
        'array' => 'Поле :attribute не повинне містити більше ніж :value.',
        'file' => 'Поле :attribute має бути менше або дорівнювати :value кілобайт.',
        'numeric' => 'Поле :attribute повинно бути менше або дорівнювати :value.',
        'string' => 'Кількість символів поля :attribute повинно бути менше або дорівнювати :value.',
    ],
    'mac_address' => ':attribute має бути коректною MAC адресою.',
    'max' => [
        'array' => 'Поле :attribute не повинне містити більше :max елементів.',
        'file' => 'Поле :attribute має бути не більше :max кілобайт.',
        'numeric' => 'Поле :attribute не має бути більше :max.',
        'string' => 'Поле :attribute має бути не більше :max символів.',
    ],
    'max_digits' => 'Поле :attribute не повинне містити більше :max цифр.',
    'mimes' => 'Поле :attribute повинне містити файл типу: :values.',
    'mimetypes' => 'Поле :attribute повинне містити файл типу: :values.',
    'min' => [
        'array' => 'Поле :attribute повинне містити не менше :min елементів.',
        'file' => 'Поле :attribute має бути не менше :min кілобайт.',
        'numeric' => 'Поле :attribute має бути не менше :min.',
        'string' => 'Поле :attribute повинне містити щонайменше :min символів.',
    ],
    'min_digits' => 'Поле :attribute повинне містити не менше :min цифр.',
    'missing' => 'Поле :attribute має бути відсутнє.',
    'missing_if' => 'Поле :attribute повинно бути відсутнє, якщо :other є :value.',
    'missing_unless' => 'Поле :attribute повинно бути відсутнє, якщо :other не є :value.',
    'missing_with' => 'Поле :attribute повинно бути відсутнім, коли :values вказано.',
    'missing_with_all' => 'Поле :attribute має бути відсутнім, коли :values вказані.',
    'multiple_of' => 'Поле :attribute повинне містити декілька :value.',
    'not_in' => 'Обрана валюта недійсна.',
    'not_regex' => 'Неприпустимий формат поля :attribute .',
    'numeric' => 'Поле :attribute повинне містити число.',
    'password' => [
        'letters' => 'Поле :attribute має містити принаймні одну букву.',
        'mixed' => 'Поле :attribute має містити принаймні одну велику й одну малу літеру.',
        'numbers' => 'Поле :attribute повинне містити принаймні одне число.',
        'symbols' => 'Поле :attribute має містити принаймні один символ.',
        'uncompromised' => 'Даний :attribute з\'явився в витоку даних. Будь ласка, виберіть інший :attribute.',
    ],
    'percent'       => 'Мінімальна амортизація повинна бути від 0 до 100, коли тип амортизації є відсотковим.',

    'present' => 'Поле :attribute повинне бути присутнім.',
    'present_if' => 'Поле :attribute має бути присутнім коли :other є рівним :value.',
    'present_unless' => 'Поле :attribute повинно бути присутнім тільки в :other : value.',
    'present_with' => 'Поле :attribute має бути присутнє коли :values вказано.',
    'present_with_all' => 'Поле :attribute має бути присутнім коли :values вказано.',
    'prohibited' => 'Поле :attribute заборонено.',
    'prohibited_if' => 'Поле :attribute заборонено, якщо :other є :value.',
    'prohibited_unless' => 'Поле :attribute заборонене, якщо :other не зазначено в :values.',
    'prohibits' => 'Поле :attribute забороняє :other бути присутнім.',
    'regex' => 'Неприпустимий формат поля :attribute .',
    'required' => 'Поле :attribute є обов\'язковим для заповнення.',
    'required_array_keys' => 'Поле :attribute повинне містити записи для: :values.',
    'required_if' => 'Поле :attribute є обов\'язковим для заповнення, коли :other є :value.',
    'required_if_accepted' => 'Поле :attribute обов\'язкове, коли :other буде прийнято.',
    'required_if_declined' => 'Поле :attribute є обов\'язковим для заповнення, коли :other відхилено.',
    'required_unless' => 'Поле :attribute обов\'язкове, якщо :other не з :values.',
    'required_with' => 'Поле :attribute є обов\'язковим для заповнення, коли :values вказано.',
    'required_with_all' => 'Поле :attribute є обов\'язковим для заповнення, коли :values вказано.',
    'required_without' => 'Поле :attribute є обов\'язковим для заповнення, коли :values не вказано.',
    'required_without_all' => 'Поле :attribute є обов\'язковим для заповнення, коли :values не вказано.',
    'same' => 'Поле :attribute має відповідати :other.',
    'size' => [
        'array' => 'Поле :attribute повинне містити :size елементів.',
        'file' => 'Поле :attribute має бути :size кілобайт.',
        'numeric' => 'Поле :attribute має бути :size.',
        'string' => 'Поле :attribute має містити :size символів.',
    ],
    'starts_with' => 'Поле :attribute повинне починатися з одного з наступних :values.',
    'string'               => 'Поле :attribute повинне містити текст.',
    'two_column_unique_undeleted' => 'Поле :attribute має бути унікальним для :table1 і :table2. ',
    'unique_undeleted'     => 'Поле :attribute має бути унікальним.',
    'non_circular'         => 'Поле :attribute не повинне створювати круглие посилання.',
    'not_array'            => ':attribute не може бути масивом.',
    'disallow_same_pwd_as_user_fields' => 'Пароль не може бути таким же, як ім\'я користувача.',
    'letters'              => 'Пароль має містити принаймні одну букву.',
    'numbers'              => 'Пароль має містити принаймні одну цифру.',
    'case_diff'            => 'Пароль повинен використовувати змішаний випадок.',
    'symbols'              => 'Пароль має містити символи.',
    'timezone' => 'Поле :attribute має містити коректну часову зону.',
    'unique' => ':attribute вже зайнятий.',
    'uploaded' => 'Завантаження поля :attribute не вдалося.',
    'uppercase' => 'Поле :attribute має бути з верхнім регістром.',
    'url' => 'Поле :attribute має бути коректним URL.',
    'ulid' => 'Поле :attribute має бути дійсним ULID.',
    'uuid' => 'Поле :attribute має бути коректним UUID.',
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

    'email_array'      => 'Одна або кілька адрес електронної пошти не правильні.',
    'checkboxes'           => ':attribute містить неприпустимі параметри.',
    'radio_buttons'        => ':attribute є неприпустимим.',
    
    'custom' => [
        'alpha_space' => 'Поле :attribute містить символ, який не допускається.',

        'hashed_pass'      => 'Поточний пароль неправильний',
        'dumbpwd'          => 'Цей пароль занадто вживаний.',
        'statuslabel_type' => 'Ви повинні вибрати правильний тип статуса',
        'custom_field_not_found'          => 'Це поле не існує, будь ласка, перевірте ваші власні імена полів.',
        'custom_field_not_found_on_model' => 'Це поле існує, але воно не доступне в цьому наборі поля даної моделі Активу.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => 'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD',
        'last_audit_date.date_format'   =>  'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD',
        'termination_date.date_format'  =>  'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD',
        'expected_checkin.date_format'  =>  'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD',
        'start_date.date_format'        =>  'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD',
        'end_date.date_format'          =>  'Поле :attribute має містити коректну дату в форматі YYYY-MM-DD',
        'invalid_value_in_field' => 'Невірне значення включене в це поле',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (змішаний регістр), ймовірно, не працює. Замість цього ви повинні використовувати <code>samaccountname</code> (нижній регістр).'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> , можливо, не є коректним фільтром авторизації. Ви мабуть хочете, <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Це значення, мабуть, не повинно бути в дужках.'],

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
        'invalid_value_in_field' => 'Невірне значення включене в це поле',
        'required' => 'Це поле обов\'язкове для заповнення',
        'email' => 'Будь ласка, введіть коректну адресу електронної пошти',
    ],


];
