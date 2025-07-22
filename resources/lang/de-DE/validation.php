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

    'accepted' => 'Dieses :attribute Feld muss akzeptiert werden.',
    'accepted_if' => 'Dieses :attribute Feld muss akzeptiert werden wenn :other :value ist.',
    'active_url' => 'Dieses :attribute Feld muss eine gültige URL sein.',
    'after' => 'Das Feld :attribute muss ein Datum nach :date sein.',
    'after_or_equal' => 'Das Feld :attribute muss ein Datum nach oder gleich :date sein.',
    'alpha' => 'Das Feld :attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => 'Das Feld :attribute darf nur Buchstaben, Zahlen, Binde- und Unterstriche enthalten.',
    'alpha_num' => 'Das Feld :attribute darf nur Buchstaben und Zahlen enthalten.',
    'array' => 'Das Feld :attribute muss ein Array sein.',
    'ascii' => 'Das Feld :attribute darf nur einbyteige alphanumerische Zeichen und Symbole enthalten.',
    'before' => 'Das Feld :attribute muss ein Datum vor dem :date sein.',
    'before_or_equal' => 'Das Feld :attribute muss ein Datum vor oder gleich dem :date sein.',
    'between' => [
        'array' => 'Das Feld :attribute muss zwischen :min und :max Elemente enthalten.',
        'file' => 'Das Feld :attribute muss zwischen :min und :max Kilobyte liegen.',
        'numeric' => 'Das Feld :attribute muss zwischen :min und :max liegen.',
        'string' => 'Das Feld :attribute muss zwischen :min und :max Zeichen enthalten.',
    ],
    'valid_regex' => 'Der reguläre Ausdruck ist ungültig.',
    'boolean' => 'Das Feld :attribute muss true oder false sein.',
    'can' => 'Das Feld :attribute enthält einen nicht autorisierten Wert.',
    'confirmed' => 'Die Bestätigung des Feldes :attribute stimmt nicht überein.',
    'contains' => 'Im Feld :attribute fehlt ein erforderlicher Wert.',
    'current_password' => 'Das Passwort ist ungültig.',
    'date' => 'Das Feld :attribute muss ein gültiges Datum enthalten.',
    'date_equals' => 'Das Feld :attribute muss ein Datum enthalten, das dem Wert :date entspricht.',
    'date_format' => 'Das Feld :attribute muss dem Format :format entsprechen.',
    'decimal' => 'Das Feld :attribute muss :decimal Dezimalstellen haben.',
    'declined' => 'Das Feld :attribute muss abgelehnt werden.',
    'declined_if' => 'Das Feld :attribute muss abgelehnt werden, wenn :other :value ist.',
    'different' => 'Die Felder :attribute und :other müssen unterschiedlich sein.',
    'digits' => 'Das Feld :attribute muss :digits Ziffern enthalten.',
    'digits_between' => 'Das Feld :attribute muss zwischen :min und :max Ziffern enthalten.',
    'dimensions' => 'Das Feld :attribute hat ungültige Bilddimensionen.',
    'distinct' => 'Das Attributfeld hat einen doppelten Wert.',
    'doesnt_end_with' => 'Das Feld :attribute darf nicht mit einem der folgenden Elemente enden: :values.',
    'doesnt_start_with' => 'Das Feld :attribute darf nicht mit einem der folgenden Elemente enden: :values.',
    'email' => 'Das Feld :attribute muss eine gültige E-Mail-Adresse enthalten.',
    'ends_with' => 'Das Feld :attribute muss mit einem der folgenden enden: :values.',
    'enum' => 'Auswahl :attribute ist ungültig.',
    'exists' => 'Das ausgewählte :attribute ist ungültig.',
    'extensions' => 'Das Feld :attribute muss eine der folgenden Erweiterungen haben: :values.',
    'file' => 'Das Feld :attribute muss eine Datei sein.',
    'filled' => 'Das :attribute Feld muss einen Wert haben.',
    'gt' => [
        'array' => 'Das Feld :attribute muss mehr als :value Elemente haben.',
        'file' => 'Das Feld :attribute muss größer als :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss größer sein als :value.',
        'string' => 'Das Feld :attribute muss größer als :value Zeichen sein.',
    ],
    'gte' => [
        'array' => 'Das Feld :attribute muss :value Elemente oder mehr enthalten.',
        'file' => 'Das Feld :attribute muss größer oder gleich :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss größer oder gleich :value sein.',
        'string' => 'Das Feld :attribute muss größer oder gleich :value Zeichen sein.',
    ],
    'hex_color' => 'Das Feld :attribute muss eine gültige hexadezimale Farbe sein.',
    'image' => 'Das Feld :attribute muss ein Bild sein.',
    'import_field_empty'    => ':fieldname darf nicht leer sein.',
    'in' => 'Auswahl :attribute ist ungültig.',
    'in_array' => 'Das Feld :attribute muss in :other vorhanden sein.',
    'integer' => 'Das Feld :attribute muss ein Integer sein.',
    'ip' => 'Das Feld :attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => 'Das Feld :attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => 'Das Feld :attribute muss eine gültige IPv6-Adresse sein.',
    'json' => 'Das Feld :attribute muss eine gültige JSON-Zeichenfolge sein.',
    'list' => 'Das Feld :attribute muss eine Liste sein.',
    'lowercase' => 'Das Feld :attribute muss klein geschrieben sein.',
    'lt' => [
        'array' => 'Das Feld :attribute muss weniger als :value Elemente haben.',
        'file' => 'Das Feld :attribute muss kleiner als :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss kleiner sein als :value.',
        'string' => 'Das Feld :attribut muss weniger als :value Zeichen enthalten.',
    ],
    'lte' => [
        'array' => 'Das Feld :attribute darf nicht mehr als :value Elemente enthalten.',
        'file' => 'Das Feld :attribute muss kleiner oder gleich :value Kilobyte sein.',
        'numeric' => 'Das Feld :attribute muss kleiner oder gleich :value sein.',
        'string' => 'Das Feld :attribute muss kleiner oder gleich :value Zeichen sein.',
    ],
    'mac_address' => 'Das Feld :attribute muss eine gültige MAC-Adresse sein.',
    'max' => [
        'array' => 'Das Feld :attribute darf nicht mehr als :max Elemente enthalten.',
        'file' => 'Das Feld :attribute darf nicht größer als :max Kilobyte sein.',
        'numeric' => 'Das Feld :attribute darf nicht größer als :max sein.',
        'string' => 'Das Feld :attribute darf nicht größer als :max Zeichen sein.',
    ],
    'max_digits' => 'Das Feld :attribute darf nicht mehr als :max Ziffern haben.',
    'mimes' => 'Das Feld :attribute muss eine Datei vom Typ: :values ​​sein.',
    'mimetypes' => 'Das Feld :attribute muss eine Datei vom Typ: :values ​​sein.',
    'min' => [
        'array' => 'Das Feld :attribute muss mindestens :min Elemente enthalten.',
        'file' => 'Das Feld :attribute muss mindestens :min Kilobyte groß sein.',
        'numeric' => 'Das Feld :attribute muss mindestens :min sein.',
        'string' => 'Das Feld :attribute muss mindestens :min Zeichen enthalten.',
    ],
    'min_digits' => 'Das Feld :attribute muss mindestens :min Ziffern haben.',
    'missing' => 'Das Feld :attribute muss fehlen.',
    'missing_if' => 'Das Feld :attribute muss fehlen, wenn :other :value ist.',
    'missing_unless' => 'Das Feld :attribute muss fehlen, es sei denn, :other ist :value.',
    'missing_with' => 'Das Feld :attribute muss fehlen, wenn :values ​​vorhanden ist.',
    'missing_with_all' => 'Das Feld :attribute muss fehlen, wenn :values ​​vorhanden sind.',
    'multiple_of' => 'Das Feld :attribute muss ein Vielfaches von :value sein.',
    'not_in' => 'Auswahl :attribute ist ungültig.',
    'not_regex' => 'Das Format von dem Feld :attribute ist ungültig.',
    'numeric' => 'Das Feld :attribute muss eine Zahl sein.',
    'password' => [
        'letters' => 'Das Feld :attribute muss mindestens einen Buchstaben enthalten.',
        'mixed' => 'Das Feld :attribute muss mindestens einen Groß- und einen Kleinbuchstaben enthalten.',
        'numbers' => 'Das Feld :attribute muss mindestens eine Zahl enthalten.',
        'symbols' => 'Das Feld :attribute muss mindestens ein Symbol enthalten.',
        'uncompromised' => 'Das angegebene :attribute ist in einem Datenleck aufgetaucht. Bitte wählen Sie ein anderes :attribute.',
    ],
    'percent'       => 'Bei einer prozentualen Abschreibung muss der Mindestabschreibungswert zwischen 0 und 100 liegen.',

    'present' => ':attribute muss vorhanden sein.',
    'present_if' => 'Das Feld :attribute muss vorhanden sein, wenn :other :value ist.',
    'present_unless' => 'Das Feld :attribute muss vorhanden sein, es sei denn, :other ist :value.',
    'present_with' => 'Das Feld :attribute muss vorhanden sein, wenn :values ​​vorhanden ist.',
    'present_with_all' => 'Das Feld :attribute muss vorhanden sein, wenn :values ​​vorhanden sind.',
    'prohibited' => 'Das Feld :attribute ist nicht erlaubt.',
    'prohibited_if' => 'Das Feld :attribute ist nicht erlaubt, wenn :other :value ist.',
    'prohibited_unless' => 'Das Feld :attribute ist nicht erlaubt, sofern :other nicht in :values ​​enthalten ist.',
    'prohibits' => 'Das Feld :attribute verhindert die Anwesenheit von :other.',
    'regex' => 'Das Format von dem Feld :attribute ist ungültig.',
    'required' => ':attribute Feld muss ausgefüllt sein.',
    'required_array_keys' => 'Das Feld :attribute muss Einträge für: :values ​​enthalten.',
    'required_if' => ':attribute wird benötigt, wenn :other :value entspricht.',
    'required_if_accepted' => 'Das Feld :attribute ist erforderlich, wenn :other akzeptiert wird.',
    'required_if_declined' => 'Das Feld :attribute ist erforderlich, wenn :other abgelehnt wird.',
    'required_unless' => ':attribute ist erforderlich, es sei denn :other ist in :values.',
    'required_with' => ':attribute wird benötigt wenn :value ausgewählt ist.',
    'required_with_all' => 'Das Feld :attribute ist erforderlich, wenn :values ​​vorhanden sind.',
    'required_without' => ':attribute wird benötigt wenn :value nicht ausgewählt ist.',
    'required_without_all' => 'Das: Attributfeld ist erforderlich, wenn keine der folgenden Werte vorhanden sind:',
    'same' => 'Das Feld :attribute muss mit :other übereinstimmen.',
    'size' => [
        'array' => 'Das Feld :attribute muss :size Elemente enthalten.',
        'file' => 'Das Feld :attribute muss :size Kilobyte groß sein.',
        'numeric' => 'Das Feld :attribute muss :size sein.',
        'string' => 'Das Feld :attribute muss :size Zeichen enthalten.',
    ],
    'starts_with' => 'Das Feld :attribute muss mit einem der folgenden beginnen: :values.',
    'string'               => 'Das Attribut muss eine Zeichenfolge sein.',
    'two_column_unique_undeleted' => ':attribute muss in :table1 und :table2 einzigartig sein. ',
    'unique_undeleted'     => 'Die Variable :attribute muss eindeutig sein.',
    'non_circular'         => 'Das :attribute darf keinen Zirkelbezug ergeben.',
    'not_array'            => ':attribute darf kein Array sein.',
    'disallow_same_pwd_as_user_fields' => 'Das Passwort muss sich vom Nutzernamen unterscheiden.',
    'letters'              => 'Das Passwort muss mindestens einen Buchstaben beinhalten.',
    'numbers'              => 'Das Passwort muss mindestens eine Zahl beinhalten.',
    'case_diff'            => 'Das Passwort muss Groß- und Kleinschreibung beinhalten.',
    'symbols'              => 'Das Passwort muss Sonderzeichen beinhalten.',
    'timezone' => 'Das Feld :attribute muss eine gültige Zeitzone sein.',
    'unique' => ':attribute schon benutzt.',
    'uploaded' => ':attribute konnte nicht hochgeladen werden.',
    'uppercase' => 'Das Feld :attribute muss groß geschrieben werden.',
    'url' => 'Das Feld :attribute muss eine gültige URL sein.',
    'ulid' => 'Das Feld :attribute muss eine gültige ULID sein.',
    'uuid' => 'Das Feld :attribute muss eine gültige UUID sein.',
    'fmcs_location' => 'In den Admin-Einstellungen ist eine vollständige Mehrfachunterstützung für Unternehmen und Standortbereicherung aktiviert und der ausgewählte Standort und die ausgewählte Firma sind nicht kompatibel.',


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

    'email_array'      => 'Eine oder mehrere Email Adressen sind ungültig.',
    'checkboxes'           => ':attribute enthält ungültige Optionen.',
    'radio_buttons'        => ':attribute ist ungültig.',
    
    'custom' => [
        'alpha_space' => 'Das :attribute Feld enthält ein nicht erlaubtes Zeichen.',

        'hashed_pass'      => 'Ihr derzeitiges Passwort ist nicht korrekt',
        'dumbpwd'          => 'Das Passwort ist zu gebräuchlich.',
        'statuslabel_type' => 'Sie müssen einen gültigen Statuslabel-Typ auswählen',
        'custom_field_not_found'          => 'Dieses Feld scheint nicht zu existieren. Bitte überprüfen Sie Ihre benutzerdefinierten Feldnamen noch einmal.',
        'custom_field_not_found_on_model' => 'Dieses Feld scheint vorhanden zu sein, ist aber im Feldsatz dieses Asset-Modells nicht verfügbar.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT sein',
        'last_audit_date.date_format'   =>  ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT hh:mm:ss sein',
        'expiration_date.date_format'   =>  ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT sein',
        'termination_date.date_format'  =>  ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT sein',
        'expected_checkin.date_format'  =>  ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT sein',
        'start_date.date_format'        =>  ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT sein',
        'end_date.date_format'          =>  ':attribute muss ein gültiges Datum im Format JJJJ-MM-TT sein',
        'invalid_value_in_field' => 'Ungültiger Wert in diesem Feld enthalten',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (Groß- und Kleinschreibung) funktioniert wahrscheinlich nicht. Sie sollten stattdessen <code>samaccountname</code> (Kleinschreibung) verwenden.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> ist wahrscheinlich kein gültiger Authentifizierungsfilter. Wahrscheinlich möchten Sie <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Dieser Wert sollte wahrscheinlich nicht in Klammern gesetzt werden.'],

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
        'invalid_value_in_field' => 'Ungültiger Wert in diesem Feld enthalten',
        'required' => 'Dieses Feld ist erforderlich',
        'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein',
    ],


];
