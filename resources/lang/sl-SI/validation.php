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

    'accepted' => 'Polje :attribute mora biti sprejeto.',
    'accepted_if' => 'Polje :attribute mora biti sprejeto, če je :other enako :value.',
    'active_url' => 'Polje :attribute mora biti veljaven URL.',
    'after' => 'Polje :attribute mora biti datum po :date.',
    'after_or_equal' => 'Polje :attribute mora biti datum, ki je po ali enak :date.',
    'alpha' => 'Polje :attribute lahko vsebuje samo črke.',
    'alpha_dash' => 'Polje :attribute lahko vsebuje samo črke, številke, pomišljaje in podčrtaje.',
    'alpha_num' => 'Atribut :attribute lahko vsebuje le črke in številke.',
    'array' => 'The :attribute field must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'Polje :attribute mora biti datum pred :date.',
    'before_or_equal' => 'Polje :attribute mora biti datum pred ali enak :date.',
    'between' => [
        'array' => 'Polje :attribute mora vsebovati med :min in :max elementov.',
        'file' => 'Polje :attribute mora biti veliko med :min in :max kilobajti.',
        'numeric' => 'Polje :attribute mora biti med :min in :max.',
        'string' => 'Polje :attribute mora imeti med :min in :max znaki.',
    ],
    'valid_regex' => 'Regularni izraz je neveljaven.',
    'boolean' => 'Polje :attribute mora biti true ali false.',
    'can' => 'Polje :attribute vsebuje nepooblaščeno vrednost.',
    'confirmed' => 'Potrditev polja :attribute se ne ujema.',
    'contains' => 'V polju :attribute manjka obvezna vrednost.',
    'current_password' => 'Geslo je napačno.',
    'date' => 'Polje :attribute mora biti veljaven datum.',
    'date_equals' => 'Polje :attribute mora biti datum, enak :date.',
    'date_format' => 'Polje :attribute se mora ujemati z obliko :format.',
    'decimal' => 'Polje :attribute mora imeti :decimal decimalnih mest.',
    'declined' => 'Polje :attribute mora biti zavrnjeno.',
    'declined_if' => 'Polje :attribute mora biti zavrnjeno, če je :other enako :value.',
    'different' => 'Polji :attribute in :other se morata razlikovati.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'Polje atribut ima podvojeno vrednost.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'Izbrani atribut je neveljaven.',
    'exists' => 'Izbrani atribut je neveljaven.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'Polje :attribute mora biti datoteka.',
    'filled' => 'Polje atribut mora imeti vrednost.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'Polje :attribute mora biti veljavna hexadecimal barva.',
    'image' => 'The :attribute field must be an image.',
    'import_field_empty'    => 'The value for :fieldname cannot be null.',
    'in' => 'Izbrani atribut je neveljaven.',
    'in_array' => 'Polje :attribute mora obstajati v :other.',
    'integer' => 'Polje :attribute mora biti celo število.',
    'ip' => 'Polje :attribute mora biti veljaven IP-naslov.',
    'ipv4' => 'Polje :attribute mora biti veljaven naslov IPv4.',
    'ipv6' => 'Polje :attribute mora biti veljaven naslov IPv6.',
    'json' => 'Polje :attribute mora biti veljaven niz JSON.',
    'list' => 'Polje :attribute mora biti seznam.',
    'lowercase' => 'Polje :attribute mora biti zapisano z malimi črkami.',
    'lt' => [
        'array' => 'Polje :attribute mora imeti manj kot :value elementov.',
        'file' => 'Polje :attribute mora biti manjše od :value kilobajtov.',
        'numeric' => 'Polje :attribute mora biti manjše od :value.',
        'string' => 'Polje :attribute mora imeti manj kot :value znakov.',
    ],
    'lte' => [
        'array' => 'Polje :attribute ne sme vsebovati več kot :value elementov.',
        'file' => 'Polje :attribute mora biti manjše ali enako :value kilobajtom.',
        'numeric' => 'Polje :attribute mora biti manjše ali enako :value.',
        'string' => 'Polje :attribute mora biti manjše ali enako znakom :value.',
    ],
    'mac_address' => 'Polje :attribute mora biti veljaven MAC naslov.',
    'max' => [
        'array' => 'Polje :attribute ne sme vsebovati več kot :max elementov.',
        'file' => 'Polje :attribute ne sme biti večje od :max kilobajtov.',
        'numeric' => 'Polje :attribute ne sme biti večje od :max.',
        'string' => 'Polje :attribute ne sme imeti več kot :max znakov.',
    ],
    'max_digits' => 'Polje :attribute ne sme imeti več kot :max števk.',
    'mimes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'mimetypes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'min' => [
        'array' => 'Polje :attribute mora vsebovati vsaj :min elementov.',
        'file' => 'Polje :attribute mora biti veliko vsaj :min kilobajtov.',
        'numeric' => 'Polje :attribute mora imeti vsaj :min.',
        'string' => 'Polje :attribute mora vsebovati vsaj :min znakov.',
    ],
    'min_digits' => 'Polje :attribute mora imeti vsaj :min števk.',
    'missing' => 'Polje :attribute manjka.',
    'missing_if' => 'Polje :attribute mora manjkati, če je :other enak :value.',
    'missing_unless' => 'Polje :attribute mora manjkati, razen če je :other enak :value.',
    'missing_with' => 'Polje :attribute mora manjkati, če je prisotno :values.',
    'missing_with_all' => 'Polje :attribute mora manjkati, če so prisotne :values.',
    'multiple_of' => 'Polje :attribute mora biti večkratnik polja :value.',
    'not_in' => 'Izbrani atribut je neveljaven.',
    'not_regex' => 'Format polja :attribute ni veljaven.',
    'numeric' => 'Polje :attribute mora biti številka.',
    'password' => [
        'letters' => 'Polje :attribute mora vsebovati vsaj eno črko.',
        'mixed' => 'Polje :attribute mora vsebovati vsaj eno veliko in eno malo črko.',
        'numbers' => 'Polje :attribute mora vsebovati vsaj eno številko.',
        'symbols' => 'Polje :attribute mora vsebovati vsaj en simbol.',
        'uncompromised' => 'Dani atribut :attribute se je pojavil v uhajanju podatkov. Izberite drug atribut :attribute.',
    ],
    'percent'       => 'Minimalna vrednost amortizacije mora biti med 0 in 100, če je vrsta amortizacije odstotek.',

    'present' => 'Polje atribut mora biti prisotno.',
    'present_if' => 'Polje :attribute mora biti prisotno, kadar je :other enak :value.',
    'present_unless' => 'Polje :attribute mora biti prisotno, razen če je :other enak :value.',
    'present_with' => 'Polje :attribute mora biti prisotno, kadar je prisoten :values.',
    'present_with_all' => 'Polje :attribute mora biti prisotno, kadar so prisotne :values.',
    'prohibited' => 'Polje :attribute je prepovedano.',
    'prohibited_if' => 'Polje :attribute je prepovedano, če je :other enako :value.',
    'prohibited_unless' => 'Polje :attribute je prepovedano, razen če je :other v :values.',
    'prohibits' => 'Polje :attribute prepoveduje prisotnost :other.',
    'regex' => 'Format polja :attribute ni veljaven.',
    'required' => 'Polje ne sme biti prazno.',
    'required_array_keys' => 'Polje :attribute mora vsebovati vnose za: :values.',
    'required_if' => 'Polje atributa je obvezno, če: drugo je: vrednost.',
    'required_if_accepted' => 'Polje :attribute je obvezno, kadar je sprejeto :other.',
    'required_if_declined' => 'Polje :attribute je obvezno, če je zavrnjeno polje :other.',
    'required_unless' => 'Polje atributa je obvezno, razen če je: drugo v: vrednosti.',
    'required_with' => 'Polje atributa je obvezno, ko: so prisotne vrednosti.',
    'required_with_all' => 'Polje :attribute je obvezno, kadar so prisotne :values.',
    'required_without' => 'Polje atributa je obvezno, če: vrednosti niso prisotne.',
    'required_without_all' => 'Polje atributa je obvezno, če nobena od: vrednosti ni prisotna.',
    'same' => 'Polje :attribute se mora ujemati z :other.',
    'size' => [
        'array' => 'Polje :attribute mora vsebovati elemente :size.',
        'file' => 'Polje :attribute mora biti :size kilobajtov.',
        'numeric' => 'Polje :attribute mora biti :size.',
        'string' => 'Polje :attribute mora imeti :size znakov.',
    ],
    'starts_with' => 'Polje :attribute se mora začeti z eno od naslednjih vrednosti: :values.',
    'string'               => 'Atribut mora biti niz.',
    'two_column_unique_undeleted' => 'Atribut :attribute mora biti enoličen v :table1 in :table2. ',
    'unique_undeleted'     => 'Atribut mora biti edinstven.',
    'non_circular'         => 'Atribut :attribute ne sme ustvariti krožne reference.',
    'not_array'            => ':attribute cannot be an array.',
    'disallow_same_pwd_as_user_fields' => 'Geslo ne sme biti enako uporabniškemu imenu.',
    'letters'              => 'Geslo mora vsebovati vsaj eno črko.',
    'numbers'              => 'Geslo mora vsebovati vsaj eno številko.',
    'case_diff'            => 'Geslo mora biti mešano z velikimi in malimi črkami.',
    'symbols'              => 'Geslo mora vsebovati simbole.',
    'timezone' => 'Polje :attribute mora biti veljaven časovni pas.',
    'unique' => 'Atribut je bil že sprejet.',
    'uploaded' => 'Atribut se ni uspel naložiti.',
    'uppercase' => 'Polje :attribute mora biti zapisano z velikimi črkami.',
    'url' => 'Polje :attribute mora biti veljaven URL.',
    'ulid' => 'Polje :attribute mora biti veljaven ULID.',
    'uuid' => 'Polje :attribute mora biti veljaven UUID.',
    'fmcs_location' => 'V skrbniških nastavitvah je omogočena polna podpora za več podjetij in določanje obsega lokacij, izbrana lokacija in izbrano podjetje pa nista združljiva.',


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

    'email_array'      => 'En ali več e-poštnih naslovov je napačnih.',
    'checkboxes'           => ':attribute vsebuje neveljavne možnosti.',
    'radio_buttons'        => ':atribut je neveljaven.',
    
    'custom' => [
        'alpha_space' => 'Polje atributa vsebuje znak, ki ni dovoljen.',

        'hashed_pass'      => 'Vaše trenutno geslo je napačno',
        'dumbpwd'          => 'To geslo je preveč pogosto.',
        'statuslabel_type' => 'Izbrati morate veljavn status oznake',
        'custom_field_not_found'          => 'Zdi se, da to polje ne obstaja. Prosimo, dvakrat preverite imena polj po meri.',
        'custom_field_not_found_on_model' => 'This field seems to exist, but is not available on this Asset Model\'s fieldset.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => 'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD',
        'last_audit_date.date_format'   =>  'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD',
        'termination_date.date_format'  =>  'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD',
        'expected_checkin.date_format'  =>  'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD',
        'start_date.date_format'        =>  'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD',
        'end_date.date_format'          =>  'Atribut :attribute mora biti veljaven datum v formatu YYYY-MM-DD',
        'invalid_value_in_field' => 'V tem polju je neveljavna vrednost',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (z mešanimi velikimi in malimi črkami) verjetno ne bo delovalo. Namesto tega uporabite <code>samaccountname</code> (z malimi črkami).'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> verjetno ni veljaven filter za avtorizacijo. Verjetno želite <code>uid=</code> '],
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
        'invalid_value_in_field' => 'V tem polju je neveljavna vrednost',
        'required' => 'To polje je obvezno',
        'email' => 'Vnesite veljaven e-poštni naslov',
    ],


];
