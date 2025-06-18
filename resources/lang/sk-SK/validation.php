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

    'accepted' => 'Potrebné potvrdiť :attribute.',
    'accepted_if' => 'Položka :attribute musí vyť potvrdená ak :other je :value.',
    'active_url' => 'Pole :attribute musí obsahovať správnu URL adresu.',
    'after' => 'Pole :attribute musí obsahovať dátum po :date.',
    'after_or_equal' => 'Pole :attribute musí obsahovať dátum rovnaký alebo nasledujúci po :date.',
    'alpha' => 'Pole :attribute musí obsahovať iba písmená.',
    'alpha_dash' => 'Pole :attribute musí obsahovať iba písmená, čísla, pomlčky a podčiarkovníky.',
    'alpha_num' => 'Pole :attribute músí obsahovať iba písmená a čisla.',
    'array' => 'Pole :attribute musí obsahovať pole hodnôt.',
    'ascii' => 'Pole :attribute musí obsahovať iba jednoznakové alfanumerické znaky a symboly.',
    'before' => 'Pole :attribute musí obsahovať dátum pred :date.',
    'before_or_equal' => 'Pole :attribute musí obsahovať dátum rovnaký alebo predchádzajúci dátumu :date.',
    'between' => [
        'array' => 'Pole :attribute musí obsahovať hodnotu medzi :min a :max položkami.',
        'file' => 'Pole :attribute musí obsahovať hodnotu medzi :min a :max kilobajtami.',
        'numeric' => 'Pole :attribute musí obsahovať hodnotu medzi :min a :max.',
        'string' => 'Pole :attribute musí obsahovať hodnotu medzi :min a :max znamkmi.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Pole :attribút musí obsahovať hodnoty pravda alebo nepravda.',
    'can' => 'Pole :attribute obsahuje nepovolenú hodnotu.',
    'confirmed' => 'Pole :attribute nesedí s overením.',
    'contains' => 'Pole :attribute neobsahuje požadovanú hodnotu.',
    'current_password' => 'Heslo je nesprávne.',
    'date' => 'Pole :attribute musí obsahovať platný dátum.',
    'date_equals' => 'Pole :attribute musí obsahovať dátum rovnaký ako :date.',
    'date_format' => 'Pole :attribute musí obashovať hodnotnu zhodnú s formátom :format.',
    'decimal' => 'Pole :attribute musí obsahovať :decimal desatinných miest.',
    'declined' => 'Pole :attribute musí byť zakázané.',
    'declined_if' => 'Pole :attribute musí byť zakázané keď :other má hodnotu :value.',
    'different' => 'Polia :attribute a :other sa nemôžu zhodovať.',
    'digits' => 'Pole :attribute musí obsahovať :digits číslic.',
    'digits_between' => 'Pole :attribute musí obsahovať od :min do :max číslic.',
    'dimensions' => 'Pole :attribute má nesprávne rozmery obrázku.',
    'distinct' => 'Pole :attribute obsahoje duplicitnú hodnotu.',
    'doesnt_end_with' => 'Pole :attribute nemôže končiť jednou z nasledujúcich hodnôt: :values.',
    'doesnt_start_with' => 'Pole :attribute nemôže začínať s jednou z nasledujúcich hodnôt: :values.',
    'email' => 'Pole :attribute musí obsahovať platnú emailovú adresu.',
    'ends_with' => 'Pole :attribute musí končiť jednou z nasledujúcich hodnôt: :values.',
    'enum' => 'Označený :attribute je neplatný.',
    'exists' => 'Označený :attribute je neplatný.',
    'extensions' => 'Pole :attribute musí mať jednu z nasledujúcich prípon: :values.',
    'file' => 'Pole :attribute musí obsahovať súbor.',
    'filled' => 'Pole :attribute musí obsahovať hodnotu.',
    'gt' => [
        'array' => 'Pole :attribute musí obsahovať viac ako :value položiek.',
        'file' => 'Pole :attribute musí byť väčšie ako :value kilobytov.',
        'numeric' => 'Pole :attribute musí byť vačie ako :value.',
        'string' => 'Pole :attribute musí byť vačšie ako :value znakov.',
    ],
    'gte' => [
        'array' => 'Pole :attribute musí mať :value alebo viac položiek.',
        'file' => 'Pole :attribute musí byť väčšie alebo rovné ako :value kilobytov.',
        'numeric' => 'Pole :attribute musí byť väčšie alebo rovnaké ako :value.',
        'string' => 'Hodnota poľa :attribute musí byť väčia alebo rovná :value znakov.',
    ],
    'hex_color' => 'Hodnota poľa :attribute musí byť platným zápisom farby v hexadecimálnom formáte.',
    'image' => 'Pole :attribute musí obsahovať obrázok.',
    'import_field_empty'    => 'Hodnote pre :fieldname nemôže byť nulová.',
    'in' => 'Označený :attribute je neplatný.',
    'in_array' => 'Pole :attribute musí existovať v :other.',
    'integer' => 'Pole :attribute musí byť číslom.',
    'ip' => 'Pole :attribute musí byť platnou IP adresou.',
    'ipv4' => 'Pole :attribute musí byť platnou IPv4 adresou.',
    'ipv6' => 'Pole :attribute musí byť platnou IPv6 adresou.',
    'json' => 'Pole :attribute musí byť platným JSON-om.',
    'list' => 'Pole :attribute musí byť zoznamom.',
    'lowercase' => 'Pole :attribute musí obsahovať malé písmenka.',
    'lt' => [
        'array' => 'Pole :attribute musí obsahovať menej ako :value položiek.',
        'file' => 'Pole :attribute musí byť menšie ako :value kilobajtov.',
        'numeric' => 'Pole :attribute musí byť menšie ako :value.',
        'string' => 'Pole :attribute musí byť menšie ako :value znakov.',
    ],
    'lte' => [
        'array' => 'Pole :attribute nemôže obsahovať viac ako :value položiek.',
        'file' => 'Pole :attribute musí byť menšie alebo rovné ako :value kilobajtov.',
        'numeric' => 'Pole :attribute musí byť menšie alebo rovné :value.',
        'string' => 'Pole :attribute musí byť menšie alebo rovné ako :value znakov.',
    ],
    'mac_address' => 'Pole :attribute musí obsahovať platnú MAC adresu.',
    'max' => [
        'array' => 'Pole :attribute nemôže obsahovať viac ako :max položiek.',
        'file' => 'Pole :attribute nemôže byť väčšie ako :max kilobajtov.',
        'numeric' => 'Pole :attribute nemôže byť väčšie ako :max.',
        'string' => 'Pole :attribute nemôže byť väčšie ako :max znakov.',
    ],
    'max_digits' => 'Pole :attribute nemôže obsahovať viac ako :max čislic.',
    'mimes' => 'Pole :attribute musí obsahovať súbory typu: :values.',
    'mimetypes' => 'Pole :attribute musí obsahovať súborytypu: :values.',
    'min' => [
        'array' => 'Pole :attribute musí obsahovať minimálne :min položiek.',
        'file' => 'Pole :attribute musí byť minimálne :min kilobajtov.',
        'numeric' => 'Pole :attribute musí byť minimálne :min.',
        'string' => 'Pole :attribute musí obsahovať minimálne :min znakov.',
    ],
    'min_digits' => 'Pole :attribute musí máť minimálne :min čislic.',
    'missing' => 'Pole :attribute musí chýbať.',
    'missing_if' => 'Pole :attribute musí chýbať keď :other je :value.',
    'missing_unless' => 'Pole :attribute musí chýbať pokým :other je :value.',
    'missing_with' => 'Pole :attribute musí chýbať keď :values sú prítomné.',
    'missing_with_all' => 'Pole :attribute musí chýbať keď :values sú prítomné.',
    'multiple_of' => 'Pole :attribute musí byť násobkom hodnoty :value.',
    'not_in' => 'Označený :attribute je neplatný.',
    'not_regex' => 'Pole :attribute má nesprávny formát.',
    'numeric' => 'Pole :attribute musí obsahovať čislo.',
    'password' => [
        'letters' => 'Pole :attribute musí obsahovať aspoň jeden znak.',
        'mixed' => 'Pole :attribute musí obsahovať aspoň jedno veľke a jedno mále písmeno.',
        'numbers' => 'Pole :attribute musí obsahovať aspoň jedno čislo.',
        'symbols' => 'Pole :attribute musí obsahovať aspoň jeden symbol.',
        'uncompromised' => 'Zadaný :attribute sa nachádza na zozname dátových únikov. Prosím zvoľte iný :attribute.',
    ],
    'percent'       => 'Minimálne odpisy musia byť medzi 0 a 100, keď je typ odpisu percentuálny.',

    'present' => 'Pole :attribute musí byť prítomné.',
    'present_if' => 'Pole :attribute musí byť prítomné keď :other je :value.',
    'present_unless' => 'Pole :attribute musí byť prítomné pokým :other je :value.',
    'present_with' => 'Pole :attribute musí byť prítomné keď :values je prítomné.',
    'present_with_all' => 'Pole :attribute musí byť prítomné keď :values sú prítomné.',
    'prohibited' => 'Pole :attribute musí byť zakázané.',
    'prohibited_if' => 'Pole :attribute je zakázané keď :other je :value.',
    'prohibited_unless' => 'Pole :attribute je zakázané pokým :other je medzi :values.',
    'prohibits' => 'Pole :attribute je zakáže zobrazovať :other.',
    'regex' => 'Pole :attribute má nesprávny formát.',
    'required' => 'Pole :attribute je povinné.',
    'required_array_keys' => 'Pole :attribute musí obsahovať položky z :values.',
    'required_if' => 'Pole :attribute je povinné keď :other je :value.',
    'required_if_accepted' => 'Pole :attribute je povinné keď :other je akceptované.',
    'required_if_declined' => 'Pole :attribute je povinné keď :other je odmietnuté.',
    'required_unless' => 'Pole :attribute je povinné pokým :other je z :values.',
    'required_with' => 'Pole :attribute je povinné keď :values sú prítomné.',
    'required_with_all' => 'Pole :attribute je povinné keď :value sú prítomné.',
    'required_without' => 'Pole :attribute je povinné keď :values nie sú prítomné.',
    'required_without_all' => 'Pole :attribute je povinné keď žiadna z hodnôt :values nie je použitá.',
    'same' => 'Pole :attribute sa musí zhodovať s :other.',
    'size' => [
        'array' => 'Pole :attribute musí obsahovať :size položiek.',
        'file' => 'Pole :attribute musí byť :size kilobajtov.',
        'numeric' => 'Pole :attribute musí byť :size.',
        'string' => 'Pole :attribute musí obsahovať :size znakov.',
    ],
    'starts_with' => 'Pole :attribute musí začínať s jedným z nasledovných hodnôt: values.',
    'string'               => 'Pole :attribute musí obsahovať text.',
    'two_column_unique_undeleted' => 'Pole :attribute musí byť unikátne naprieč :table1 a :table2. ',
    'unique_undeleted'     => 'Pole :attribute musí byť unikátne.',
    'non_circular'         => 'Pole :attribute nemôže vytvárať kruhovú referenciu.',
    'not_array'            => ':attribute nemôže byť poľom.',
    'disallow_same_pwd_as_user_fields' => 'Heslo nemôže byť rovnaké ako užívateľské meno.',
    'letters'              => 'Heslo musí obsahovať najmenej jedno písmeno.',
    'numbers'              => 'Heslo musí obsahovať najmenej jednu číslicu.',
    'case_diff'            => 'Heslo musí obsahovať veľké aj malé písmena.',
    'symbols'              => 'Heslo musí obsahovať symboly.',
    'timezone' => 'Pole :attribute musí obsahovať platnú časovú zónu.',
    'unique' => 'Pole :attribute už bolo vybrané.',
    'uploaded' => 'Pole :attribute sa nepodarilo nahrať.',
    'uppercase' => 'Pole :attribute musí obsahovať veľké písmená.',
    'url' => 'Pole :attribute musí obsahovať správnu URL adresu.',
    'ulid' => 'Pole :attribute musí obsahovať platný ULID.',
    'uuid' => 'Pole :attribute musí obsahovať platný ULID.',
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

    'email_array'      => 'Neplatná jedna alebo viacero emailových adries.',
    'checkboxes'           => ':attribute obsahuje neplatné možnosti.',
    'radio_buttons'        => ':attribute je neplatný.',
    
    'custom' => [
        'alpha_space' => 'Pole :attribute obsahuje nepovolený znak.',

        'hashed_pass'      => 'Vaše aktuálne heslo je nesprávne',
        'dumbpwd'          => 'Heslo je príliš bežné.',
        'statuslabel_type' => 'Musíte zvoliť platný typ stavového štítku',
        'custom_field_not_found'          => 'Pole podľa všetkého neexistuje, prosím overte názvy vlastných polí.',
        'custom_field_not_found_on_model' => 'Pole podľa všetkého existuje, avšak nie je dostupné pre sadu polí tohto modelu majetku.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => 'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD',
        'last_audit_date.date_format'   =>  'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD',
        'termination_date.date_format'  =>  'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD',
        'expected_checkin.date_format'  =>  'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD',
        'start_date.date_format'        =>  'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD',
        'end_date.date_format'          =>  'Pole :attribute musí obsahovať platný dátum vo formáte YYYY-MM-DD',
        'invalid_value_in_field' => 'Neplatná hodnota zahrnutá v tomto poli',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (malé aj veľké znaky) pravdepodobne nebude fungovať. Mali by ste použiť <code>samaccountname</code> (malé znaky) namiesto neho.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> nie je pravdepodobné platné pole pre filter autentifikácie. Pravdepodobne ste chceli <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'Táto hodnota by nemala byť obalená v zátvorkách.'],

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
        'email' => 'Zadajte platnú e-mailovú adresu',
    ],


];
