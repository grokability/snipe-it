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

    'accepted' => 'Câmpul :attribute trebuie acceptat.',
    'accepted_if' => 'Câmpul :attribute trebuie acceptat atunci când :other este :value.',
    'active_url' => 'Câmpul :attribute trebuie să fie o adresă URL validă.',
    'after' => 'Câmpul :attribute trebuie să fie o dată ulterioară datei :date.',
    'after_or_equal' => 'Câmpul :attribute trebuie să fie o dată ulterioară sau egală cu :date.',
    'alpha' => 'Câmpul :attribute trebuie să conțină doar litere.',
    'alpha_dash' => 'Câmpul :attribute trebuie să conțină doar litere, cifre, cratime și subliniere.',
    'alpha_num' => 'Câmpul :attribute trebuie să conțină doar litere și cifre.',
    'array' => 'Câmpul :attribute trebuie să fie un tablou.',
    'ascii' => 'Câmpul :attribute trebuie să conțină doar caractere alfanumerice și simboluri pe un singur octet.',
    'before' => 'Câmpul :attribute trebuie să fie o dată anterioară datei :date.',
    'before_or_equal' => 'Câmpul :attribute trebuie să fie o dată anterioară sau egală cu :date.',
    'between' => [
        'array' => 'Câmpul :attribute trebuie să aibă între :min și :max elemente.',
        'file' => 'Câmpul :attribute trebuie să aibă între :min și :max kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să fie între :min și :max.',
        'string' => 'Câmpul :attribute trebuie să aibă între :min și :max caractere.',
    ],
    'valid_regex' => 'Expresia regulată este invalidă.',
    'boolean' => 'Câmpul :attribute trebuie să fie adevărat sau fals.',
    'can' => 'Câmpul :attribute conține o valoare neautorizată.',
    'confirmed' => 'Confirmarea câmpului :attribute nu se potrivește.',
    'contains' => 'Câmpului :attribute îi lipsește o valoare necesară.',
    'current_password' => 'Parola este incorectă.',
    'date' => 'Câmpul :attribute trebuie să fie o dată validă.',
    'date_equals' => 'Câmpul :attribute trebuie să fie o dată egală cu :date.',
    'date_format' => 'Câmpul :attribute trebuie să se potrivească formatului :format.',
    'decimal' => 'Câmpul :attribute trebuie să aibă :decimal zecimale.',
    'declined' => 'Câmpul :attribute trebuie refuzat.',
    'declined_if' => 'Câmpul :attribute trebuie refuzat atunci când :other este :value.',
    'different' => 'Câmpul :attribute și :other trebuie să fie diferite.',
    'digits' => 'Câmpul :attribute trebuie să aibă :digits cifre.',
    'digits_between' => 'Câmpul :attribute trebuie să aibă între :min și :max cifre.',
    'dimensions' => 'Câmpul :attribute are dimensiuni de imagine invalide.',
    'distinct' => 'Câmpul :attribute are o valoare duplicat.',
    'doesnt_end_with' => 'Câmpul :attribute nu trebuie să se termine cu una dintre următoarele: :values.',
    'doesnt_start_with' => 'Câmpul :attribute nu trebuie să înceapă cu una dintre următoarele: :values.',
    'email' => 'Câmpul :attribute trebuie să fie o adresă de email validă.',
    'ends_with' => 'Câmpul :attribute trebuie să se termine cu una dintre următoarele: :values.',
    'enum' => ':attribute-ul selectat este invalid.',
    'exists' => ':attribute-ul selectat este invalid.',
    'extensions' => 'Câmpul :attribute trebuie să aibă una dintre următoarele extensii: :values.',
    'file' => 'Câmpul :attribute trebuie să fie un fișier.',
    'filled' => 'Câmpul :attribute trebuie să aibă o valoare.',
    'gt' => [
        'array' => 'Câmpul :attribute trebuie să aibă mai mult de :value elemente.',
        'file' => 'Câmpul :attribute trebuie să fie mai mare de :value kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mare de :value.',
        'string' => 'Câmpul :attribute trebuie să aibă mai mult de :value caractere.',
    ],
    'gte' => [
        'array' => 'Câmpul :attribute trebuie să aibă :value elemente sau mai mult.',
        'file' => 'Câmpul :attribute trebuie să fie mai mare sau egal cu :value kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mare sau egal cu :value.',
        'string' => 'Câmpul :attribute trebuie să aibă mai mult sau egal cu :value caractere.',
    ],
    'hex_color' => 'Câmpul :attribute trebuie să fie o culoare hexadecimală validă.',
    'image' => 'Câmpul :attribute trebuie să fie o imagine.',
    'import_field_empty' => 'Valoarea pentru :fieldname nu poate fi nulă.',
    'in' => ':attribute-ul selectat este invalid.',
    'in_array' => 'Câmpul :attribute trebuie să existe în :other.',
    'integer' => 'Câmpul :attribute trebuie să fie un număr întreg.',
    'ip' => 'Câmpul :attribute trebuie să fie o adresă IP validă.',
    'ipv4' => 'Câmpul :attribute trebuie să fie o adresă IPv4 validă.',
    'ipv6' => 'Câmpul :attribute trebuie să fie o adresă IPv6 validă.',
    'json' => 'Câmpul :attribute trebuie să fie un șir JSON valid.',
    'list' => 'Câmpul :attribute trebuie să fie o listă.',
    'lowercase' => 'Câmpul :attribute trebuie să conțină doar litere mici.',
    'lt' => [
        'array' => 'Câmpul :attribute trebuie să aibă mai puțin de :value elemente.',
        'file' => 'Câmpul :attribute trebuie să fie mai mic de :value kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mic de :value.',
        'string' => 'Câmpul :attribute trebuie să aibă mai puțin de :value caractere.',
    ],
    'lte' => [
        'array' => 'Câmpul :attribute nu trebuie să aibă mai mult de :value elemente.',
        'file' => 'Câmpul :attribute trebuie să fie mai mic sau egal cu :value kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mic sau egal cu :value.',
        'string' => 'Câmpul :attribute trebuie să aibă mai puțin sau egal cu :value caractere.',
    ],
    'mac_address' => 'Câmpul :attribute trebuie să fie o adresă MAC validă.',
    'max' => [
        'array' => 'Câmpul :attribute nu trebuie să aibă mai mult de :max elemente.',
        'file' => 'Câmpul :attribute nu trebuie să fie mai mare de :max kilobiți.',
        'numeric' => 'Câmpul :attribute nu trebuie să fie mai mare de :max.',
        'string' => 'Câmpul :attribute nu trebuie să aibă mai mult de :max caractere.',
    ],
    'max_digits' => 'Câmpul :attribute nu trebuie să aibă mai mult de :max cifre.',
    'mimes' => 'Câmpul :attribute trebuie să fie un fișier de tip: :values.',
    'mimetypes' => 'Câmpul :attribute trebuie să fie un fișier de tip: :values.',
    'min' => [
        'array' => 'Câmpul :attribute trebuie să aibă cel puțin :min elemente.',
        'file' => 'Câmpul :attribute trebuie să aibă cel puțin :min kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să aibă cel puțin :min.',
        'string' => 'Câmpul :attribute trebuie să aibă cel puțin :min caractere.',
    ],
    'min_digits' => 'Câmpul :attribute trebuie să aibă cel puțin :min cifre.',
    'missing' => 'Câmpul :attribute trebuie să lipsească.',
    'missing_if' => 'Câmpul :attribute trebuie să lipsească atunci când :other este :value.',
    'missing_unless' => 'Câmpul :attribute trebuie să lipsească, cu excepția cazului în care :other este :value.',
    'missing_with' => 'Câmpul :attribute trebuie să lipsească atunci când :values este prezent.',
    'missing_with_all' => 'Câmpul :attribute trebuie să lipsească atunci când :values sunt prezente.',
    'multiple_of' => 'Câmpul :attribute trebuie să fie un multiplu de :value.',
    'not_in' => ':attribute-ul selectat este invalid.',
    'not_regex' => 'Formatul câmpului :attribute este invalid.',
    'numeric' => 'Câmpul :attribute trebuie să fie un număr.',
    'password' => [
        'letters' => 'Câmpul :attribute trebuie să conțină cel puțin o literă.',
        'mixed' => 'Câmpul :attribute trebuie să conțină cel puțin o literă mare și una mică.',
        'numbers' => 'Câmpul :attribute trebuie să conțină cel puțin o cifră.',
        'symbols' => 'Câmpul :attribute trebuie să conțină cel puțin un simbol.',
        'uncompromised' => ':attribute-ul dat a apărut într-o scurgere de date. Vă rugăm să alegeți un alt :attribute.',
    ],
    'percent' => 'Minimul de depreciere trebuie să fie între 0 și 100 atunci când tipul de depreciere este procentual.',

    'present' => 'Câmpul :attribute trebuie să fie prezent.',
    'present_if' => 'Câmpul :attribute trebuie să fie prezent atunci când :other este :value.',
    'present_unless' => 'Câmpul :attribute trebuie să fie prezent, cu excepția cazului în care :other este :value.',
    'present_with' => 'Câmpul :attribute trebuie să fie prezent atunci când :values este prezent.',
    'present_with_all' => 'Câmpul :attribute trebuie să fie prezent atunci când :values sunt prezente.',
    'prohibited' => 'Câmpul :attribute este interzis.',
    'prohibited_if' => 'Câmpul :attribute este interzis atunci când :other este :value.',
    'prohibited_unless' => 'Câmpul :attribute este interzis, cu excepția cazului în care :other este în :values.',
    'prohibits' => 'Câmpul :attribute interzice ca :other să fie prezent.',
    'regex' => 'Formatul câmpului :attribute este invalid.',
    'required' => 'Câmpul :attribute este obligatoriu.',
    'required_array_keys' => 'Câmpul :attribute trebuie să conțină intrări pentru: :values.',
    'required_if' => 'Câmpul :attribute este obligatoriu atunci când :other este :value.',
    'required_if_accepted' => 'Câmpul :attribute este obligatoriu atunci când :other este acceptat.',
    'required_if_declined' => 'Câmpul :attribute este obligatoriu atunci când :other este refuzat.',
    'required_unless' => 'Câmpul :attribute este obligatoriu, cu excepția cazului în care :other este în :values.',
    'required_with' => 'Câmpul :attribute este obligatoriu atunci când :values este prezent.',
    'required_with_all' => 'Câmpul :attribute este obligatoriu atunci când :values sunt prezente.',
    'required_without' => 'Câmpul :attribute este obligatoriu atunci când :values nu este prezent.',
    'required_without_all' => 'Câmpul :attribute este obligatoriu atunci când niciunul dintre :values nu este prezent.',
    'same' => 'Câmpul :attribute trebuie să se potrivească cu :other.',
    'size' => [
        'array' => 'Câmpul :attribute trebuie să conțină :size elemente.',
        'file' => 'Câmpul :attribute trebuie să aibă :size kilobiți.',
        'numeric' => 'Câmpul :attribute trebuie să aibă :size.',
        'string' => 'Câmpul :attribute trebuie să aibă :size caractere.',
    ],
    'starts_with' => 'Câmpul :attribute trebuie să înceapă cu una dintre următoarele: :values.',
    'string' => 'Câmpul :attribute trebuie să fie un șir de caractere.',
    'two_column_unique_undeleted' => 'Câmpul :attribute trebuie să fie unic în :table1 și :table2.',
    'unique_undeleted' => 'Câmpul :attribute trebuie să fie unic.',
    'non_circular' => 'Câmpul :attribute nu trebuie să creeze o referință circulară.',
    'not_array' => ':attribute nu poate fi un tablou.',
    'disallow_same_pwd_as_user_fields' => 'Parola nu poate fi aceeași cu numele de utilizator.',
    'letters' => 'Parola trebuie să conțină cel puțin o literă.',
    'numbers' => 'Parola trebuie să conțină cel puțin o cifră.',
    'case_diff' => 'Parola trebuie să utilizeze litere mari și mici.',
    'symbols' => 'Parola trebuie să conțină simboluri.',
    'timezone' => 'Câmpul :attribute trebuie să fie un fus orar valid.',
    'unique' => ':attribute a fost deja preluat.',
    'uploaded' => 'Încărcarea :attribute a eșuat.',
    'uppercase' => 'Câmpul :attribute trebuie să conțină doar litere mari.',
    'url' => 'Câmpul :attribute trebuie să fie o adresă URL validă.',
    'ulid' => 'Câmpul :attribute trebuie să fie un ULID valid.',
    'uuid' => 'Câmpul :attribute trebuie să fie un UUID valid.',
    'fmcs_location' => 'Suportul complet pentru companii multiple și scopul locației sunt activate în Setările Admin, iar locația și compania selectate nu sunt compatibile.',


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

    'email_array' => 'Una sau mai multe adrese de email sunt invalide.',
    'checkboxes' => ':attribute conține opțiuni invalide.',
    'radio_buttons' => ':attribute este invalid.',

    'custom' => [
        'alpha_space' => 'Câmpul :attribute conține un caracter nepermis.',

        'hashed_pass' => 'Parola ta curentă este incorectă',
        'dumbpwd' => 'Această parolă este prea comună.',
        'statuslabel_type' => 'Trebuie să selectați un tip valid de etichetă de stare',
        'custom_field_not_found' => 'Acest câmp nu pare să existe, vă rugăm să verificați numele câmpurilor personalizate.',
        'custom_field_not_found_on_model' => 'Acest câmp pare să existe, dar nu este disponibil în setul de câmpuri al acestui Model de Activ.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ',
        'last_audit_date.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ oo:mm:ss',
        'expiration_date.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ',
        'termination_date.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ',
        'expected_checkin.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ',
        'start_date.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ',
        'end_date.date_format' => 'Câmpul :attribute trebuie să fie o dată validă în formatul AAAA-LL-ZZ',
        'invalid_value_in_field' => 'Valoare invalidă inclusă în acest câmp',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code> (cu litere mari/mici) probabil nu va funcționa. Ar trebui să utilizați <code>samaccountname</code> (cu litere mici) în schimb.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> probabil nu este un filtru de autentificare valid. Probabil doriți <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Această valoare probabil nu ar trebui să fie înconjurată de paranteze.'],

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
        'invalid_value_in_field' => 'Valoare invalidă inclusă în acest câmp',
        'required' => 'Acest câmp este obligatoriu',
        'email' => 'Vă rugăm să introduceți o adresă de email validă',
    ],


];