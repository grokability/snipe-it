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

    'accepted' => ':attribute musi zostać zaakceptowany.',
    'accepted_if' => ':attribute musi być zaakceptowany, gdy :other ma wartość :value.',
    'active_url' => 'Pole :attribute musi być prawidłowym adresem URL.',
    'after' => 'Pole :attribute musi być datą po :date.',
    'after_or_equal' => 'Pole :attribute musi być datą po lub równą :date.',
    'alpha' => ':attribute może zawierać tylko litery.',
    'alpha_dash' => ':attribute może zawierać tylko litery, cyfry i myślniki.',
    'alpha_num' => ':attribute może zawierać tylko litery i cyfry.',
    'array' => 'Pole :attribute nie może być tablicą.',
    'ascii' => 'Pole :attribute musi zawierać tylko jednobajtowe znaki alfanumeryczne i symbole.',
    'before' => 'Pole :attribute musi być datą przed :date.',
    'before_or_equal' => 'Pole :attribute musi być datą przed lub równą :date.',
    'between' => [
        'array' => ':attribute musi mieć pomiędzy :min a :max elementów.',
        'file' => 'Pole :attribute musi być pomiędzy :min a :max kilobajtów.',
        'numeric' => 'Pole :attribute musi być pomiędzy :min a :max.',
        'string' => 'Pole :attribute musi zawierać się między :min a :max znaków.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Pole atrybutu: musi być prawdziwe lub fałszywe.',
    'can' => 'Pole :attribute zawiera nieautoryzowaną wartość.',
    'confirmed' => 'Potwierdzenie pola :attribute nie pasuje.',
    'contains' => 'Pole :attribute nie posiada wymaganej wartości.',
    'current_password' => 'Hasło jest nieprawidłowe.',
    'date' => 'Pole :attribute musi być prawidłową datą.',
    'date_equals' => 'Pole :attribute musi być datą równą :date.',
    'date_format' => 'Pole :attribute musi pasować do formatu :format.',
    'decimal' => 'Pole :attribute musi mieć :dziesiętne miejsca po przecinku.',
    'declined' => 'Pole :attribute musi zostać odrzucone.',
    'declined_if' => 'Pole :attribute musi zostać odrzucone, gdy :other jest :value.',
    'different' => 'Pole :attribute i :other muszą być różne.',
    'digits' => 'Pole :attribute musi zawierać :digits cyfry.',
    'digits_between' => 'Pole :attribute musi zawierać się między :min a :max cyfr.',
    'dimensions' => 'Pole :attribute ma nieprawidłowe wymiary obrazu.',
    'distinct' => 'Pole :attribute ma zduplikowane wartości.',
    'doesnt_end_with' => 'Pole :attribute nie może kończyć się jednym z następujących: :values.',
    'doesnt_start_with' => 'Pole :attribute nie może zaczynać się od jednego z następujących: :values.',
    'email' => 'Pole :attribute musi być poprawnym adresem e-mail.',
    'ends_with' => 'Pole :attribute musi kończyć się jednym z następujących: :values.',
    'enum' => 'Wybrany :attribute jest nieprawidłowy.',
    'exists' => 'Wybrane :attribute jest niewłaściwe.',
    'extensions' => 'Pole :attribute musi mieć jedno z następujących rozszerzeń: :values.',
    'file' => 'Pole :attribute musi być plikiem.',
    'filled' => 'Pole :attribute musi posiadać wartość.',
    'gt' => [
        'array' => ':attribute musi mieć więcej niż :value elementów.',
        'file' => 'Pole :attribute musi być większe niż :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być większe niż :value.',
        'string' => 'Pole :attribute musi być większe niż :value znaków.',
    ],
    'gte' => [
        'array' => ':attribute musi mieć :value elementów lub więcej.',
        'file' => 'Pole :attribute musi być większe lub równe :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być większe lub równe :value.',
        'string' => 'Pole :attribute musi być większe lub równe :value znaków.',
    ],
    'hex_color' => 'Pole :attribute musi być prawidłowym kolorem szesnastkowym.',
    'image' => 'Pole :attribute musi być obrazem.',
    'import_field_empty'    => 'Wartość dla :fieldname nie może być pusta.',
    'in' => 'Wybrane :attribute jest niewłaściwe.',
    'in_array' => 'Pole :attribute musi istnieć w :other.',
    'integer' => 'Pole :attribute musi być liczbą całkowitą.',
    'ip' => 'Pole :attribute musi być prawidłowym adresem IP.',
    'ipv4' => 'Atrybut: musi być prawidłowym adresem IPv4.',
    'ipv6' => 'Pole :attribute musi być prawidłowym adresem IPv6.',
    'json' => 'Atrybut: musi być prawidłowym ciągiem JSON.',
    'list' => 'Pole :attribute musi być listą.',
    'lowercase' => 'Pole :attribute musi być małą literą.',
    'lt' => [
        'array' => ':attribute musi mieć mniej niż :value elementów.',
        'file' => 'Pole :attribute musi być mniejsze niż :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być mniejsze niż :value.',
        'string' => 'Pole :attribute musi być mniejsze niż :value znaków.',
    ],
    'lte' => [
        'array' => ':attribute nie może mieć więcej niż :value elementów.',
        'file' => 'Pole :attribute musi być mniejsze lub równe :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być mniejsze lub równe :value.',
        'string' => 'Pole :attribute musi być mniejsze lub równe :value znaków.',
    ],
    'mac_address' => 'Pole :attribute musi być prawidłowym adresem MAC.',
    'max' => [
        'array' => ':attribute nie może mieć więcej niż :max elementów.',
        'file' => 'Pole :attribute nie może być większe niż :max kilobajtów.',
        'numeric' => 'Pole :attribute nie może być większe niż :max.',
        'string' => 'Pole :attribute nie może być większe niż :max znaków.',
    ],
    'max_digits' => 'Pole :attribute nie może zawierać więcej niż :max cyfr.',
    'mimes' => 'Pole :attribute musi być plikiem typu: :values.',
    'mimetypes' => 'Pole :attribute musi być plikiem typu: :values.',
    'min' => [
        'array' => 'Pole :attribute musi mieć co najmniej :min elementów.',
        'file' => 'Pole :attribute musi mieć co najmniej :min kilobajtów.',
        'numeric' => 'Pole :attribute musi być co najmniej :min.',
        'string' => 'Pole :attribute musi mieć co najmniej :min znaków.',
    ],
    'min_digits' => 'Pole :attribute musi zawierać co najmniej :min cyfr.',
    'missing' => 'Brakuje pola :attribute',
    'missing_if' => 'Pole :attribute musi być puste, gdy :other jest :value.',
    'missing_unless' => 'Pole :attribute musi być puste, chyba że :other jest :value.',
    'missing_with' => 'Pole :attribute musi być puste, gdy :values jest obecne.',
    'missing_with_all' => 'Pole :attribute musi być puste, gdy :values są obecne.',
    'multiple_of' => 'Pole :attribute musi być wielokrotnością :value.',
    'not_in' => 'Wybrany :attribute jest nieprawidłowy.',
    'not_regex' => 'Format pola :attribute jest nieprawidłowy.',
    'numeric' => 'Pole :attribute musi być liczbą.',
    'password' => [
        'letters' => 'Pole :attribute musi zawierać co najmniej jedną literę.',
        'mixed' => 'Pole :attribute musi zawierać co najmniej jedną wielką literę i jedną małą literę.',
        'numbers' => 'Pole :attribute musi zawierać co najmniej jedną liczbę.',
        'symbols' => 'Pole :attribute musi zawierać co najmniej jeden symbol.',
        'uncompromised' => 'Podany :attribute pojawił się w wycieku danych. Proszę wybrać inny :attribute.',
    ],
    'percent'       => 'Minimalna amortyzacja musi wynosić od 0 do 100 w przypadku gdy wartość procentowa amortyzacji jest wyrażona w procentach.',

    'present' => ':attribute nie może być puste.',
    'present_if' => 'Pole :attribute musi być obecne, gdy :other jest :value.',
    'present_unless' => 'Pole :attribute musi być obecne, chyba że :other jest :value.',
    'present_with' => ':attribute musi być obecny, gdy :values jest obecny.',
    'present_with_all' => ':attribute musi być obecny, gdy :values są obecne.',
    'prohibited' => 'Pole :attribute jest zabronione.',
    'prohibited_if' => 'Pole :attribute jest zabronione, gdy :other to :value.',
    'prohibited_unless' => 'Pole :attribute jest zabronione, chyba że :other znajduje się w :values.',
    'prohibits' => 'Pole :attribute zabrania :other obecności .',
    'regex' => 'Format pola :attribute jest nieprawidłowy.',
    'required' => ':attribute nie może być puste.',
    'required_array_keys' => 'Pole :attribute musi zawierać wpisy dla: :values.',
    'required_if' => 'Pole :attribute jest wymagane gdy :other jest :value.',
    'required_if_accepted' => 'Pole :attribute jest wymagane, gdy :other jest zaakceptowane.',
    'required_if_declined' => 'Pole :attribute jest wymagane, gdy :other jest odrzucone.',
    'required_unless' => 'Pole atrybutów: wymagane jest, chyba że inne są w: wartościach.',
    'required_with' => 'Pole :attribute jest wymagane gdy :values jest podana.',
    'required_with_all' => 'Pole :attribute jest wymagane, gdy :values są obecne.',
    'required_without' => 'Pole :attribute jest wymagane gdy :values nie jest podana.',
    'required_without_all' => 'Pole atrybutu: attribute jest wymagane, gdy żadna z: wartości nie jest obecna.',
    'same' => 'Pole :attribute musi pasować do :other.',
    'size' => [
        'array' => ':attribute musi zawierać :size elementów.',
        'file' => 'Pole :attribute musi mieć :size kilobajtów.',
        'numeric' => 'Pole :attribute musi być :size.',
        'string' => 'Pole :attribute musi mieć :size znaków.',
    ],
    'starts_with' => 'Pole :attribute musi zaczynać się od jednego z następujących: :values.',
    'string'               => 'Atrybut: atrybut musi być ciągiem.',
    'two_column_unique_undeleted' => ':attribute musi być unikalny pomiędzy :table1 i :table2. ',
    'unique_undeleted'     => 'Wartość :attribute musi być unikalna.',
    'non_circular'         => ':attribute nie może tworzyć odwołań cyklicznych.',
    'not_array'            => ':attribute nie może być tablicą.',
    'disallow_same_pwd_as_user_fields' => 'Hasło nie może być takie samo jak nazwa użytkownika.',
    'letters'              => 'Hasło musi zawierać co najmniej jedną literę.',
    'numbers'              => 'Hasło musi zawierać co najmniej jedną cyfrę.',
    'case_diff'            => 'Hasło musi zawierać małe i wielkie litery.',
    'symbols'              => 'Hasło musi zawierać znaki specjalne.',
    'timezone' => 'Pole :attribute musi być poprawną strefą czasową.',
    'unique' => ':attribute został już wzięty.',
    'uploaded' => 'Nie udało się przesłać atrybutu:.',
    'uppercase' => 'Pole :attribute musi być wielkimi literami.',
    'url' => 'Pole :attribute musi być prawidłowym adresem URL.',
    'ulid' => 'Pole :attribute musi być poprawnym ULID.',
    'uuid' => 'Pole :attribute musi być prawidłowym UUID.',
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

    'email_array'      => 'Jeden lub więcej wprowadzonych adresów jest nieprawidłowy.',
    'checkboxes'           => ':attribute zawiera nieprawidłowe opcje.',
    'radio_buttons'        => ':attribute jest nieprawidłowy.',
    
    'custom' => [
        'alpha_space' => 'Pole: attribute zawiera znak, który nie jest dozwolony.',

        'hashed_pass'      => 'Twoje bieżące hasło jest niepoprawne',
        'dumbpwd'          => 'To hasło jest zbyt powszechne.',
        'statuslabel_type' => 'Musisz wybrać odpowiedni typ etykiety statusu',
        'custom_field_not_found'          => 'To pole nie istnieje, sprawdź dokładnie nazwy pól niestandardowych.',
        'custom_field_not_found_on_model' => 'To pole wydaje się istnieć, ale nie jest dostępne w tym zestawie pól Modelu Zasobów.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute musi być prawidłową datą w formacie YYYY-MM-DD',
        'last_audit_date.date_format'   =>  ':attribute musi być prawidłową datą w formacie YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  ':attribute musi być prawidłową datą w formacie YYYY-MM-DD',
        'termination_date.date_format'  =>  ':attribute musi być prawidłową datą w formacie YYYY-MM-DD',
        'expected_checkin.date_format'  =>  ':attribute musi być prawidłową datą w formacie YYYY-MM-DD',
        'start_date.date_format'        =>  ':attribute musi być prawidłową datą w formacie YYYY-MM-DD',
        'end_date.date_format'          =>  ':attribute musi być prawidłową datą w formacie YYYY-MM-DD',
        'invalid_value_in_field' => 'Nieprawidłowa wartość dołączona do tego pola',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (przypadek mieszany) prawdopodobnie nie zadziała. Zamiast tego powinieneś użyć <code>samaccountname</code> (małe przypadki).'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> prawdopodobnie nie jest prawidłowym filtrem uwierzytelniającym. Prawdopodobnie chcesz <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Ta wartość prawdopodobnie nie powinna być zawijana w nawiasy.'],

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
        'invalid_value_in_field' => 'Nieprawidłowa wartość dołączona do tego pola',
        'required' => 'To pole jest wymagane',
        'email' => 'Podaj poprawny adres e-mail',
    ],


];
