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

    'accepted' => 'Il campo :attribute deve essere accettato.',
    'accepted_if' => 'Il campo :attribute deve essere accettato quando :other è :value.',
    'active_url' => 'Il campo :attribute deve essere un URL valido.',
    'after' => 'Il campo :attribute deve essere una data successiva a :date.',
    'after_or_equal' => 'Il campo :attribute deve essere una data successiva o uguale a :date.',
    'alpha' => 'Il campo :attribute deve contenere solo lettere.',
    'alpha_dash' => 'Il campo :attribute deve contenere solo lettere, numeri, trattini o trattini bassi.',
    'alpha_num' => 'Il campo :attribute deve contenere solo lettere e numeri.',
    'array' => 'Il campo :attribute deve essere un array.',
    'ascii' => 'Il campo :attribute deve contenere solo caratteri alfanumerici e simboli a byte singolo.',
    'before' => 'Il campo :attribute deve essere una data precedente il :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale al :date.',
    'between' => [
        'array' => 'Il campo :attribute deve avere tra :min e :max elementi.',
        'file' => 'Il campo :attribute deve essere tra :min e :max kilobyte.',
        'numeric' => 'Il campo :attribute deve essere tra :min e :max.',
        'string' => 'Il campo :attribute deve essere lungo tra :min e :max caratteri.',
    ],
    'valid_regex' => 'L\'espressione regolare non è valida.',
    'boolean' => 'Il campo: attributo deve essere vero o falso.',
    'can' => 'Il campo :attribute contiene un valore non autorizzato.',
    'confirmed' => 'La conferma del campo :attribute non corrisponde.',
    'contains' => 'Al campo :attribute manca un valore richiesto.',
    'current_password' => 'La password non è corretta.',
    'date' => 'Il campo :attribute deve essere una data valida.',
    'date_equals' => 'Il campo :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il campo :attribute deve corrispondere al formato :format.',
    'decimal' => 'Il campo :attribute deve avere :decimal decimali.',
    'declined' => 'Il campo :attribute deve essere rifiutato.',
    'declined_if' => 'Il campo :attribute deve essere rifiutato quando :other è :value.',
    'different' => 'Il campo :attribute e :other devono essere diversi.',
    'digits' => 'Il campo :attribute deve essere :digits cifre.',
    'digits_between' => 'Il campo :attribute deve essere compreso tra :min e :max cifre.',
    'dimensions' => 'Il campo :attribute ha una dimensione dell\'immagine non valida.',
    'distinct' => 'Il campo :attribute ha un valore duplicato.',
    'doesnt_end_with' => 'Il campo :attribute non deve terminare con uno dei seguenti: :values.',
    'doesnt_start_with' => 'Il campo :attribute non deve iniziare con uno dei seguenti: :values.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'ends_with' => 'Il campo :attribute deve terminare con uno dei seguenti: :values.',
    'enum' => 'L\' :attribute selezionato è invalido.',
    'exists' => ':attribute selezionato non è valido.',
    'extensions' => 'Il campo :attribute deve avere una delle seguenti estensioni: :values.',
    'file' => 'Il campo :attribute deve essere un file.',
    'filled' => 'Il campo :attribute deve avere un valore.',
    'gt' => [
        'array' => 'Il campo :attribute deve avere più di :value elementi.',
        'file' => 'Il campo :attribute deve essere maggiore di :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere maggiore di :value.',
        'string' => 'Il campo :attribute deve essere maggiore di :value caratteri.',
    ],
    'gte' => [
        'array' => 'Il campo :attribute deve avere :value o più elementi.',
        'file' => 'Il campo :attribute deve essere maggiore o uguale a :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere maggiore o uguale a :value.',
        'string' => 'Il campo :attribute deve essere maggiore o uguale a :value caratteri.',
    ],
    'hex_color' => 'Il campo :attribute deve essere un colore esadecimale valido.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'import_field_empty'    => ':fieldname non può essere vuoto.',
    'in' => ':attribute selezionato non è valido.',
    'in_array' => 'Il campo :attribute deve esistere in :other.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'ip' => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il campo :attribute deve essere una stringa JSON valida.',
    'list' => 'Il campo :attribute deve essere una lista.',
    'lowercase' => 'Il campo :attribute deve essere minuscolo.',
    'lt' => [
        'array' => 'Il campo :attribute deve avere meno di :value elementi.',
        'file' => 'Il campo :attribute deve essere inferiore a :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere inferiore a :value.',
        'string' => 'Il campo :attribute deve essere inferiore a :value caratteri.',
    ],
    'lte' => [
        'array' => 'Il campo :attribute non deve avere più di :value elementi.',
        'file' => 'Il campo :attribute deve essere inferiore o uguale a :value kilobytes.',
        'numeric' => 'Il campo :attribute deve essere minore o uguale a :value.',
        'string' => 'Il campo :attribute deve essere inferiore o uguale a :value caratteri.',
    ],
    'mac_address' => 'Il campo :attribute deve essere un indirizzo MAC valido.',
    'max' => [
        'array' => 'Il campo :attribute non deve avere più di :max elementi.',
        'file' => 'Il campo :attribute non deve essere maggiore di :max kilobytes.',
        'numeric' => 'Il campo :attribute non deve essere maggiore di :max.',
        'string' => 'Il campo :attribute non deve avere più di :max caratteri.',
    ],
    'max_digits' => 'Il campo :attribute non deve avere più di :max cifre.',
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Il campo :attribute deve avere almeno :min elementi.',
        'file' => 'Il campo :attribute deve essere almeno :min kilobytes.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'string' => 'Il campo :attribute deve avere almeno :min caratteri.',
    ],
    'min_digits' => 'Il campo :attribute deve avere almeno :min cifre.',
    'missing' => 'Il campo :attribute deve mancare.',
    'missing_if' => 'Il campo :attribute deve mancare quando :other è :value.',
    'missing_unless' => 'Il campo :attribute deve essere mancante a meno che :other non sia :value.',
    'missing_with' => 'Il campo :attribute deve mancare quando :values è presente.',
    'missing_with_all' => 'Il campo :attribute deve mancare quando :values sono presenti.',
    'multiple_of' => 'Il campo :attribute deve essere multiplo di :value.',
    'not_in' => ':attribute selezionato non è valido.',
    'not_regex' => 'Il formato del campo :attribute non è valido.',
    'numeric' => 'Il campo :attribute deve essere un numero.',
    'password' => [
        'letters' => 'Il campo :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il campo :attribute deve contenere almeno una lettera maiuscola e una lettera minuscola.',
        'numbers' => 'Il campo :attribute deve contenere almeno un numero.',
        'symbols' => 'Il campo :attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'Il valore :attribute fornito è comparso in un data leak. Si prega di scegliere un :attribute differente.',
    ],
    'percent'       => 'Il deprezzamento minimo deve essere tra 0 e 100 quando il tipo di deprezzamento è Percentuale.',

    'present' => 'Il campo :attribute deve essere presente.',
    'present_if' => 'Il campo :attribute deve essere presente quando :other è :value.',
    'present_unless' => 'Il campo :attribute deve essere presente a meno che :other non sia :value.',
    'present_with' => 'Il campo :attribute deve essere presente quando :values è presente.',
    'present_with_all' => 'Il campo :attribute deve essere presente quando :values sono presenti.',
    'prohibited' => 'Il campo :attribute è vietato.',
    'prohibited_if' => 'Il campo :attribute è vietato quando :other è :value.',
    'prohibited_unless' => 'Il campo :attribute è vietato a meno che :other non sia in :values.',
    'prohibits' => 'Il campo :attribute inibisce la presenza di :other.',
    'regex' => 'Il formato del campo :attribute non è valido.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_array_keys' => 'Il campo :attribute deve contenere voci per: :values.',
    'required_if' => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_if_accepted' => 'Il campo :attribute è obbligatorio quando :other è accettato.',
    'required_if_declined' => 'Il campo :attribute è obbligatorio quando :other è rifiutato.',
    'required_unless' => 'Il campo :attribute è obbligatorio a meno che :other sia in :values.',
    'required_with' => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all' => 'Il campo :attribute è obbligatorio quando :values sono presenti.',
    'required_without' => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno dei valori :values è presente.',
    'same' => 'Il campo :attribute deve corrispondere a :other.',
    'size' => [
        'array' => 'Il campo :attribute deve contenere :size elementi.',
        'file' => 'Il campo :attribute deve essere :size kilobytes.',
        'numeric' => 'Il campo :attribute deve essere :size.',
        'string' => 'Il campo :attribute deve avere :size caratteri.',
    ],
    'starts_with' => 'Il campo :attribute deve iniziare con uno dei seguenti: :values.',
    'string'               => ':attribute deve essere una stringa.',
    'two_column_unique_undeleted' => ':attribute deve essere univoco tra :table1 e :table2 . ',
    'unique_undeleted'     => ':attribute deve essere unico.',
    'non_circular'         => ':attribute non deve creare un riferimento circolare.',
    'not_array'            => ':attribute non può essere un array.',
    'disallow_same_pwd_as_user_fields' => 'La password non può essere uguale al nome utente.',
    'letters'              => 'La password deve contenere almeno una lettera.',
    'numbers'              => 'La password deve contenere almeno un numero.',
    'case_diff'            => 'La password deve utilizzare maiuscole e minuscole.',
    'symbols'              => 'La password deve contenere simboli.',
    'timezone' => 'Il campo :attribute deve essere un fuso orario valido.',
    'unique' => ':attribute è già stato preso.',
    'uploaded' => 'Non è stato possibile caricare :attribute.',
    'uppercase' => 'Il campo :attribute deve essere maiuscolo.',
    'url' => 'Il campo :attribute deve essere un URL valido.',
    'ulid' => 'Il campo :attribute deve essere un ULID valido.',
    'uuid' => 'Il campo :attribute deve essere un UUID valido.',
    'fmcs_location' => 'Nelle Impostazioni Admin è abilitato il supporto completo multi aziendale con restrizione per le Sedi, e la Sede selezionata non è compatibile con l\'Azienda scelta.',


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

    'email_array'      => 'Una o più email sono invalidi.',
    'checkboxes'           => ':attribute contiene opzioni non valide.',
    'radio_buttons'        => ':attribute non è valido.',
    
    'custom' => [
        'alpha_space' => 'Il campo :attribute contiene un carattere non consentito.',

        'hashed_pass'      => 'La tua attuale password non è corretta',
        'dumbpwd'          => 'Questa password è troppo comune.',
        'statuslabel_type' => 'È necessario selezionare un tipo di etichetta di stato valido',
        'custom_field_not_found'          => 'Questo campo non sembra esistere, si prega di ricontrollare i nomi dei campi personalizzati.',
        'custom_field_not_found_on_model' => 'Sembra che questo campo esista, ma non è disponibile tra i campi di questo Modello di Bene.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute deve essere in formato AAAA-MM-GG',
        'last_audit_date.date_format'   =>  ':attribute deve essere in formato AAAA-MM-GG hh:mm:ss',
        'expiration_date.date_format'   =>  ':attribute deve essere in formato AAAA-MM-GG',
        'termination_date.date_format'  =>  ':attribute deve essere in formato AAAA-MM-GG',
        'expected_checkin.date_format'  =>  ':attribute deve essere in formato AAAA-MM-GG',
        'start_date.date_format'        =>  ':attribute deve essere in formato AAAA-MM-GG',
        'end_date.date_format'          =>  ':attribute deve essere in formato AAAA-MM-GG',
        'invalid_value_in_field' => 'Valore non valido incluso in questo campo',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (grafìa mista) non va bene. Dovresti piuttosto usare <code>samaccountname</code> (minuscolo).'
        ],
        'ldap_auth_filter_query' => ['not_in' => 'Probabilmente <code>uid=samaccountname</code> non è un filtro di l\'autenticazione valido. Forse è meglio <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Questo valore probabilmente non dovrebbe stare tra parentesi.'],

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
        'invalid_value_in_field' => 'Valore non valido incluso in questo campo',
        'required' => 'Questo campo è obbligatorio',
        'email' => 'Inserire un indirizzo e-mail valido',
    ],


];
