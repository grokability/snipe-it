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

    'accepted' => '«:attribute» դաշտը պետք է ընդունվի:',
    'accepted_if' => '«:attribute» դաշտը պետք է լինի վավեր Url։',
    'active_url' => '«:attribute» դաշտը պետք է լինի ամսաթիվ :date-ից հետո։',
    'after' => '«:attribute» դաշտը պետք է լինի ամսաթիվ :date-ից հետո։',
    'after_or_equal' => '«:attribute» դաշտը պետք է լինի ամսաթիվ :date-ին հավասար կամ դրանից հետո։',
    'alpha' => '«:attribute» դաշտը պետք է պարունակի միայն տառեր։',
    'alpha_dash' => '«:attribute» դաշտը պետք է պարունակի միայն տառեր, թվեր, գծիկներ և ընդգծման նշաններ։',
    'alpha_num' => '«:attribute» դաշտը պետք է պարունակի միայն տառեր և թվեր:',
    'array' => '«:attribute» դաշտը պետք է լինի զանգված:',
    'ascii' => '«:attribute» դաշտը պետք է պարունակի միայն միաբայթ տառաթվային նշաններ և սիմվոլներ:',
    'before' => '«:attribute» դաշտը պետք է լինի ամսաթիվ :date-ից առաջ:',
    'before_or_equal' => '«:attribute» դաշտը պետք է լինի ամսաթիվ :date-ին հավասար կամ դրանից առաջ:',
    'between' => [
        'array' => '«:attribute» դաշտը պետք է պարունակի :min-ից :max տարր:',
        'file' => '«:attribute» դաշտը պետք է լինի :min-ից :max կիլոբայթի սահմաններում:',
        'numeric' => '«:attribute» դաշտը պետք է լինի :min-ից :max սահմաններում:',
        'string' => '«:attribute» դաշտը պետք է պարունակի :min-ից :max նիշ:',
    ],
    'valid_regex' => 'Կանոնավոր արտահայտությունը (regex) անվավեր է:',
    'boolean' => '«:attribute» դաշտը պետք է լինի «ճիշտ» (true) կամ «սխալ» (false):',
    'can' => '«:attribute» դաշտը պարունակում է չթույլատրված արժեք:',
    'confirmed' => '«:attribute» դաշտի հաստատումը չի համապատասխանում:',
    'contains' => '«:attribute» դաշտը չունի պարտադիր արժեք:',
    'current_password' => 'Գաղտնաբառը սխալ է:',
    'date' => '«:attribute» դաշտը պետք է լինի վավեր ամսաթիվ:',
    'date_equals' => ':attribute դաշտը պետք է լինի :date-ին հավասար ամսաթիվ։',
    'date_format' => '«:attribute» դաշտը պետք է համապատասխանի :format ձևաչափին:',
    'decimal' => '«:attribute» դաշտը պետք է ունենա :decimal տասնորդական նիշ:',
    'declined' => '«:attribute» դաշտը պետք է մերժվի:',
    'declined_if' => '«:attribute» դաշտը պետք է մերժվի, երբ «:other»-ը «:value» է:',
    'different' => '«:attribute» դաշտը և «:other»-ը պետք է տարբեր լինեն:',
    'digits' => '«:attribute» դաշտը պետք է լինի :digits նիշանոց թիվ:',
    'digits_between' => '«:attribute» դաշտը պետք է լինի :min-ից :max նիշանոց թիվ:',
    'dimensions' => '«:attribute» դաշտի պատկերի չափսերն անվավեր են:',
    'distinct' => '«:attribute» դաշտը պարունակում է կրկնվող արժեք:',
    'doesnt_end_with' => '«:attribute» դաշտը չպետք է ավարտվի հետևյալներից որևէ մեկով՝ :values:',
    'doesnt_start_with' => '«:attribute» դաշտը չպետք է սկսվի հետևյալներից որևէ մեկով՝ :values:',
    'email' => '«:attribute» դաշտը պետք է լինի վավեր էլեկտրոնային հասցե:',
    'ends_with' => '«:attribute» դաշտը պետք է ավարտվի հետևյալներից որևէ մեկով՝ :values:',
    'enum' => 'Ընտրված «:attribute»-ն անվավեր է:',
    'exists' => 'Ընտրված «:attribute»-ն անվավեր է:',
    'extensions' => '«:attribute» դաշտը պետք է ունենա հետևյալ ընդլայնումներից մեկը՝ :values:',
    'file' => '«:attribute» դաշտը պետք է լինի ֆայլ:',
    'filled' => '«:attribute» դաշտը պետք է ունենա արժեք:',
    'gt' => [
        'array' => '«:attribute» դաշտը պետք է ունենա ավելի քան :value տարր:',
        'file' => '«:attribute» դաշտը պետք է լինի ավելի քան :value կիլոբայթ:',
        'numeric' => '«:attribute» դաշտը պետք է լինի ավելի մեծ, քան :value-ն:',
        'string' => '«:attribute» դաշտը պետք է պարունակի ավելի քան :value նիշ:',
    ],
    'gte' => [
        'array' => '«:attribute» դաշտը պետք է ունենա :value կամ ավելի տարր:',
        'file' => '«:attribute» դաշտը պետք է լինի :value կամ ավելի կիլոբայթ:',
        'numeric' => '«:attribute» դաշտը պետք է լինի մեծ կամ հավասար :value-ին:',
        'string' => '«:attribute» դաշտը պետք է պարունակի :value կամ ավելի նիշ:',
    ],
    'hex_color' => ':attribute դաշտը պետք է լինի վավեր hexadecimal գույն։',
    'image' => ':attribute դաշտը պետք է լինի պատկեր։',
    'import_field_empty' => ':fieldname դաշտի արժեքը չի կարող դատարկ լինել։',
    'in' => 'Ընտրված «:attribute»-ն անվավեր է:',
    'in_array' => ':attribute դաշտը պետք է գոյություն ունենա :other-ում։',
    'integer' => ':attribute դաշտը պետք է լինի ամբողջ թիվ։',
    'ip' => ':attribute դաշտը պետք է լինի վավեր IP հասցե։',
    'ipv4' => ':attribute դաշտը պետք է լինի վավեր IPv4 հասցե։',
    'ipv6' => ':attribute դաշտը պետք է լինի վավեր IPv6 հասցե։',
    'json' => ':attribute դաշտը պետք է լինի վավեր JSON տող։',
    'list' => ':attribute դաշտը պետք է լինի ցուցակ։',
    'lowercase' => ':attribute դաշտը պետք է լինի փոքրատառերով։',
    'lt' => [
        'array' => ':attribute դաշտը պետք է ունենա :value-ից պակաս տարրեր։',
        'file' => ':attribute դաշտը պետք է լինի :value կիլոբայթից փոքր։',
        'numeric' => ':attribute դաշտը պետք է լինի :value-ից փոքր։',
        'string' => ':attribute դաշտը պետք է ունենա :value-ից պակաս նիշ։',
    ],
    'lte' => [
        'array' => ':attribute դաշտը չպետք է ունենա :value-ից ավելի տարր։',
        'file' => ':attribute դաշտը պետք է լինի առավելագույնը :value կիլոբայթ։',
        'numeric' => ':attribute դաշտը պետք է լինի :value-ից փոքր կամ հավասար։',
        'string' => ':attribute դաշտը պետք է ունենա առավելագույնը :value նիշ։',
    ],
    'mac_address' => ':attribute դաշտը պետք է լինի վավեր MAC հասցե։',
    'max' => [
        'array' => ':attribute դաշտը չպետք է ունենա :max-ից ավելի տարր։',
        'file' => ':attribute դաշտը չպետք է գերազանցի :max կիլոբայթ։',
        'numeric' => ':attribute դաշտը չպետք է լինի :max-ից մեծ։',
        'string' => ':attribute դաշտը չպետք է ունենա :max-ից ավելի նիշ։',
    ],
    'max_digits' => ':attribute դաշտը չպետք է ունենա :max-ից ավելի թվանշան։',
    'mimes' => ':attribute դաշտը պետք է լինի :values տեսակի ֆայլ։',
    'mimetypes' => ':attribute դաշտը պետք է լինի :values տեսակի ֆայլ։',
    'min' => [
        'array' => ':attribute դաշտը պետք է ունենա առնվազն :min տարր։',
        'file' => ':attribute դաշտը պետք է լինի առնվազն :min կիլոբայթ։',
        'numeric' => ':attribute դաշտը պետք է լինի առնվազն :min։',
        'string' => ':attribute դաշտը պետք է ունենա առնվազն :min նիշ։',
    ],
    'min_digits' => ':attribute դաշտը պետք է ունենա առնվազն :min թվանշան։',
    'missing' => ':attribute դաշտը պետք է բացակայի։',
    'missing_if' => ':attribute դաշտը պետք է բացակայի, երբ :other-ը հավասար է :value-ին։',
    'missing_unless' => ':attribute դաշտը պետք է բացակայի, եթե :other-ը հավասար չէ :value-ին։',
    'missing_with' => ':attribute դաշտը պետք է բացակայի, երբ :values-ը առկա է։',
    'missing_with_all' => ':attribute դաշտը պետք է բացակայի, երբ :values-ը առկա են։',
    'multiple_of' => ':attribute դաշտը պետք է լինի :value-ի բազմապատիկ։',
    'not_in' => 'Ընտրված «:attribute»-ն անվավեր է:',
    'not_regex' => ':attribute դաշտի ձևաչափը սխալ է։',
    'numeric' => ':attribute դաշտը պետք է լինի թիվ։',
    'password' => [
        'letters' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ տառ։',
        'mixed' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ մեծատառ և մեկ փոքրատառ։',
        'numbers' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ թիվ։',
        'symbols' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ նշան։',
        'uncompromised' => 'Տրված :attribute-ը հայտնաբերվել է տվյալների արտահոսքում։ Խնդրում ենք ընտրել այլ :attribute։',
    ],
    'percent' => 'Արժեզրկման նվազագույն արժեքը պետք է լինի 0-ից 100 միջակայքում, երբ արժեզրկման տեսակը տոկոս է։',

    'present' => ':attribute դաշտը պետք է առկա լինի։',
    'present_if' => ':attribute դաշտը պետք է առկա լինի, երբ :other-ը հավասար է :value-ին։',
    'present_unless' => ':attribute դաշտը պետք է առկա լինի, եթե :other-ը հավասար չէ :value-ին։',
    'present_with' => ':attribute դաշտը պետք է առկա լինի, երբ :values-ը առկա է։',
    'present_with_all' => ':attribute դաշտը պետք է առկա լինի, երբ :values-ը առկա են։',
    'prohibited' => ':attribute դաշտը արգելված է։',
    'prohibited_if' => ':attribute դաշտը արգելված է, երբ :other-ը հավասար է :value-ին։',
    'prohibited_unless' => ':attribute դաշտը արգելված է, եթե :other-ը չի գտնվում :values ցանկում։',
    'prohibits' => ':attribute դաշտը թույլ չի տալիս, որ :other-ը առկա լինի։',
    'regex' => ':attribute դաշտի ձևաչափը սխալ է։',
    'required' => ':attribute դաշտը պարտադիր է։',
    'required_array_keys' => ':attribute դաշտը պետք է պարունակի հետևյալ արժեքները՝ :values։',
    'required_if' => ':attribute դաշտը պարտադիր է, երբ :other-ը հավասար է :value-ին։',
    'required_if_accepted' => ':attribute դաշտը պարտադիր է, երբ :other-ը ընդունված է։',
    'required_if_declined' => ':attribute դաշտը պարտադիր է, երբ :other-ը մերժված է։',
    'required_unless' => ':attribute դաշտը պարտադիր է, եթե :other-ը չի գտնվում :values ցանկում։',
    'required_with' => ':attribute դաշտը պարտադիր է, երբ :values-ը առկա է։',
    'required_with_all' => ':attribute դաշտը պարտադիր է, երբ :values-ը առկա են։',
    'required_without' => ':attribute դաշտը պարտադիր է, երբ :values-ը առկա չէ։',
    'required_without_all' => ':attribute դաշտը պարտադիր է, երբ :values-ից ոչ մեկը առկա չէ։

