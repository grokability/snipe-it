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

    'accepted' => ':attribute-fältet måste accepteras.',
    'accepted_if' => ':attribute-fältet måste accepteras när :other är :value.',
    'active_url' => ':attribute-fältet måste vara en giltig URL.',
    'after' => ':attribute-fältet måste vara ett datum efter :date.',
    'after_or_equal' => ':attribute-fältet-fältet måste vara ett datum efter eller samma som :date.',
    'alpha' => ':attribute-fältet får endast innehålla bokstäver.',
    'alpha_dash' => ':attribute-fältet får endast innehålla bokstäver, siffror, bindestreck eller understreck.',
    'alpha_num' => ':attribute-fältet får endast innehålla bokstäver och siffror.',
    'array' => ':attribute-fältet måste vara en array.',
    'ascii' => ':attribute-fältet får endast innehålla en single-byte alfanumeriska tecken och symboler.',
    'before' => ':attribute-fältet måste vara ett datum före :date.',
    'before_or_equal' => ':attribute-fältet måste vara ett datum före eller samma som :date.',
    'between' => [
        'array' => ':attribute-fältet måste ha mellan :min och :max objekt.',
        'file' => ':attribute-fältet måste vara mellan :min och :max kilobyte.',
        'numeric' => ':attribute-fältet måste vara mellan :min och :max.',
        'string' => ':attribute-fältet måste vara mellan :min och :max tecken.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Attributfältet måste vara sant eller felaktigt.',
    'can' => ':attribute-fältet innehåller ett otillåtet värde.',
    'confirmed' => ':attribute-fältets bekräftelse matchar inte.',
    'contains' => ':attribute-fältet saknar ett obligatoriskt värde.',
    'current_password' => 'Lösenordet är felaktigt.',
    'date' => ':attribute-fältet måste ha ett giltigt datum.',
    'date_equals' => ':attribute-fältets datum måste vara samma som :date.',
    'date_format' => ':attribute-fältet måste matcha formatet :format.',
    'decimal' => ':attribute-fältet måste ha :decimal decimaltecken.',
    'declined' => ':attribute-fältet måste nekas.',
    'declined_if' => ':attribute-fältet måste nekas när :other är :value.',
    'different' => ':attribute-fältet och :annat måste vara unika.',
    'digits' => ':attribute-fältet måste vara :digits siffror.',
    'digits_between' => ':attribute-fältet måste vara mellan :min och :max siffror.',
    'dimensions' => ':attribute-fältet has inkorrekta bilddimensioner.',
    'distinct' => ':attribute fältet har samma värde.',
    'doesnt_end_with' => ':attribute-fältet får inte sluta med en av följande värden: :values.',
    'doesnt_start_with' => ':attribute-fältet får inte börja med en av följande värden: :values.',
    'email' => ':attribute-fältet måste vara en giltig e-postadress.',
    'ends_with' => ':attribute-fältet måste sluta med en av följande värden: :values.',
    'enum' => 'Det valda :attribute är ogiltigt.',
    'exists' => 'Det valda :attribute är ogiltigt.',
    'extensions' => ':attribute-fältet måste ha någon av följande tillval: :values.',
    'file' => ':attribute-fältet måste vara en fil.',
    'filled' => ':attribute fältet måste ha ett värde.',
    'gt' => [
        'array' => ':attribute-fältet måste ha mer än :value objekt.',
        'file' => ':attribute-fältet måste vara större än :value kilobyte.',
        'numeric' => ':attribute-fältet måste vara större än :value.',
        'string' => ':attribute-fältet måste vara större än :value tecken.',
    ],
    'gte' => [
        'array' => ':attribute-fältet måste ha :value objekt eller fler.',
        'file' => ':attribute-fältet måste vara större än eller lika med :value kilobyte.',
        'numeric' => ':attribute-fältet måste vara större än eller lika med :value.',
        'string' => ':attribute-fältet måste vara större än eller lika med :value tecken.',
    ],
    'hex_color' => ':attribute-fältet måste vara en giltig hexadecimalfärg.',
    'image' => ':attribute-fältet måste vara en bild.',
    'import_field_empty'    => 'Värdet för :fieldname kan inte vara noll.',
    'in' => 'Det valda :attribute är ogiltigt.',
    'in_array' => ':attribute-fältet måste existera i :other.',
    'integer' => ':attribute-fältet måste vara en integer.',
    'ip' => ':attribute-fältet måste vara en giltig IP-adress.',
    'ipv4' => ':attribute-fältet måste vara en giltig IPv4-adress.',
    'ipv6' => ':attribute-fältet måste vara en giltig IPv6-adress.',
    'json' => ':attribute-fältet måste vara en giltig JSON-sträng.',
    'list' => ':attribute-fältet måste vara en lista.',
    'lowercase' => ':attribute-fältet måste skrivas med gemener.',
    'lt' => [
        'array' => ':attribute-fältet måste ha färre än :value objekt.',
        'file' => ':attribute-fältet måste vara mindre än :value kilobyte.',
        'numeric' => ':attribute-fältet måste vara mindre än :value.',
        'string' => ':attribute-fältet måste vara färre än :value tecken.',
    ],
    'lte' => [
        'array' => ':attribute-fältet får inte ha fler än :value objekt.',
        'file' => ':attribute-fältet måste vara mindre än eller lika med :value kilobyte.',
        'numeric' => ':attribute-fältet måste vara mindre än eller lika med :value.',
        'string' => ':attribute-fältet måste vara färre än eller lika med :value tecken.',
    ],
    'mac_address' => ':attribute-fältet måste vara en giltig MAC-adress.',
    'max' => [
        'array' => ':attribute-fältet får inte ha fler än :max objekt.',
        'file' => ':attribute-fältet får inte vara större än :max kilobyte.',
        'numeric' => ':attribute-fältet får inte vara större än :max.',
        'string' => ':attribute-fältet får inte vara fler än :max tecken.',
    ],
    'max_digits' => ':attribute-fältet får inte ha fler än :max siffror.',
    'mimes' => ':attribute-fältet måste vara av filtyp: :values.',
    'mimetypes' => ':attribute-fältet måste vara av filtyp: :values.',
    'min' => [
        'array' => ':attribute-fältet måste ha färre än :min objekt.',
        'file' => ':attribute-fältet måste vara minst :min kilobyte.',
        'numeric' => ':attribute-fältet måste vara minst :min.',
        'string' => ':attribute-fältet måste innehålla minst :min tecken.',
    ],
    'min_digits' => ':attribute-fältet måste vara minst :min siffror.',
    'missing' => ':attribute-fältet måste vara tom.',
    'missing_if' => ':attribute-fältet måste vara tom när :other är :value.',
    'missing_unless' => ':attribute-fältet måste vara tom om inte :other är :value.',
    'missing_with' => ':attribute-fältet måste vara tom när :values finns.',
    'missing_with_all' => ':attribute-fältet måste vara tom när :values finns.',
    'multiple_of' => ':attribute-fältet måste vara multipel av :value.',
    'not_in' => 'Det valda :attribute är ogiltigt.',
    'not_regex' => ':attribute-fältets format är ogiltigt.',
    'numeric' => ':attribute-fältet måste vara ett nummer.',
    'password' => [
        'letters' => ':attribute-fältet måste innehålla minst en bokstav.',
        'mixed' => ':attribute-fältet måste innehåller minst en stor och en liten bokstav.',
        'numbers' => ':attribute-fältet måste innehålla minst ett nummer.',
        'symbols' => ':attribute-fältet måste innehåll minst en symbol.',
        'uncompromised' => ':attribute-fältet har upptäckts i en dataläcka. Vänligen ange ett annat :attribute.',
    ],
    'percent'       => 'Värdeminskningsminimum måste vara mellan 0 och 100 när värdeminskningstypen är i procent.',

    'present' => ':attribute fältet måste finnas.',
    'present_if' => ':attribute-fältet måste vara ifyllt om :other är :value.',
    'present_unless' => ':attribute-fältet måste vara ifyllt så länge inte :other är :value.',
    'present_with' => ':attribute-fältet måste vara ifyllt om :values är angivet.',
    'present_with_all' => ':attribute-fältet måste vara ifyllt om :values är angivna.',
    'prohibited' => ':attribute-fältet är förbjudet.',
    'prohibited_if' => ':attribute-fältet är förbjudet när :other är :value.',
    'prohibited_unless' => ':attribute-fältet är förbjudet om inte :other finns i :values.',
    'prohibits' => ':attribute-fältet förbjuder :other från att vara angivet.',
    'regex' => ':attribute-fältets format är ogiltigt.',
    'required' => 'Fältet: :attribute är obligatoriskt.',
    'required_array_keys' => ':attribute-fältet måste innehålla värden för: :values.',
    'required_if' => 'Fältet :attribute krävs när :other är :value.',
    'required_if_accepted' => ':attribute-fältet är obligatoriskt när :other är accepterat.',
    'required_if_declined' => ':attribute-fältet är obligatoriskt om :other är nekat.',
    'required_unless' => 'Fältet :attribute krävs om inte :other anges i :values.',
    'required_with' => 'Fältet :attribute krävs när :values angivits.',
    'required_with_all' => ':attribute-fältet är obligatoriskt när :values är angivet.',
    'required_without' => 'Fältet :attribute krävs när :values saknas.',
    'required_without_all' => 'Fältet :attribute krävs när inga :values har angetts.',
    'same' => ':attribute-fältet måste matcha :other.',
    'size' => [
        'array' => ':attribute-fältet måste innehålla :size objekt.',
        'file' => ':attribute-fältet måste vara :size kilobyte.',
        'numeric' => ':attribute-fältet måste vara :size.',
        'string' => ':attribute-fältet måste vara :size tecken.',
    ],
    'starts_with' => ':attribute-fältet måste starta med minst ett av följande: :values.',
    'string'               => ':attribute måste vara en sträng.',
    'two_column_unique_undeleted' => ':attribute måste vara unikt i :table1 och :table2. ',
    'unique_undeleted'     => ':attribute måste vara unikt.',
    'non_circular'         => ':attribute får inte skapa en cirkulär referens.',
    'not_array'            => ':attribute kan inte vara en array.',
    'disallow_same_pwd_as_user_fields' => 'Lösenordet kan inte vara samma som användarnamnet.',
    'letters'              => 'Lösenord måste innehålla minst en bokstav.',
    'numbers'              => 'Lösenord måste innehålla minst en siffra.',
    'case_diff'            => 'Lösenordet måste innehålla både versaler och gemener.',
    'symbols'              => 'Lösenordet måste innehålla symboler.',
    'timezone' => ':attribute-fältet måste vara en giltig tidszon.',
    'unique' => ':attribute är upptaget.',
    'uploaded' => 'Uppladdningen av :attribute misslyckades.',
    'uppercase' => ':attribute-fältet måste vara versaler.',
    'url' => ':attribute-fältet måste vara en giltig URL.',
    'ulid' => ':attribute-fältet måste vara ett giltigt ULID.',
    'uuid' => ':attribute-fältet måste vara ett giltigt UUID.',
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

    'email_array'      => 'En eller flera e-postadresser är ogiltiga.',
    'checkboxes'           => ':attribute innehåller ogiltiga alternativ.',
    'radio_buttons'        => ':attribute är ogiltigt.',
    
    'custom' => [
        'alpha_space' => 'Fältet :attribute innehåller ett tecken som inte är tillåtet.',

        'hashed_pass'      => 'Ditt nuvarande lösenord är felaktigt',
        'dumbpwd'          => 'Det angivna lösenordet är för vanligt.',
        'statuslabel_type' => 'Du måste ange en giltig statusetikett',
        'custom_field_not_found'          => 'Detta fält verkar inte existera, vänligen dubbelkolla dina anpassade fält.',
        'custom_field_not_found_on_model' => 'Detta fält verkar existera, men är inte tillgängligt på tillgångsmodellens fältuppsättning.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute måste vara ett giltigt datum i YYYY-MM-DD format',
        'last_audit_date.date_format'   =>  ':attribute måste vara ett giltigt datum i YYYY-MM-DD hh:mm:ss format',
        'expiration_date.date_format'   =>  ':attribute måste vara ett giltigt datum i YYYY-MM-DD format',
        'termination_date.date_format'  =>  ':attribute måste vara ett giltigt datum i YYYY-MM-DD format',
        'expected_checkin.date_format'  =>  ':attribute måste vara ett giltigt datum i YYYY-MM-DD format',
        'start_date.date_format'        =>  ':attribute måste vara ett giltigt datum i YYYY-MM-DD format',
        'end_date.date_format'          =>  ':attribute måste vara ett giltigt datum i YYYY-MM-DD format',
        'invalid_value_in_field' => 'Ogiltigt värde i detta fält',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (blandade gemener och versaler) kommer sannolikt inte att fungera. Du bör använda <code>samaccountname</code> (gemener) istället.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> är förmodligen inte ett giltigt aut-filter. Du vill förmodligen ha <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Detta värde bör sannolikt inte vara inom parantes.'],

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
        'invalid_value_in_field' => 'Ogiltigt värde i detta fält',
        'required' => 'Detta fält är obligatoriskt',
        'email' => 'Vänligen ange en giltig e-postadress',
    ],


];
