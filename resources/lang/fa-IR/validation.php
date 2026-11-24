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

    'accepted' => 'فیلد :attribute باید پذیرفته شود.',
    'accepted_if' => 'فیلد :attribute هنگامی که :other برابر با :value است باید پذیرفته شود.',
    'active_url' => 'فیلد :attribute باید یک آدرس اینترنتی معتبر باشد.',
    'after' => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'after_or_equal' => 'فیلد :attribute باید تاریخی بعد از یا مساوی با :date باشد.',
    'alpha' => 'فیلد :attribute فقط باید شامل حروف باشد.',
    'alpha_dash' => 'فیلد :attribute فقط باید شامل حروف، اعداد، خط فاصله و زیرخط باشد.',
    'alpha_num' => 'فیلد :attribute فقط باید شامل حروف و اعداد باشد.',
    'array' => 'فیلد :attribute باید یک آرایه باشد.',
    'ascii' => 'فیلد :attribute فقط باید شامل نویسه‌های تک‌بایتی الفبایی-عددی و نمادها باشد.',
    'before' => 'فیلد :attribute باید تاریخی قبل از :date باشد.',
    'before_or_equal' => 'فیلد :attribute باید تاریخی قبل از یا مساوی با :date باشد.',
    'between' => [
        'array' => 'فیلد :attribute باید بین :min و :max مورد داشته باشد.',
        'file' => 'فیلد :attribute باید بین :min و :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بین :min و :max باشد.',
        'string' => 'فیلد :attribute باید بین :min و :max نویسه باشد.',
    ],
    'valid_regex' => 'عبارت منظم نامعتبر است.',
    'boolean' => 'فیلد attribute باید درست یا غلط باشد.',
    'can' => 'فیلد :attribute شامل یک مقدار غیرمجاز است.',
    'confirmed' => 'تأییدیه فیلد :attribute مطابقت ندارد.',
    'contains' => 'فیلد :attribute فاقد یک مقدار الزامی است.',
    'current_password' => 'رمز عبور نادرست است.',
    'date' => 'فیلد :attribute باید یک تاریخ معتبر باشد.',
    'date_equals' => 'فیلد :attribute باید تاریخی برابر با :date باشد.',
    'date_format' => 'فیلد :attribute باید با فرمت :format مطابقت داشته باشد.',
    'decimal' => 'فیلد :attribute باید دارای :decimal رقم اعشار باشد.',
    'declined' => 'فیلد :attribute باید رد شود.',
    'declined_if' => 'فیلد :attribute هنگامی که :other برابر با :value است باید رد شود.',
    'different' => 'فیلد :attribute و :other باید متفاوت باشند.',
    'digits' => 'فیلد :attribute باید :digits رقم باشد.',
    'digits_between' => 'فیلد :attribute باید بین :min و :max رقم باشد.',
    'dimensions' => 'ابعاد تصویر فیلد :attribute نامعتبر است.',
    'distinct' => 'فیلد attribute دارای مقدار تکراری است.',
    'doesnt_end_with' => 'فیلد :attribute نباید با یکی از موارد زیر خاتمه یابد: :values.',
    'doesnt_start_with' => 'فیلد :attribute نباید با یکی از موارد زیر شروع شود: :values.',
    'email' => 'فیلد :attribute باید یک آدرس ایمیل معتبر باشد.',
    'ends_with' => 'فیلد :attribute باید با یکی از موارد زیر خاتمه یابد: :values.',
    'enum' => 'ویژگی انتخاب شده نامعتبر است.',
    'exists' => 'ویژگی انتخاب شده نامعتبر است.',
    'extensions' => 'فیلد :attribute باید دارای یکی از پسوندهای زیر باشد: :values.',
    'file' => 'فیلد :attribute باید یک فایل باشد.',
    'filled' => 'فیلد attribute باید مقدار داشته باشد.',
    'gt' => [
        'array' => 'فیلد :attribute باید بیش از :value مورد داشته باشد.',
        'file' => 'فیلد :attribute باید بزرگتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بزرگتر از :value باشد.',
        'string' => 'فیلد :attribute باید بیشتر از :value نویسه باشد.',
    ],
    'gte' => [
        'array' => 'فیلد :attribute باید حداقل :value مورد داشته باشد.',
        'file' => 'فیلد :attribute باید بزرگتر یا مساوی با :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید بزرگتر یا مساوی با :value باشد.',
        'string' => 'فیلد :attribute باید بیشتر یا مساوی با :value نویسه باشد.',
    ],
    'hex_color' => 'فیلد :attribute باید یک رنگ هگزادسیمال معتبر باشد.',
    'image' => 'فیلد :attribute باید یک تصویر باشد.',
    'import_field_empty' => 'مقدار :fieldname نمی‌تواند خالی باشد.',
    'in' => 'ویژگی انتخاب شده نامعتبر است.',
    'in_array' => 'فیلد :attribute باید در :other وجود داشته باشد.',
    'integer' => 'فیلد :attribute باید یک عدد صحیح باشد.',
    'ip' => 'فیلد :attribute باید یک آدرس IP معتبر باشد.',
    'ipv4' => 'فیلد :attribute باید یک آدرس IPv4 معتبر باشد.',
    'ipv6' => 'فیلد :attribute باید یک آدرس IPv6 معتبر باشد.',
    'json' => 'فیلد :attribute باید یک رشته معتبر JSON باشد.',
    'list' => 'فیلد :attribute باید یک لیست باشد.',
    'lowercase' => 'فیلد :attribute باید با حروف کوچک باشد.',
    'lt' => [
        'array' => 'فیلد :attribute باید کمتر از :value مورد داشته باشد.',
        'file' => 'فیلد :attribute باید کمتر از :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید کمتر از :value باشد.',
        'string' => 'فیلد :attribute باید کمتر از :value نویسه باشد.',
    ],
    'lte' => [
        'array' => 'فیلد :attribute نباید بیشتر از :value مورد داشته باشد.',
        'file' => 'فیلد :attribute باید کمتر یا مساوی با :value کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید کمتر یا مساوی با :value باشد.',
        'string' => 'فیلد :attribute باید کمتر یا مساوی با :value نویسه باشد.',
    ],
    'mac_address' => 'فیلد :attribute باید یک آدرس MAC معتبر باشد.',
    'max' => [
        'array' => 'فیلد :attribute نباید بیش از :max مورد داشته باشد.',
        'file' => 'فیلد :attribute نباید بزرگتر از :max کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute نباید بزرگتر از :max باشد.',
        'string' => 'فیلد :attribute نباید بیشتر از :max نویسه باشد.',
    ],
    'max_digits' => 'فیلد :attribute نباید بیش از :max رقم داشته باشد.',
    'mimes' => 'فیلد :attribute باید فایلی از نوع :values باشد.',
    'mimetypes' => 'فیلد :attribute باید فایلی از نوع :values باشد.',
    'min' => [
        'array' => 'فیلد :attribute باید حداقل :min مورد داشته باشد.',
        'file' => 'فیلد :attribute باید حداقل :min کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید حداقل :min باشد.',
        'string' => 'فیلد :attribute باید حداقل :min نویسه باشد.',
    ],
    'min_digits' => 'فیلد :attribute باید حداقل :min رقم داشته باشد.',
    'missing' => 'فیلد :attribute باید وجود نداشته باشد.',
    'missing_if' => 'فیلد :attribute هنگامی که :other برابر با :value است باید وجود نداشته باشد.',
    'missing_unless' => 'فیلد :attribute باید وجود نداشته باشد مگر اینکه :other برابر با :value باشد.',
    'missing_with' => 'فیلد :attribute هنگامی که :values وجود دارد باید وجود نداشته باشد.',
    'missing_with_all' => 'فیلد :attribute هنگامی که :values وجود دارند باید وجود نداشته باشد.',
    'multiple_of' => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in' => 'ویژگی انتخاب شده نامعتبر است.',
    'not_regex' => 'فرمت فیلد :attribute نامعتبر است.',
    'numeric' => 'فیلد :attribute باید یک عدد باشد.',
    'password' => [
        'letters' => 'فیلد :attribute باید حداقل شامل یک حرف باشد.',
        'mixed' => 'فیلد :attribute باید حداقل شامل یک حرف بزرگ و یک حرف کوچک باشد.',
        'numbers' => 'فیلد :attribute باید حداقل شامل یک عدد باشد.',
        'symbols' => 'فیلد :attribute باید حداقل شامل یک نماد باشد.',
        'uncompromised' => ':attribute وارد شده در یک نشت اطلاعاتی دیده شده است. لطفاً :attribute متفاوتی انتخاب کنید.',
    ],
    'percent' => 'حداقل استهلاک زمانی که نوع استهلاک درصدی است باید بین ۰ تا ۱۰۰ باشد.',

    'present' => 'فیلد attribute باید باشد.',
    'present_if' => 'فیلد :attribute هنگامی که :other برابر با :value است باید ارائه شده باشد.',
    'present_unless' => 'فیلد :attribute باید ارائه شده باشد مگر اینکه :other برابر با :value باشد.',
    'present_with' => 'فیلد :attribute هنگامی که :values وجود دارد باید ارائه شده باشد.',
    'present_with_all' => 'فیلد :attribute هنگامی که :values وجود دارند باید ارائه شده باشد.',
    'prohibited' => 'فیلد :attribute ممنوع است.',
    'prohibited_if' => 'فیلد :attribute هنگامی که :other برابر با :value است ممنوع است.',
    'prohibited_unless' => 'فیلد :attribute ممنوع است مگر اینکه :other در :values باشد.',
    'prohibits' => 'فیلد :attribute حضور :other را منع می‌کند.',
    'regex' => 'فرمت فیلد :attribute نامعتبر است.',
    'required' => 'فیلد ویژگی ضروری است.',
    'required_array_keys' => 'فیلد :attribute باید شامل ورودی‌هایی برای :values باشد.',
    'required_if' => 'فیلد ویژگی ضروری است، وقتی که دیگری ارزش است.',
    'required_if_accepted' => 'فیلد :attribute هنگامی که :other پذیرفته می‌شود الزامی است.',
    'required_if_declined' => 'فیلد :attribute هنگامی که :other رد می‌شود الزامی است.',
    'required_unless' => 'فیلد attribute: مورد نیاز است مگر اینکه: دیگر در: مقادیر باشد.',
    'required_with' => 'فیلد ویژگی ضروری است، وقتی که ارزش موجود باشد.',
    'required_with_all' => 'فیلد :attribute هنگامی که :values وجود دارند الزامی است.',
    'required_without' => 'فیلد ویژگی ضروری است، وقتی که ارزش ها حاضر نباشند.',
    'required_without_all' => 'فیلد attribute: وقتی که هیچ یک از: مقادیر وجود ندارد، مورد نیاز است.',
    'same' => 'فیلد :attribute باید با :other مطابقت داشته باشد.',
    'size' => [
        'array' => 'فیلد :attribute باید شامل :size مورد باشد.',
        'file' => 'فیلد :attribute باید :size کیلوبایت باشد.',
        'numeric' => 'فیلد :attribute باید :size باشد.',
        'string' => 'فیلد :attribute باید :size نویسه باشد.',
    ],
    'starts_with' => 'فیلد :attribute باید با یکی از موارد زیر شروع شود: :values.',
    'string' => 'attribute باید یک رشته باشد.',
    'two_column_unique_undeleted' => ':attribute باید در هر دو جدول :table1 و :table2 یکتا باشد. ',
    'unique_undeleted' => ': attribute باید منحصر به فرد باشد.',
    'non_circular' => 'ویژگی : نباید یک مرجع دایره ای ایجاد کند',
    'parent_must_be_top_level' => ':attribute انتخاب‌شده باید خودش یک مورد در سطح بالا باشد. تنها یک سطح تودرتو مجاز است.',
    'must_have_no_children' => 'این مورد از قبل فرزندانی دارد، بنابراین نمی‌توان والدی به آن اختصاص داد.',
    'parent_within_scope' => ':attribute انتخاب‌شده خارج از محدوده اختصاص‌یافته به شما است.',
    'not_array' => ':attribute نمی‌تواند یک آرایه باشد.',
    'disallow_same_pwd_as_user_fields' => 'رمز عبور نمی تواند همان آدرس ایمیل باشد.',
    'letters' => 'گذرواژه باید دارای حداقل یک رقم باشد.',
    'numbers' => 'گذرواژه باید دارای حداقل یک رقم باشد.',
    'case_diff' => 'رمز عبور باید از حروف مختلط استفاده کند.',
    'symbols' => 'رمز عبور نباید حاوی فضای خالی باشد.',
    'timezone' => 'فیلد :attribute باید یک منطقه زمانی معتبر باشد.',
    'unique' => 'ویژگی در حال حاضر گرفته شده است.',
    'uploaded' => 'ویژگی: attribute failed to upload.',
    'uppercase' => 'فیلد :attribute باید با حروف بزرگ باشد.',
    'url' => 'فیلد :attribute باید یک آدرس اینترنتی معتبر باشد.',
    'external_url' => 'فیلد :attribute باید یک URL خارجی معتبر (http:// یا https://) باشد که به یک آدرس خصوصی یا محلی اشاره نکند.',
    'asset_tag_pattern_placeholder' => 'الگوی برچسب دارایی باید حداقل شامل یکی از متغیرهای :placeholders باشد، در غیر این صورت به هر دارایی همگام‌شده برچسب یکسانی اختصاص می‌یابد و فقط اولین دارایی ذخیره خواهد شد.',
    'custom_http_extras_json' => [
        'not_json' => 'فیلدهای اضافی سفارشی باید JSON معتبر باشند. خطای تجزیه‌کننده: :error',
        'not_array' => 'فیلدهای اضافی سفارشی باید یک آرایه JSON باشند (شروع با [ و پایان با ]).',
        'entry_not_object' => 'ورودی شماره #:index فیلدهای اضافی سفارشی باید یک شیء JSON شامل فیلدهای key، label و path باشد.',
        'missing_field' => 'ورودی شماره #:index فیلدهای اضافی سفارشی فاقد فیلد الزامی ":field" است.',
    ],
    'ulid' => 'فیلد :attribute باید یک ULID معتبر باشد.',
    'uuid' => 'فیلد :attribute باید یک UUID معتبر باشد.',
    'valid_css_color' => 'فیلد :attribute باید یک رنگ معتبر CSS (مانند hex، rgb، rgba، hsl یا hsla) باشد.',
    'eloquent_model_class' => 'فیلد :attribute باید نام کامل کلاس (Fully-Qualified Class Name) یک مدل Eloquent باشد.',
    'fmcs_company' => 'فیلد :attribute الزامی است زیرا پشتیبانی کامل از چندشرکتی فعال بوده و کاربران شناور مجاز نیستند.',
    'fmcs_location' => 'مکان ":location" متعلق به :location_company است که با شرکت انتخاب‌شده مطابقت ندارد.',
    'fmcs_target_compat' => ':attribute متعلق به شرکتی نیست که این کاربر بتواند به آن گزارش دهد.',
    'is_unique_across_company_and_location' => ':attribute باید در شرکت و مکان انتخاب‌شده یکتا باشد.',

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

    'email_array' => 'یک یا بیش از یک آدرس ایمیل نامعتبر است.',
    'checkboxes' => ':attribute شامل گزینه‌های نامعتبر است.',
    'radio_buttons' => ':attribute نامعتبر است.',
    'image_file_too_large' => ':attribute نباید بزرگتر از :max باشد.',

    'custom' => [
        'alpha_space' => 'فیلد attribute شامل یک شخصیت است که مجاز نیست.',

        'hashed_pass' => 'رمز عبور فعلی شما اشتباه است',
        'dumbpwd' => 'این رمز عبور خیلی رایج است',
        'statuslabel_type' => 'شما باید نوع برچسب معتبر را انتخاب کنید',
        'custom_field_not_found' => 'به نظر می‌رسد این فیلد وجود ندارد، لطفاً نام فیلدهای سفارشی خود را دوباره بررسی کنید.',
        'custom_field_not_found_on_model' => 'به نظر می‌رسد این فیلد وجود دارد، اما در مجموعه فیلدهای این مدل دارایی در دسترس نیست.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD باشد',
        'last_audit_date.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD hh:mm:ss باشد',
        'expiration_date.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD باشد',
        'termination_date.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD باشد',
        'expected_checkin.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD باشد',
        'start_date.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD باشد',
        'end_date.date_format' => ':attribute باید یک تاریخ معتبر با فرمت YYYY-MM-DD باشد',
        'invalid_value_in_field' => 'مقدار نامعتبر در این فیلد وارد شده است',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code> (حروف کوچک و بزرگ ترکیبی) به احتمال زیاد کار نخواهد کرد. باید به جای آن از <code>samaccountname</code> (تمام حروف کوچک) استفاده کنید.',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> احتمالاً یک فیلتر احراز هویت معتبر نیست. احتمالاً منظور شما <code>uid=</code> است '],
        'ldap_filter' => ['regex' => 'این مقدار احتمالاً نباید داخل پرانتز قرار گیرد.'],

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
        'serials.*' => 'شماره سریال',
        'asset_tags.*' => 'برچسب دارایی
',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'مقدار نامعتبر در این فیلد وارد شده است',
        'required' => 'این فیلد الزامی است',
        'email' => 'لطفاً یک آدرس ایمیل معتبر وارد کنید',
    ],

];