Match / Size / Format',
    'same' => ':attribute դաշտը պետք է համընկնի :other-ի հետ։',
    'size' => [
        'array' => ':attribute դաշտը պետք է պարունակի :size տարր։',
        'file' => ':attribute դաշտը պետք է լինի :size կիլոբայթ։',
        'numeric' => ':attribute դաշտը պետք է լինի :size։',
        'string' => ':attribute դաշտը պետք է ունենա :size նիշ։',
    ],
    'starts_with' => ':attribute դաշտը պետք է սկսվի հետևյալներից մեկով՝ :values։',
    'string' => ':attribute-ը պետք է լինի տող (string)։',
    'two_column_unique_undeleted' => ':attribute-ը պետք է լինի եզակի :table1 և :table2 աղյուսակներում։',
    'unique_undeleted' => ':attribute-ը պետք է լինի եզակի։',
    'non_circular' => ':attribute-ը չպետք է ստեղծի շրջանաձև հղում։',
    'not_array' => ':attribute-ը չի կարող լինել զանգված (array)։',
    'disallow_same_pwd_as_user_fields' => 'Գաղտնաբառը չի կարող նույնը լինել, ինչ օգտանունը։',
    'letters' => 'Գաղտնաբառը պետք է պարունակի առնվազն մեկ տառ։',
    'numbers' => 'Գաղտնաբառը պետք է պարունակի առնվազն մեկ թիվ։',
    'case_diff' => 'Գաղտնաբառը պետք է պարունակի մեծ և փոքրատառեր։',
    'symbols' => 'Գաղտնաբառը պետք է պարունակի նշաններ։',
    'timezone' => ':attribute դաշտը պետք է լինի վավեր ժամային գոտի։',
    'unique' => ':attribute-ն արդեն օգտագործվում է։',
    'uploaded' => ':attribute-ը չի հաջողվել վերբեռնել։',
    'uppercase' => ':attribute դաշտը պետք է լինի մեծատառերով։
