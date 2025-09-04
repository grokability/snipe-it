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

    'accepted' => 'Polje :attribute mora biti prihvaćeno.',
    'accepted_if' => 'Polje :attribute je mora biti prihvaćeno kada polje :other sadrži :value.',
    'active_url' => 'Polje :attribute mora biti ispravna URL adresa.',
    'after' => 'Polje :attribute mora biti datum kasniji od :date.',
    'after_or_equal' => 'Polje :attribute mora biti datum kasniji ili jednak od :date.',
    'alpha' => 'Polje :attribute mora da sadrži samo slova.',
    'alpha_dash' => 'Polje :attribute mora da sadrži jedino slova, brojeve, crtice i donje crtice.',
    'alpha_num' => 'Polje :attribute mora da sadrži samo slova i brojeve.',
    'array' => 'Polje :attribute mora biti niz.',
    'ascii' => 'Polje :attribute mora da sadrži samo alfanumeričke karaktere i simbole od jednog bajta.',
    'before' => 'Polje :attribute mora biti datum raniji od :date.',
    'before_or_equal' => 'Polje :attribute mora biti datum raniji ili jednak od :date.',
    'between' => [
        'array' => 'Polje :attribute mora sadržati između :min i :max stavki.',
        'file' => 'Polje :attribute mora biti između :min i :max kilobajta.',
        'numeric' => 'Polje :attribute mora biti između :min i :max.',
        'string' => 'Polje :attribute mora biti između :min i :max karaktera.',
    ],
    'valid_regex' => 'Regularni izraz nije ispravan.',
    'boolean' => 'Polje :attribute mora biti tačno ili netačno.',
    'can' => 'Polje :attribute mora da sadrži neovlašćenu vrednost.',
    'confirmed' => 'Potvrda polja :attribute se ne poklapa.',
    'contains' => 'Polju :attribute nedostaje neophodna vrednost.',
    'current_password' => 'Lozinka nije ispravna.',
    'date' => 'Polje :attribute mora biti ispravan datum.',
    'date_equals' => 'Polje :attribute mora biti datum jednak :data.',
    'date_format' => 'Polje :attribute mora da se poklapa sa formatom :format.',
    'decimal' => 'Polje :attribute mora da sadrži :decimal decimalnih mesta.',
    'declined' => 'Polje :attribute mora biti odbijeno.',
    'declined_if' => 'Polje :attribute mora biti odbijeno kada :other sadrži :value.',
    'different' => 'Polje :attribute i :other moraju da se razlikuju.',
    'digits' => 'Polje :attribute mora da sadrži :digits cifara.',
    'digits_between' => 'Polje :attribute mora da sadrži između :min i :max cifara.',
    'dimensions' => 'Polje :attribute sadrži neispravne dimenzije slike.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'Polje :attribute ne sme da se završi sa jednim od sledećih: :values.',
    'doesnt_start_with' => 'Polje :attribute ne sme da počen sa jednim od sledećih: :values.',
    'email' => 'Polje :attribute mora biti ispravna adresa e-pošte.',
    'ends_with' => 'Polje :attribute mora se završiti sa jednim od sledećih: :values.',
    'enum' => 'Odabrani :attribute nije ispravan.',
    'exists' => 'Odabrani :attribute nije korektan.',
    'extensions' => 'Polje :attribute mora da sadrži jednu od sledećih ekstenzija: :values.',
    'file' => 'Polje :attribute mora biti datoteka.',
    'filled' => ':attribute mora imati vrednost.',
    'gt' => [
        'array' => 'Polje :attribute mora sa sadrži više od :values stavki.',
        'file' => 'Polje :attribute mora biti veće od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće od :value.',
        'string' => 'Polje :attribute mora biti veće od :value karaktera.',
    ],
    'gte' => [
        'array' => 'Polje :attribute mora da sadrži :value ili više stavki.',
        'file' => 'Polje :attribute mora biti veće ili jednako sa :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti veće od ili jednako sa :value.',
        'string' => 'Polje :attribute mora da sadrži više ili jednako :value karaktera.',
    ],
    'hex_color' => 'Polje :attribute mora biti ispravna heksadecimalna boja.',
    'image' => 'Polje :attribute mora biti slika.',
    'import_field_empty'    => 'Vrednost za :fieldname ne može biti prazna.',
    'in' => 'Odabrani :attribute nije korektan.',
    'in_array' => 'Polje :attribute mora da postoji u :other.',
    'integer' => 'Polje :attribute mora biti celobrojna vrednost.',
    'ip' => 'Polje :attribute mora biti ispravna IP adresa.',
    'ipv4' => 'Polje :attribute mora biti ispravna IPv4 adresa.',
    'ipv6' => 'Polje :attribute mora biti ispravna IPv6 adresa.',
    'json' => 'Polje :attribute mora biti ispravan JSON tekst.',
    'list' => 'Polje :attribute mora biti spisak.',
    'lowercase' => 'Polje :attribute mora da sadrži sve mala slova.',
    'lt' => [
        'array' => 'Polje :attribute mora da sadrži manje od :value stavki.',
        'file' => 'Polje :attribute mora biti manje od :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje od :value.',
        'string' => 'Polje :attribute mora da sadrži manje od :value karaktera.',
    ],
    'lte' => [
        'array' => 'Polje :attribute ne sme da sadrži više od :value stavki.',
        'file' => 'Polje :attribute mora biti manje od ili jednako :value kilobajta.',
        'numeric' => 'Polje :attribute mora biti manje ili jednako sa :value.',
        'string' => 'Polje :attribute mora da sadrži manje ili jednako :value karaktera.',
    ],
    'mac_address' => 'Polje :attribute mora biti ispravna MAC adresa.',
    'max' => [
        'array' => 'Polje :attribute ne sme da sadrži više od :max stavki.',
        'file' => 'Polje :attribute ne sme biti veće od :max kilobajta.',
        'numeric' => 'Polje :attribute ne sme biti veće od :max.',
        'string' => 'Polje :attribute ne sme da sadrži više od :max karaktera.',
    ],
    'max_digits' => 'Polje :attribute ne sme da sadrži više od :max cifara.',
    'mimes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'mimetypes' => 'Polje :attribute mora biti datoteka tipa: :values.',
    'min' => [
        'array' => 'Polje :attribute mora da sdarži najmanje :min stavki.',
        'file' => 'Polje :attribute mora biti najmanje :min kilobajta.',
        'numeric' => 'Polje :attribute mora biti najmanje :min.',
        'string' => 'Polje :attribute mora da sadrži najmanje :min karaktera.',
    ],
    'min_digits' => 'Polje :attribute mora da sadrži najmanje :min cifara.',
    'missing' => 'Polje :attribute mora biti nedostajuće.',
    'missing_if' => 'Polje :attribute mora biti nedostajuće kada :other sadrži :value.',
    'missing_unless' => 'Polje :attribute mora biti nedostajuće osim ako :other sadrži :value.',
    'missing_with' => 'Polje :attribute mora biti nedostajuće kada je :values izabrano.',
    'missing_with_all' => 'Polje :attribute mora biti nedostajuće kada je :values izabrano.',
    'multiple_of' => 'Polje :attribute mora biti proizvod množenja sa :value.',
    'not_in' => 'Odabrani :attribute nije ispravan.',
    'not_regex' => 'Format polja :attribute nije ispravan.',
    'numeric' => 'Polje :attribute mora biti broj.',
    'password' => [
        'letters' => 'Polje :attribute mora da sadrži najmanje jedno slovo.',
        'mixed' => 'Polje :attribute mora da sadrži najmanje jedno veliko i jedno malo slovo.',
        'numbers' => 'Polje :attribute mora da sadrži najmanje jedan broj.',
        'symbols' => 'Polje :attribute mora da sadrži najmanje jedan simbol.',
        'uncompromised' => 'Uneto :attribute se pojavilo među procurelim podacima. Molim vas unesite drugo :attribute.',
    ],
    'percent'       => 'Minimum amortizacije mora biti između 0 i 100 kada je vrsta amortizacije procentna vrednost.',

    'present' => ':attribute polje mora biti prisutno.',
    'present_if' => 'Polje :attribute mora imati vrednost kada :other sadrži :value.',
    'present_unless' => 'Polje :attribute mora imati vrednost osim ako :other sadrži :value.',
    'present_with' => 'Polje :attribute mora imati vrednost kada je :values izabrano.',
    'present_with_all' => 'Polje :attribute mora imati vrednost kada je :values izabrano.',
    'prohibited' => 'Polje :attribute je zabranjeno.',
    'prohibited_if' => 'Polje :attribute je zabranjeno kada :other sadrži :value.',
    'prohibited_unless' => 'Polje :attribute je zabranjeno osim ako :other sadrži :values.',
    'prohibits' => 'Polje :attribute zabranjuje da :other bude izabrano.',
    'regex' => 'Format polja :attribute nije ispravan.',
    'required' => ':attribute polje je obavezno.',
    'required_array_keys' => 'Polje :attribute mora da sadrži unose za : :values.',
    'required_if' => ':attribute polje je obavezno kada je :other :value.',
    'required_if_accepted' => 'Polje :attribute je obavezno kada je :other prihvaćeno.',
    'required_if_declined' => 'Polje :attribute je neophodno kada je :other odbijeno.',
    'required_unless' => ':attribute polje je obavezno unless :other is in :values.',
    'required_with' => ':attribute polje je obavezno kada postoji :values.',
    'required_with_all' => 'Polje :attribute je neophodno kada je :values izabrano.',
    'required_without' => ':attribute polje je obavezno kada :values ne postoji.',
    'required_without_all' => ':attribute polje je obavezno ako nijedna od :values nije prisutna.',
    'same' => 'Polje :attribute se mora poklapati sa :other.',
    'size' => [
        'array' => 'Polje :attribute mora da sadrži :size stavki.',
        'file' => 'Polje :attribute mora biti :size kilobajta.',
        'numeric' => 'Polje :attribute mora biti :size.',
        'string' => 'Polje :attribute mora da sadrži :size karaktera.',
    ],
    'starts_with' => 'Polje :attribute mora da počne sa jednim od sledećih: :values.',
    'string'               => ':attribute mora biti :string.',
    'two_column_unique_undeleted' => ':attribute mora biti jedinstven kako u :table1 tako i u :table2. ',
    'unique_undeleted'     => ':attribute mora biti jedinstven.',
    'non_circular'         => ':attribute ne sme da kreira cirkularnu referencu.',
    'not_array'            => ':attribute ne može biti niz.',
    'disallow_same_pwd_as_user_fields' => 'Lozinka ne može biti ista kao korisničko ime.',
    'letters'              => 'Lozinka mora da sadrži barem jedno slovo.',
    'numbers'              => 'Lozinka mora da sadrži barem jednu cifru.',
    'case_diff'            => 'Lozinka mora da sadrži malo i veliko slovo.',
    'symbols'              => 'Lozinka mora da sadrži simbole.',
    'timezone' => 'Polje :attribute mora biti ispravna vremenska zona.',
    'unique' => ':attribute je već zauzet.',
    'uploaded' => ':attribute nije prenet.',
    'uppercase' => 'Polje :attribute mora da sadrži sve velika slova.',
    'url' => 'Polje :attribute mora biti ispravna URL adresa.',
    'ulid' => 'Polje :attribute mora biti ispravan ULID.',
    'uuid' => 'Polje :attribute mora biti ispravan UUID.',
    'fmcs_location' => 'Puna podrška za višestruko kompanija i opsezi lokacija je omogućeno u administratorskim podešavanjima, a izabrana lokacija i kompanija nisu kompatibilne.',


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

    'email_array'      => 'Jedna ili više adresa e-pošte nisu ispravne.',
    'checkboxes'           => ':attribute sadrži neispravne opcije.',
    'radio_buttons'        => ':attribute je neispravan.',
    
    'custom' => [
        'alpha_space' => ':attribute polje sadrži znak koji nije dozvoljen.',

        'hashed_pass'      => 'Vaša lozinka je neispravna',
        'dumbpwd'          => 'Lozinka nije sigurna.',
        'statuslabel_type' => 'Morate odabrati ispravnu vrstu oznake statusa',
        'custom_field_not_found'          => 'Izgleda da ovo polje ne postoji. Molim vas proverite imena vaših prilagođenih polja.',
        'custom_field_not_found_on_model' => 'Izgleda da ovo polje postoji, ali nije dostupno za grupu polja ovog modela imovine.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute mora biti ispravan datum u YYYY-MM-DD formatu',
        'last_audit_date.date_format'   =>  ':attribute mora biti ispravan datum u YYYY-MM-DD hh:mm:ss formatu',
        'expiration_date.date_format'   =>  ':attribute mora biti ispravan datum u YYYY-MM-DD formatu',
        'termination_date.date_format'  =>  ':attribute mora biti ispravan datum u YYYY-MM-DD formatu',
        'expected_checkin.date_format'  =>  ':attribute mora biti ispravan datum u YYYY-MM-DD formatu',
        'start_date.date_format'        =>  ':attribute mora biti ispravan datum u YYYY-MM-DD formatu',
        'end_date.date_format'          =>  ':attribute mora biti ispravan datum u YYYY-MM-DD formatu',
        'invalid_value_in_field' => 'Neispravna vrednost je sadržana u ovom polju',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (mala i velika slova) verovatno neće raditi. Trebalo bi da umesto toga koristite <code>samaccountname</code> (mala slova).'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> verovatno nije ispravan filter za autentifikaciju. Verovatno želite <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Ova vrednost verovatno ne bi trebalo da bude u zagradi.'],

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
        'invalid_value_in_field' => 'Neispravna vrednost je sadržana u ovom polju',
        'required' => 'Ovo polje je obavezno',
        'email' => 'Molim vas unesite ispravnu adresu e-pošte',
    ],


];
