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

    'accepted' => ':attribute veld moet geaccepteerd worden.',
    'accepted_if' => ':attribute veld moet geaccepteerd worden als :other :value is.',
    'active_url' => ':attribute veld moet een geldige URL zijn.',
    'after' => ':attribute veld moet een datum na :date zijn.',
    'after_or_equal' => ':attribute veld moet een datum na of gelijk aan :date zijn.',
    'alpha' => ':attribute veld mag alleen letters bevatten.',
    'alpha_dash' => ':attribute veld mag alleen letters, cijfers, streepjes en onderstrepingstekens bevatten.',
    'alpha_num' => ':attribute veld mag alleen letters en cijfers bevatten.',
    'array' => ':attribute moet een array zijn.',
    'ascii' => ':attribute veld mag alleen alfanumerieke tekens en symbolen bevatten.',
    'before' => ':attribute veld moet een datum voor :date zijn.',
    'before_or_equal' => ':attribute veld moet een datum voor of gelijk aan :date zijn.',
    'between' => [
        'array' => ':attribute veld moet tussen :min en :max items bevatten.',
        'file' => ':attribute veld moet tussen :min en :max kilobytes zijn.',
        'numeric' => ':attribute veld moet tussen de :min en de :max liggen.',
        'string' => ':attribute veld moet tussen :min en :max karakters lang zijn.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => ':attribute moet waar of onwaar zijn.',
    'can' => ':attribute veld bevat een niet-geautoriseerde waarde.',
    'confirmed' => ':attribute veld bevestiging komt niet overeen.',
    'contains' => ':attribute veld mist een verplichte waarde.',
    'current_password' => 'Het wachtwoord is onjuist.',
    'date' => ':attribute veld moet een geldige datum zijn.',
    'date_equals' => ':attribute moet een datum gelijk zijn aan :date.',
    'date_format' => ':attribute veld moet overeenkomen met het formaat :format.',
    'decimal' => ':attribute veld moet :decimale decimale plaatsen hebben.',
    'declined' => ':attribute veld moet worden geweigerd.',
    'declined_if' => ':attribute veld moet afgewezen worden als :other :value is.',
    'different' => ':attribute veld en :other mag niet hetzelfde zijn.',
    'digits' => ':attribute veld moet uit :digits cijfers bestaan.',
    'digits_between' => ':attribute veld moet tussen de :min en :max cijfers zijn.',
    'dimensions' => ':attribute veld heeft geen geldige afmetingen voor afbeeldingen.',
    'distinct' => ':attribute veld heeft een duplicaat waarde.',
    'doesnt_end_with' => ':attribute veld mag niet eindigen met één van de volgende: :values.',
    'doesnt_start_with' => ':attribute veld mag niet beginnen met één van de volgende: :values.',
    'email' => ':attribute veld moet een geldig e-mail adres zijn.',
    'ends_with' => ':attribute veld moet eindigen met één van de volgende: :values.',
    'enum' => 'Het geselecteerde kenmerk :attribute is ongeldig.',
    'exists' => 'Het geselecteerde kenmerk :attribute is ongeldig.',
    'extensions' => ':attribute veld moet een van de volgende extensies hebben: :values.',
    'file' => ':attribute veld moet een bestand zijn.',
    'filled' => ':attribute veld moet een waarde hebben.',
    'gt' => [
        'array' => 'Het :attribute veld moet meer dan :value items bevatten.',
        'file' => 'Het :attribute veld moet groter zijn dan :value kilobytes.',
        'numeric' => ':attribute veld moet groter zijn dan :value.',
        'string' => 'Het veld :attribute moet meer dan :value karakters bevatten.',
    ],
    'gte' => [
        'array' => 'Het :attribute veld moet :value of meer bevatten.',
        'file' => 'Het veld :attribute moet groter of gelijk zijn aan :value kilobytes.',
        'numeric' => ':attribute veld moet groter of gelijk zijn aan :value.',
        'string' => 'Het veld :attribute moet :value of groter zijn.',
    ],
    'hex_color' => ':attribute veld moet een geldige hexadecimale kleur hebben.',
    'image' => ':attribute veld moet een afbeelding zijn.',
    'import_field_empty'    => 'De waarde voor :fieldname kan niet leeg zijn.',
    'in' => 'Het geselecteerde kenmerk :attribute is ongeldig.',
    'in_array' => ':attribute veld moet bestaan in :other.',
    'integer' => ':attribute veld moet een geheel getal zijn.',
    'ip' => ':attribute veld moet een geldig IP-adres zijn.',
    'ipv4' => ':attribute veld moet een geldig IPv4-adres zijn.',
    'ipv6' => ':attribute veld moet een geldig IPv6-adres zijn.',
    'json' => ':attribute veld moet een geldige JSON string zijn.',
    'list' => ':attribute moet een lijst zijn.',
    'lowercase' => ':attribute veld moet een kleine letters zijn.',
    'lt' => [
        'array' => 'Het :attribute veld moet minder dan :value items bevatten.',
        'file' => 'Het :attribute veld moet kleiner zijn dan :value kilobytes.',
        'numeric' => ':attribute veld moet kleiner zijn dan :value.',
        'string' => ':attribute veld moet minder dan :value karakters bevatten.',
    ],
    'lte' => [
        'array' => 'Het :attribute veld mag niet meer dan :value items bevatten.',
        'file' => 'Het veld :attribute moet kleiner of gelijk zijn aan :value kilobytes.',
        'numeric' => ':attribute veld moet kleiner of gelijk zijn aan :value.',
        'string' => 'Het veld :attribute moet minder of gelijk zijn aan :value tekens.',
    ],
    'mac_address' => ':attribute veld moet een geldig MAC-adres zijn.',
    'max' => [
        'array' => ':attribute veld mag niet meer dan :max items bevatten.',
        'file' => ':attribute veld mag niet groter zijn dan :max kilobytes.',
        'numeric' => ':attribute veld mag niet groter zijn dan :max.',
        'string' => ':attribute veld mag niet groter zijn dan :max tekens.',
    ],
    'max_digits' => ':attribute veld mag niet meer dan :max cijfers bevatten.',
    'mimes' => ':attribute veld moet een bestand zijn van het type: :values.',
    'mimetypes' => ':attribute veld moet een bestand zijn van het type: :values.',
    'min' => [
        'array' => ':attribute veld moet minstens :min items bevatten.',
        'file' => ':attribute veld moet minstens :min kilobytes zijn.',
        'numeric' => ':attribute veld moet minstens :min zijn.',
        'string' => ':attribute veld moet minstens :min tekens bevatten.',
    ],
    'min_digits' => ':attribute veld moet minstens :min cijfers bevatten.',
    'missing' => ':attribute moet ontbreken.',
    'missing_if' => ':attribute veld moet ontbreken wanneer :other :value is.',
    'missing_unless' => ':attribute veld moet ontbreken, tenzij :other gelijk is aan :value.',
    'missing_with' => ':attribute veld moet ontbreken als :values aanwezig is.',
    'missing_with_all' => ':attribute veld moet ontbreken wanneer :values aanwezig zijn.',
    'multiple_of' => 'Het :attribute veld moet een veelvoud van :value zijn.',
    'not_in' => 'Het geselecteerde kenmerk :attribute is ongeldig.',
    'not_regex' => ':attribute veld formaat is ongeldig.',
    'numeric' => ':attribute veld moet een getal zijn.',
    'password' => [
        'letters' => ':attribute moet minstens één letter bevatten.',
        'mixed' => ':attribute veld moet minstens één hoofdletter en één kleine letter bevatten.',
        'numbers' => ':attribute veld moet minstens één cijfer bevatten.',
        'symbols' => ':attribute veld moet minstens één teken bevatten.',
        'uncompromised' => 'Het gegeven :attribute is weergegeven in een gegevenslek. Kies een ander :attribuut.',
    ],
    'percent'       => 'Het afschrijvingsminimum moet tussen 0 en 100 liggen wanneer het afschrijvingstype procentueel is.',

    'present' => ':attribute veld moet aanwezig zijn.',
    'present_if' => ':attribute veld moet aanwezig zijn als :other :value is.',
    'present_unless' => ':attribute veld moet aanwezig zijn tenzij :other gelijk is aan :value.',
    'present_with' => ':attribute veld moet aanwezig zijn als :values aanwezig is.',
    'present_with_all' => ':attribute veld moet aanwezig zijn wanneer :values aanwezig zijn.',
    'prohibited' => ':attribute veld is verboden.',
    'prohibited_if' => ':attribute veld is verboden wanneer :other :value is.',
    'prohibited_unless' => ':attribute veld is verboden tenzij :other gelijk is aan :values.',
    'prohibits' => ':attribute veld verbiedt :other van aanwezig te zijn.',
    'regex' => ':attribute veld formaat is ongeldig.',
    'required' => 'Het veld :attribute is verplicht.',
    'required_array_keys' => ':attribute veld moet items bevatten voor: :values.',
    'required_if' => 'het veld :attribute is verplicht als :other gelijk is aan :value.',
    'required_if_accepted' => ':attribute veld is verplicht als :other wordt geaccepteerd.',
    'required_if_declined' => ':attribute veld is verplicht als :other wordt geweigerd.',
    'required_unless' => ':attribute veld is vereist tenzij :other is in :values.',
    'required_with' => 'Het veld :attribute is verplicht als :values ingesteld staan.',
    'required_with_all' => ':attribute veld is verplicht wanneer :values aanwezig zijn.',
    'required_without' => 'Het veld :attribute is verplicht als :values niet ingesteld staan.',
    'required_without_all' => ':attribute veld is vereist wanneer geen van :values aanwezig zijn.',
    'same' => ':attribute veld moet overeenkomen met :other.',
    'size' => [
        'array' => ':attribute veld moet :size items bevatten.',
        'file' => ':attribute veld moet :size kilobytes zijn.',
        'numeric' => ':attribute veld moet :size zijn.',
        'string' => ':attribute veld moet :size karakters bevatten.',
    ],
    'starts_with' => ':attribute veld moet beginnen met één van de volgende: :values.',
    'string'               => ':attribute moet een string zijn.',
    'two_column_unique_undeleted' => ':attribute moet uniek zijn in :table1 en :table2. ',
    'unique_undeleted'     => 'De :attribute moet uniek zijn. ',
    'non_circular'         => ':attribute mag geen circulaire referentie aanmaken.',
    'not_array'            => ':attribute kan geen array zijn.',
    'disallow_same_pwd_as_user_fields' => 'Wachtwoord kan niet hetzelfde zijn als de gebruikersnaam.',
    'letters'              => 'Wachtwoord moet ten minste één letter bevatten.',
    'numbers'              => 'Wachtwoord moet ten minste één cijfer bevatten.',
    'case_diff'            => 'Wachtwoord moet kleine letters en hoofdletters bevatten.',
    'symbols'              => 'Wachtwoord moet symbolen bevatten.',
    'timezone' => ':attribute moet een geldige tijdzone zijn.',
    'unique' => 'Het veld :attribute is reeds in gebruik.',
    'uploaded' => 'Uploaden van :attribute is mislukt.',
    'uppercase' => ':attribute veld moet met hoofdletter zijn.',
    'url' => ':attribute veld moet een geldige URL zijn.',
    'ulid' => ':attribute veld moet een geldige ULID zijn.',
    'uuid' => ':attribute veld moet een geldige UUID zijn.',
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

    'email_array'      => 'één of meer e-mail adressen kloppen niet.',
    'checkboxes'           => ':attribute bevat ongeldige opties.',
    'radio_buttons'        => ':attribute is ongeldig.',
    
    'custom' => [
        'alpha_space' => ':attribute veld bevat een karakter wat niet is toegestaan.',

        'hashed_pass'      => 'Je huidige wachtwoord is incorrect',
        'dumbpwd'          => 'Dat wachtwoord is te veelvoorkomend.',
        'statuslabel_type' => 'Selecteer een valide status label',
        'custom_field_not_found'          => 'Dit veld lijkt niet te bestaan, controleer uw aangepaste veldnamen.',
        'custom_field_not_found_on_model' => 'Dit veld lijkt te bestaan, maar is niet beschikbaar in de veldset van dit Asset Model.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute moet een geldige datum zijn in JJJJ-MM-DD formaat',
        'last_audit_date.date_format'   =>  ':attribute moet een geldige datum zijn in JJJJ-MM-DD uu:mm:ss formaat',
        'expiration_date.date_format'   =>  ':attribute moet een geldige datum zijn in JJJJ-MM-DD formaat',
        'termination_date.date_format'  =>  ':attribute moet een geldige datum zijn in JJJJ-MM-DD formaat',
        'expected_checkin.date_format'  =>  ':attribute moet een geldige datum zijn in JJJJ-MM-DD formaat',
        'start_date.date_format'        =>  ':attribute moet een geldige datum zijn in JJJJ-MM-DD formaat',
        'end_date.date_format'          =>  ':attribute moet een geldige datum zijn in JJJJ-MM-DD formaat',
        'invalid_value_in_field' => 'Ongeldige waarde ingevoerd in dit veld',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (Hoofd- en kleine letters) zal waarschijnlijk niet werken. Je zou in plaats hiervan <code>samaccountname</code> (kleine letters) kunnen gebruiken.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> is waarschijnlijk geen geldig authenticatiefilter. Je wilt waarschijnlijk <code>uid=</code> gebruiken.'],
        'ldap_filter' => ['regex' => 'Deze waarde hoeft waarschijnlijk niet verpakt te worden in haakjes.'],

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
        'invalid_value_in_field' => 'Ongeldige waarde ingevoerd in dit veld',
        'required' => 'Dit veld is verplicht',
        'email' => 'Vul een geldig e-mailadres in',
    ],


];