',
    'url' => '«:attribute» դաշտը պետք է լինի ամսաթիվ :date-ից հետո։',
    'ulid' => ':attribute դաշտը պետք է լինի վավեր ULID։',
    'uuid' => ':attribute դաշտը պետք է լինի վավեր UUID։',
    'fmcs_location' => 'Admin Settings-ում միացված է բազմակի ընկերությունների աջակցությունը և տեղադրության սահմանափակումը, և ընտրված տեղադրությունը և ընկերությունը համատեղելի չեն։',
    'is_unique_across_company_and_location' => ':attribute-ը պետք է եզակի լինի ընտրված ընկերության և տեղադրության շրջանակում։',

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

    'email_array' => 'Մեկ կամ մի քանի էլ․ հասցե սխալ է։',
    'checkboxes' => ':attribute-ը պարունակում է սխալ տարբերակներ։',
    'radio_buttons' => ':attribute-ը սխալ է։',

    'custom' => [
        'alpha_space' => ':attribute դաշտը պարունակում է չթույլատրված նիշ։',

        'hashed_pass' => 'Ձեր ընթացիկ գաղտնաբառը սխալ է։',
        'dumbpwd' => 'Այս գաղտնաբառը չափազանց տարածված է։',
        'statuslabel_type' => 'Պետք է ընտրեք վավեր կարգավիճակի պիտակի տեսակ։',
        'custom_field_not_found' => 'Այս դաշտը կարծես գոյություն չունի։ Խնդրում ենք կրկին ստուգել ձեր custom դաշտերի անունները։',
        'custom_field_not_found_on_model' => 'Այս դաշտը գոյություն ունի, սակայն հասանելի չէ այս Asset Model-ի fieldset-ում։',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD ձևաչափով։',
        'last_audit_date.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD hh:mm:ss ձևաչափով։',
        'expiration_date.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD ձևաչափով։',
        'termination_date.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD ձևաչափով։',
        'expected_checkin.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD ձևաչափով։',
        'start_date.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD ձևաչափով։',
        'end_date.date_format' => ':attribute-ը պետք է լինի վավեր ամսաթիվ՝ YYYY-MM-DD ձևաչափով։',
        'invalid_value_in_field' => 'Այս դաշտում ներառված է սխալ արժեք։',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code> (խառը մեծ և փոքրատառերով) հավանաբար չի աշխատի։ Փոխարենը պետք է օգտագործել <code>samaccountname</code> (փոքրատառերով)։',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> հավանաբար վավեր authentication ֆիլտր չէ։ Հավանաբար պետք է օգտագործեք <code>uid=</code>։'],
        'ldap_filter' => ['regex' => 'Այս արժեքը հավանաբար չպետք է լինի փակագծերի մեջ։'],

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

    'attributes' => [
        'serials.*' => 'Սերիական համար',
        'asset_tags.*' => 'Ակտիվի պիտակ',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'Այս դաշտում ներառված է սխալ արժեք։',
        'required' => 'Այս դաշտը պարտադիր է։',
        'email' => 'Խնդրում ենք մուտքագրել վավեր էլ․ հասցե։',
    ],

];
