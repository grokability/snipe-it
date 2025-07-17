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

    'accepted' => 'Je potřeba potvrdit :attribute.',
    'accepted_if' => 'Položka :attribute je vyžadována, když :other je :value.',
    'active_url' => ':attribute není platnou URL.',
    'after' => ':attribute nemůže být později než :date.',
    'after_or_equal' => 'Atribut musí mít datum následující nebo rovné :date.',
    'alpha' => ':attribute může obsahovat pouze písmena.',
    'alpha_dash' => ':attribute může obsahovat pouze písmena, čísla, a pomlčky.',
    'alpha_num' => ':attribute může obsahovat pouze písmena a čísla.',
    'array' => 'Hodnota pole :attribute musí být typu pole.',
    'ascii' => 'Pole :attribute musí obsahovat pouze jednobytové alfanumerické znaky a symboly.',
    'before' => 'Hodnota pole :attribute musí být před datem :date.',
    'before_or_equal' => 'Hodnota pole :attribute musí být před nebo rovno hodnotě :date.',
    'between' => [
        'array' => ':attribute musí být mezi hodnotami :min a :max.',
        'file' => ':attribute musí být větší než :min a menší než :max kilobytů.',
        'numeric' => 'Hodnota pole :attribute musí být mezi hodnotami :min a :max.',
        'string' => 'Hodnota pole :attribute musí mít mezi :min a :max znaky.',
    ],
    'valid_regex' => 'Regulární výraz je neplatný.',
    'boolean' => 'Hodnota pole :attribute musí být buď true, nebo false.',
    'can' => 'Pole :attribute obsahuje nepovolenou hodnotu.',
    'confirmed' => 'Potvrzení pole :attribute se neshoduje.',
    'contains' => 'V poli :attribute chybí požadovaná hodnota.',
    'current_password' => 'Zadané heslo není správné.',
    'date' => 'Hodnota pole :attribute musí být platné datum.',
    'date_equals' => 'Hodnota pole :attribute musí být datum rovné :date.',
    'date_format' => 'Hodnota pole :attribute musí odpovídat formátu :format.',
    'decimal' => 'Hodnota pole :attribute musí mít :decimal desetinných míst.',
    'declined' => 'Pole :attribute musí být odmítnuto.',
    'declined_if' => 'Pole :attribute musí být odmítnuto, pokud je hodnota pole :other rovna :value.',
    'different' => 'Pole :attribute a pole :other musí mít odlišné hodnoty.',
    'digits' => 'Pole :attribute musí obsahovat přesně :digits číslic.',
    'digits_between' => 'Pole :attribute musí obsahovat mezi :min a :max číslicemi.',
    'dimensions' => 'Pole :attribute obsahuje obrázek s neplatnými rozměry.',
    'distinct' => 'Pole atributu: atribut má duplicitní hodnotu.',
    'doesnt_end_with' => 'Hodnota pole :attribute nesmí končit jednou z následujících hodnot: :values.',
    'doesnt_start_with' => 'Hodnota pole :attribute nesmí začínat jednou z následujících hodnot: :values.',
    'email' => 'Pole :attribute musí obsahovat platnou e-mailovou adresu.',
    'ends_with' => 'Hodnota pole :attribute musí končit jednou z následujících hodnot: :values.',
    'enum' => 'Zvolený :attribute je neplatný.',
    'exists' => 'Zvolený :attribute je neplatný.',
    'extensions' => 'Pole :attribute musí mít jednu z následujících přípon: :values.',
    'file' => 'Pole :attribute musí být soubor.',
    'filled' => 'Pole atributu: musí mít hodnotu.',
    'gt' => [
        'array' => 'Pole :attribute musí obsahovat více než :value položek.',
        'file' => 'Velikost pole :attribute musí být větší než :value KB.',
        'numeric' => 'Hodnota pole :attribute musí být větší než :value.',
        'string' => 'Hodnota pole :attribute musí mít více než :value znaků.',
    ],
    'gte' => [
        'array' => 'Pole :attribute musí obsahovat :value nebo více položek.',
        'file' => 'Velikost pole :attribute musí být alespoň :value kilobajtů.',
        'numeric' => 'Hodnota pole :attribute musí být větší nebo rovna :value.',
        'string' => 'Hodnota pole :attribute musí mít alespoň :value znaků.',
    ],
    'hex_color' => 'Pole :attribute musí obsahovat platnou barvu ve formátu hexadecimálního kódu.',
    'image' => 'Pole :attribute musí být obrázek.',
    'import_field_empty'    => 'Hodnota pro :fieldname nemůže být null.',
    'in' => 'Zvolený :attribute je neplatný.',
    'in_array' => 'Hodnota pole :attribute musí existovat v poli :other.',
    'integer' => 'Pole :attribute musí být celé číslo.',
    'ip' => 'Pole :attribute musí obsahovat platnou IP adresu.',
    'ipv4' => 'Pole :attribute musí obsahovat platnou IPv4 adresu.',
    'ipv6' => 'Pole :attribute musí obsahovat platnou IPv6 adresu.',
    'json' => 'Pole :attribute musí obsahovat platný JSON řetězec.',
    'list' => 'Pole :attribute musí být seznam.',
    'lowercase' => 'Hodnota pole :attribute musí být malými písmeny.',
    'lt' => [
        'array' => 'Pole :attribute musí obsahovat méně než :value položek.',
        'file' => 'Velikost pole :attribute musí být menší než :value KB.',
        'numeric' => 'Hodnota pole :attribute musí být menší než :value.',
        'string' => 'Hodnota pole :attribute musí mít méně než :value znaků.',
    ],
    'lte' => [
        'array' => 'Pole :attribute nesmí obsahovat více než :value položek.',
        'file' => 'Velikost pole :attribute musí být menší nebo rovna :value KB.',
        'numeric' => 'Hodnota pole :attribute musí být menší nebo rovna :value.',
        'string' => 'Hodnota pole :attribute musí mít nejvýše :value znaků.',
    ],
    'mac_address' => 'Pole :attribute musí obsahovat platnou MAC adresu.',
    'max' => [
        'array' => 'Pole :attribute nesmí obsahovat více než :max položek.',
        'file' => 'Velikost pole :attribute nesmí být větší než :max kilobajtů.',
        'numeric' => 'Hodnota pole :attribute nesmí být větší než :max.',
        'string' => 'Hodnota pole :attribute nesmí být delší než :max znaků.',
    ],
    'max_digits' => 'Pole :attribute nesmí obsahovat více než :max číslic.',
    'mimes' => 'Pole :attribute musí být soubor typu: :values.',
    'mimetypes' => 'Pole :attribute musí být soubor typu: :values.',
    'min' => [
        'array' => 'Pole :attribute musí obsahovat alespoň :min položek.',
        'file' => 'Velikost pole :attribute musí být minimálně :min KB.',
        'numeric' => 'Hodnota pole :attribute musí být alespoň :min.',
        'string' => 'Hodnota pole :attribute musí mít alespoň :min znaků.',
    ],
    'min_digits' => 'Pole :attribute musí obsahovat alespoň :min číslic.',
    'missing' => 'Pole :attribute musí být prázdné.',
    'missing_if' => 'Pole :attribute musí být prázdné, pokud je :other nastaveno na :value.',
    'missing_unless' => 'Pole :attribute musí být prázdné, pokud :other není nastaveno na :value.',
    'missing_with' => 'Pole :attribute musí být prázdné, pokud je přítomna hodnota :values.',
    'missing_with_all' => 'Pole :attribute musí být prázdné, pokud jsou přítomny hodnoty :values.',
    'multiple_of' => 'Pole :attribute musí být násobkem hodnoty :value.',
    'not_in' => 'Zvolený :attribute je neplatný.',
    'not_regex' => 'Formát pole :attribute je neplatný.',
    'numeric' => 'Pole :attribute musí být číslo.',
    'password' => [
        'letters' => 'Pole :attribute musí obsahovat alespoň jedno písmeno.',
        'mixed' => 'Pole :attribute musí obsahovat alespoň jedno velké a jedno malé písmeno.',
        'numbers' => 'Pole :attribute musí obsahovat alespoň jedno číslo.',
        'symbols' => 'Pole :attribute musí obsahovat alespoň jeden symbol.',
        'uncompromised' => 'Zadané :attribute bylo nalezeno v úniku dat. Vyberte prosím jiné :attribute.',
    ],
    'percent'       => 'Minimální hodnota odpisu musí být mezi 0 a 100, pokud je typ odpisu procentuální.',

    'present' => 'Pole atributu musí být přítomno.',
    'present_if' => 'Pole :attribute musí být vyplněné, pokud je :other nastaveno na :value.',
    'present_unless' => 'Pole :attribute musí být vyplněné, pokud :other není nastaveno na :value.',
    'present_with' => 'Pole :attribute musí být vyplněné, pokud je přítomna hodnota :values.',
    'present_with_all' => 'Pole :attribute musí být vyplněné, pokud jsou přítomny hodnoty :values.',
    'prohibited' => 'Pole :attribute je zakázané.',
    'prohibited_if' => 'Pole :attribute je zakázané, pokud je :other nastaveno na :value.',
    'prohibited_unless' => 'Pole :attribute je zakázané, pokud :other není mezi hodnotami :values.',
    'prohibits' => 'Pole :attribute zakazuje přítomnost :other.',
    'regex' => 'Formát pole :attribute je neplatný.',
    'required' => 'Pole :attribute je požadováno.',
    'required_array_keys' => 'Pole :attribute musí obsahovat položky pro :values.',
    'required_if' => 'Položka :attribute je vyžadována, když :other je :value.',
    'required_if_accepted' => 'Pole :attribute je povinné, pokud je :other schváleno.',
    'required_if_declined' => 'Pole :attribute je povinné, pokud je :other zamítnuto.',
    'required_unless' => 'Pole atributu: je povinné, pokud: jiný není v: hodnoty.',
    'required_with' => 'Hodnota :attribute je vyžadována, když je přítomno :values.',
    'required_with_all' => 'Pole :attribute je povinné, pokud jsou přítomny hodnoty :values.',
    'required_without' => 'Hodnota :attribute je vyžadována, když není přítomno :values.',
    'required_without_all' => 'Pole atributu: je vyžadováno, pokud nejsou žádné hodnoty: existují.',
    'same' => 'Pole :attribute musí odpovídat poli :other.',
    'size' => [
        'array' => 'Pole :attribute musí obsahovat :size položek.',
        'file' => 'Pole :attribute musí mít velikost :size kilobajtů.',
        'numeric' => 'Pole :attribute musí mít velikost :size.',
        'string' => 'Pole :attribute musí mít :size znaků.',
    ],
    'starts_with' => 'Pole :attribute musí začínat jedním z následujících: :values.',
    'string'               => 'Atribut: musí být řetězec.',
    'two_column_unique_undeleted' => ':attribute musí být unikátní napříč :table1 a :table2. ',
    'unique_undeleted'     => 'Je třeba, aby se :attribute neopakoval.',
    'non_circular'         => ':attribute nesmí vytvořit kruhový odkaz.',
    'not_array'            => ':attribute nemůže být pole.',
    'disallow_same_pwd_as_user_fields' => 'Heslo nemůže být stejné jako uživatelské jméno.',
    'letters'              => 'Heslo musí obsahovat nejméně jedno písmeno.',
    'numbers'              => 'Heslo musí obsahovat alespoň jednu číslici.',
    'case_diff'            => 'Heslo musí použít smíšené znaky.',
    'symbols'              => 'Heslo musí obsahovat symboly.',
    'timezone' => 'Pole :attribute musí být platná časová zóna.',
    'unique' => ':attribute byl již vybrán.',
    'uploaded' => 'Atribut: se nepodařilo nahrát.',
    'uppercase' => 'Pole :attribute musí být psáno velkými písmeny.',
    'url' => ':attribute není platnou URL.',
    'ulid' => 'Pole :attribute musí být platný ULID.',
    'uuid' => 'Pole :attribute musí být platný UUID.',
    'fmcs_location' => 'V administračním nastavení je zapnutá podpora více společností a rozsah lokalit, ale vybraná lokalita a společnost nejsou kompatibilní.',


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

    'email_array'      => 'Jedna nebo více e-mailových adres je neplatná.',
    'checkboxes'           => ':attribute obsahuje neplatné možnosti.',
    'radio_buttons'        => ':attribute je neplatný.',
    
    'custom' => [
        'alpha_space' => 'Pole atributu: atribut obsahuje znak, který není povolen.',

        'hashed_pass'      => 'Vaše současné heslo je nesprávné',
        'dumbpwd'          => 'Toto heslo je příliš běžné.',
        'statuslabel_type' => 'Musíte vybrat platný typ štítku stavu',
        'custom_field_not_found'          => 'Vypadá to, že toto pole neexistuje, zkontrolujte prosím své vlastní názvy polí.',
        'custom_field_not_found_on_model' => 'Zdá se, že toto pole existuje, ale není dostupné na tomto poli Modelového modelu.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute musí být platné datum ve formátu RRRR-MM-DD',
        'last_audit_date.date_format'   =>  ':attribute musí být platné datum ve formátu RRRR-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  ':attribute musí být platné datum ve formátu RRRR-MM-DD',
        'termination_date.date_format'  =>  ':attribute musí být platné datum ve formátu RRRR-MM-DD',
        'expected_checkin.date_format'  =>  ':attribute musí být platné datum ve formátu RRRR-MM-DD',
        'start_date.date_format'        =>  ':attribute musí být platné datum ve formátu RRRR-MM-DD',
        'end_date.date_format'          =>  ':attribute musí být platné datum ve formátu RRRR-MM-DD',
        'invalid_value_in_field' => 'Neplatná hodnota zahrnutá v tomto poli',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (smíšená velikost písmen) pravděpodobně nebude fungovat. Místo toho bys měl použít <code>samaccountname</code> (malá písmena).'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> pravděpodobně není platný autentizační filtr. Pravděpodobně chceš <code>uid=</code>'],
        'ldap_filter' => ['regex' => 'Tato hodnota pravděpodobně nemá být uzavřena v závorkách.'],

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
        'invalid_value_in_field' => 'Neplatná hodnota zahrnutá v tomto poli',
        'required' => 'Toto pole je povinné',
        'email' => 'Prosím zadej platnou emailovou adresu',
    ],


];
