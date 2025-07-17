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

    'accepted' => ':attribute ველი უნდა იყოს დადასტურებული.',
    'accepted_if' => ':attribute ველი უნდა იყოს დადასტურებული, როდესაც :other არის :value.',
    'active_url' => ':attribute ველი უნდა იყოს ვალიდური URL მისამართი.',
    'after' => ':attribute ველი უნდა იყოს თარიღი, რომელიც აღემატება :date-ს.',
    'after_or_equal' => ':attribute უნდა იყოს თარიღი, რომელიც აღემატება ან ტოლია :date-ს.',
    'alpha' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ასოებს.',
    'alpha_dash' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ასოებს, ციფრებს, ტირეებსა და ქვედა ტირეებს.',
    'alpha_num' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ასოებსა და ციფრებს.',
    'array' => ':attribute ველი უნდა იყოს მასივი.',
    'ascii' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ერთბაიტიან ასო-ციფრულ სიმბოლოებს და ნიშნებს.',
    'before' => ':attribute ველი უნდა იყოს თარიღი, რომელიც ნაკლებია ვიდრე :date.',
    'before_or_equal' => ':attribute ველი უნდა იყოს თარიღი, რომელიც ნაკლებია ან ტოლია :date-ს.',
    'between' => [
        'array' => ':attribute ველი უნდა შეიცავდეს მინიმუმ :min და მაქსიმუმ :max ელემენტს.',
        'file' => ':attribute ველი უნდა იყოს :min-დან :max კილობაიტამდე ზომის ფაილი.',
        'numeric' => ':attribute ველი უნდა იყოს რიცხვი დიაპაზონში :min-დან :max-მდე.',
        'string' => ':attribute ველი უნდა შეიცავდეს მინიმუმ :min და მაქსიმუმ :max სიმბოლოს.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => ':attribute ველი უნდა იყოს ჭეშმარიტი ან მცდარი.',
    'can' => ':attribute ველი შეიცავს დაუშვებელ მნიშვნელობას.',
    'confirmed' => ':attribute ველის დადასტურება არ ემთხვევა.',
    'contains' => ':attribute ველში სავალდებულო მნიშვნელობა აკლია.',
    'current_password' => 'პაროლი არასწორია.',
    'date' => ':attribute ველი უნდა იყოს ვალიდური თარიღი.',
    'date_equals' => ':attribute ველი უნდა იყოს თარიღი, რომელიც ტოლია :date-ს.',
    'date_format' => ':attribute ველი უნდა შეესაბამებოდეს ფორმატს: :format.',
    'decimal' => ':attribute ველი უნდა შეიცავდეს :decimal ათწილადს.',
    'declined' => ':attribute ველი უნდა იყოს უარყოფილი.',
    'declined_if' => ':attribute ველი უნდა იყოს უარყოფილი, როცა :other არის :value.',
    'different' => ':attribute ველი და :other უნდა განსხვავდებოდეს ერთმანეთისგან.',
    'digits' => ':attribute ველი უნდა შეიცავდეს ზუსტად :digits ციფრს.',
    'digits_between' => ':attribute ველი უნდა შეიცავდეს :min-დან :max ციფრამდე.',
    'dimensions' => ':attribute ველს არასწორი გამოსახულების განზომილებები აქვს.',
    'distinct' => ':attribute ველში დუბლირებული მნიშვნელობაა.',
    'doesnt_end_with' => ':attribute ველი არ უნდა მთავრდებოდეს შემდეგით: :values.',
    'doesnt_start_with' => ':attribute ველი არ უნდა იწყებოდეს შემდეგით: :values.',
    'email' => ':attribute ველი უნდა იყოს ვალიდური ელფოსტის მისამართი.',
    'ends_with' => ':attribute ველი უნდა მთავრდებოდეს ერთ-ერთით: :values.',
    'enum' => 'არჩეული :attribute არასწორია.',
    'exists' => 'არჩეული :attribute არასწორია.',
    'extensions' => ':attribute ველი უნდა შეიცავდეს ერთ-ერთ შემდეგ გაფართოებას: :values.',
    'file' => ':attribute ველი უნდა იყოს ფაილი.',
    'filled' => ':attribute ველს უნდა ჰქონდეს მნიშვნელობა.',
    'gt' => [
        'array' => ':attribute ველი უნდა შეიცავდეს :value-ზე მეტ ელემენტს.',
        'file' => ':attribute ველი უნდა იყოს :value კილობაიტზე მეტი.',
        'numeric' => ':attribute ველი უნდა იყოს :value-ზე მეტი.',
        'string' => ':attribute ველი უნდა იყოს :value სიმბოლოზე მეტი.',
    ],
    'gte' => [
        'array' => ':attribute ველი უნდა შეიცავდეს :value ან მეტ ელემენტს.',
        'file' => ':attribute ველი უნდა იყოს არანაკლებ :value კილობაიტისა.',
        'numeric' => ':attribute ველი უნდა იყოს არანაკლებ :value-ის.',
        'string' => ':attribute ველი უნდა შეიცავდეს არანაკლებ :value სიმბოლოს.',
    ],
    'hex_color' => ':attribute ველი უნდა იყოს სწორი ჰექსადეციმალური ფერის კოდი.',
    'image' => ':attribute ველი უნდა იყოს სურათი.',
    'import_field_empty'    => ':fieldname ველის მნიშვნელობა ვერ იქნება ცარიელი.',
    'in' => 'არჩეული :attribute არასწორია.',
    'in_array' => ':attribute ველი უნდა არსებობდეს :other-ში.',
    'integer' => ':attribute ველი უნდა იყოს მთელი რიცხვი.',
    'ip' => ':attribute ველი უნდა იყოს სწორი IP მისამართი.',
    'ipv4' => ':attribute ველი უნდა იყოს სწორი IPv4 მისამართი.',
    'ipv6' => ':attribute ველი უნდა იყოს სწორი IPv6 მისამართი.',
    'json' => ':attribute ველი უნდა იყოს სწორი JSON სტრიქონი.',
    'list' => ':attribute ველი უნდა იყოს სია.',
    'lowercase' => ':attribute ველი უნდა იყოს პატარა ასოებით.',
    'lt' => [
        'array' => ':attribute ველში ელემენტების რაოდენობა უნდა იყოს ნაკლები ვიდრე :value.',
        'file' => ':attribute ველი უნდა იყოს ნაკლები ვიდრე :value კილობაიტი.',
        'numeric' => ':attribute ველი უნდა იყოს ნაკლები ვიდრე :value.',
        'string' => ':attribute ველი უნდა შეიცავდეს ნაკლებ სიმბოლოს ვიდრე :value.',
    ],
    'lte' => [
        'array' => ':attribute ველში ელემენტების რაოდენობა არ უნდა აღემატებოდეს :value-ს.',
        'file' => ':attribute ველი უნდა იყოს არაუმეტეს :value კილობაიტის.',
        'numeric' => ':attribute ველი უნდა იყოს არაუმეტეს :value.',
        'string' => ':attribute ველი უნდა შეიცავდეს არაუმეტეს :value სიმბოლოს.',
    ],
    'mac_address' => ':attribute ველი უნდა შეიცავდეს სწორ MAC-მისამართს.',
    'max' => [
        'array' => ':attribute ველში ელემენტების რაოდენობა არ უნდა აღემატებოდეს :max-ს.',
        'file' => ':attribute ველი არ უნდა აღემატებოდეს :max კილობაიტს.',
        'numeric' => ':attribute ველი არ უნდა აღემატებოდეს :max-ს.',
        'string' => ':attribute ველი არ უნდა აღემატებოდეს :max სიმბოლოს.',
    ],
    'max_digits' => ':attribute ველი არ უნდა შეიცავდეს :max-ზე მეტ ციფრს.',
    'mimes' => ':attribute ველი უნდა იყოს შემდეგი ტიპის ფაილი: :values.',
    'mimetypes' => ':attribute ველი უნდა იყოს შემდეგი MIME ტიპის ფაილი: :values.',
    'min' => [
        'array' => ':attribute ველს უნდა ჰქონდეს სულ მცირე :min ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს მინიმუმ :min კილობაიტი.',
        'numeric' => ':attribute ველი უნდა იყოს არანაკლებ :min.',
        'string' => ':attribute ველი უნდა შეიცავდეს მინიმუმ :min სიმბოლოს.',
    ],
    'min_digits' => ':attribute ველი უნდა შეიცავდეს სულ მცირე :min ციფრს.',
    'missing' => ':attribute ველი უნდა იყოს არარსებული.',
    'missing_if' => ':attribute ველი უნდა იყოს არარსებული, როცა :other არის :value.',
    'missing_unless' => ':attribute ველი უნდა იყოს არარსებული, თუ :other არ არის :value.',
    'missing_with' => ':attribute ველი უნდა იყოს არარსებული, როცა :values არსებობს.',
    'missing_with_all' => ':attribute ველი უნდა იყოს არარსებული, როცა :values-ები არსებობს.',
    'multiple_of' => ':attribute ველი უნდა იყოს :value-ის ჯერადი.',
    'not_in' => 'არჩეული :attribute არასწორია.',
    'not_regex' => ':attribute ველის ფორმატი არასწორია.',
    'numeric' => ':attribute ველი უნდა იყოს რიცხვი.',
    'password' => [
        'letters' => ':attribute ველში უნდა არსებობდეს მინიმუმ ერთი ასო.',
        'mixed' => ':attribute ველში უნდა არსებობდეს მინიმუმ ერთი დიდი და ერთი პატარა ასო.',
        'numbers' => ':attribute ველში უნდა არსებობდეს მინიმუმ ერთი ციფრი.',
        'symbols' => ':attribute ველში უნდა არსებობდეს მინიმუმ ერთი სიმბოლო.',
        'uncompromised' => 'ნაპოვნია მონაცემთა გაჟონვისას გამოყენებული :attribute. გთხოვთ, აირჩიოთ სხვა :attribute.',
    ],
    'percent'       => 'ამორტიზაციის მინიმალური პროცენტი უნდა იყოს 0-იდან 100-მდე, როდესაც ამორტიზაციის ტიპი არის პროცენტული.',

    'present' => ':attribute ველი აუცილებელია.',
    'present_if' => ':attribute ველი აუცილებელია, როდესაც :other არის :value.',
    'present_unless' => ':attribute ველი აუცილებელია, თუკი :other არ არის :values შორის.',
    'present_with' => ':attribute ველი აუცილებელია, როდესაც :values არის მოცემული.',
    'present_with_all' => ':attribute ველი აუცილებელია, როდესაც :values არის მოცემული.',
    'prohibited' => ':attribute ველი აკრძალულია.',
    'prohibited_if' => ':attribute ველი აკრძალულია, როდესაც :other არის :value.',
    'prohibited_unless' => ':attribute ველი აკრძალულია, თუკი :other არ არის :values შორის.',
    'prohibits' => ':attribute ველი კრძალავს :other ველის არსებობას.',
    'regex' => ':attribute ველის ფორმატი არასწორია.',
    'required' => ':attribute ველი სავალდებულოა.',
    'required_array_keys' => ':attribute ველი უნდა შეიცავდეს შემდეგ ჩანაწერებს: :values.',
    'required_if' => ':attribute ველი სავალდებულოა, როდესაც :other არის :value.',
    'required_if_accepted' => ':attribute ველი სავალდებულოა, როდესაც :other მიიღებულია.',
    'required_if_declined' => ':attribute ველი სავალდებულოა, როდესაც :other უარყოფილია.',
    'required_unless' => ':attribute ველი სავალდებულოა, თუ არა :other არის :values შორის.',
    'required_with' => ':attribute ველი სავალდებულოა, როდესაც :values არის მოცემული.',
    'required_with_all' => ':attribute ველი სავალდებულოა, როდესაც :values არის მოცემული.',
    'required_without' => ':attribute ველი სავალდებულოა, როდესაც :values არ არის მოცემული.',
    'required_without_all' => ':attribute ველი სავალდებულოა, როდესაც არც ერთი :values არ არის მოცემული.',
    'same' => ':attribute ველი უნდა ემთხვეოდეს :other-ს.',
    'size' => [
        'array' => ':attribute ველში უნდა იყოს ზუსტად :size ელემენტი.',
        'file' => ':attribute ველის ზომა უნდა იყოს ზუსტად :size კილობაიტი.',
        'numeric' => ':attribute ველის მნიშვნელობა უნდა იყოს ზუსტად :size.',
        'string' => ':attribute ველში უნდა იყოს ზუსტად :size სიმბოლო.',
    ],
    'starts_with' => ':attribute ველი უნდა იწყებოდეს ერთ-ერთ შემდეგიდან: :values.',
    'string'               => ':attribute უნდა იყოს ტექსტური (სტრიქონი).',
    'two_column_unique_undeleted' => ':attribute უნდა იყოს უნიკალური ორივე ცხრილში: :table1 და :table2.',
    'unique_undeleted'     => ':attribute უნდა იყოს უნიკალური.',
    'non_circular'         => ':attribute არ უნდა გამოიწვიოს ციკლური რეფერენცია.',
    'not_array'            => ':attribute ველი არ შეიძლება იყოს მასივი.',
    'disallow_same_pwd_as_user_fields' => 'პაროლი არ შეიძლება ემთხვეოდეს მომხმარებლის სახელს.',
    'letters'              => 'პაროლი უნდა შეიცავდეს მინიმუმ ერთ ასოს.',
    'numbers'              => 'პაროლი უნდა შეიცავდეს მინიმუმ ერთ რიცხვს.',
    'case_diff'            => 'პაროლი უნდა შეიცავდეს დიდ და პატარა ასოებს.',
    'symbols'              => 'პაროლი უნდა შეიცავდეს სიმბოლოებს.',
    'timezone' => ':attribute ველი უნდა შეიცავდეს ვალიდურ დროის ზონას.',
    'unique' => ':attribute უკვე დაკავებულია.',
    'uploaded' => ':attribute-ის ატვირთვა ჩაიშალა.',
    'uppercase' => ':attribute ველი უნდა იყოს დიდი ასოებით.',
    'url' => ':attribute ველი უნდა იყოს ვალიდური URL მისამართი.',
    'ulid' => ':attribute ველი უნდა იყოს ვალიდური ULID.',
    'uuid' => ':attribute ველი უნდა იყოს ვალიდური UUID.',
    'fmcs_location' => 'ადმინისტრატორის პარამეტრებში ჩართულია კომპანიის მრავალმხრივი მხარდაჭერა და ლოკაციის გამორკვევა, ხოლო არჩეული ლოკაცია და კომპანია ერთმანეთთან შეუთავსებელია.',


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

    'email_array'      => 'ერთზე მეტი ელ.ფოსტის მისამართი არის არავალიდური.',
    'checkboxes'           => ':attribute ველი შეიცავს არასწორ არჩევანს.',
    'radio_buttons'        => ':attribute არ არის ვალიდური.',
    
    'custom' => [
        'alpha_space' => ':attribute ველი შეიცავს დაუშვებელ სიმბოლოს.',

        'hashed_pass'      => 'თქვენი მიმდინარე პაროლი არასწორია.',
        'dumbpwd'          => 'ეს პაროლი ძალიან ხშირია და დაუშვებელია.',
        'statuslabel_type' => 'საჭიროა ვალიდური სტატუსის ეტიკეტის ტიპის არჩევა.',
        'custom_field_not_found'          => 'ეს ველი არ არსებობს. გთხოვთ გადაამოწმოთ "Custom" ველების სახელები.',
        'custom_field_not_found_on_model' => 'ეს ველი არსებობს, თუმცა ამ ინვენტარის მოდელის ველების ნაკრებში ხელმისაწვდომი არ არის.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD',
        'last_audit_date.date_format'   =>  ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD',
        'termination_date.date_format'  =>  ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD',
        'expected_checkin.date_format'  =>  ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD',
        'start_date.date_format'        =>  ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD',
        'end_date.date_format'          =>  ':ველი უნდა იყოს ვალიდური თარიღის ფორმატში YYYY-MM-DD',
        'invalid_value_in_field' => 'ველში შეტანილია არასწორი მნიშვნელობა',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (შერეული რეგისტრი) დიდი ალბათობით არ იმუშავებს. ამის ნაცვლად უნდა გამოიყენოთ <code>samaccountname</code> (პატარა ასოებით).'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> არ არის ვალიდური ავტორიზაციის ფილტრი. სავარაუდოდ უნდა გამოიყენოთ <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'ეს მნიშვნელობა, სავარაუდოდ, არ უნდა იყოს მოცულული ფრჩხილებში.'],

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
        'invalid_value_in_field' => 'ველში მოცემულია არასწორი მნიშვნელობა',
        'required' => 'ველი აუცილებელია',
        'email' => 'გთხოვთ, შეიყვანოთ ვალიდური ელექტრონული ფოსტის მისამართი',
    ],


];
