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

    'accepted' => 'يجب قبول الحقل :attribute.',
    'accepted_if' => 'يجب قبول الحقل :attribute عندما تكون قيمة :other هي :value.',
    'active_url' => 'يجب أن يحتوي الحقل :attribute على رابط URL صالح.',
    'after' => 'يجب أن يكون تاريخ الحقل :attribute بعد :date.',
    'after_or_equal' => 'يجب أن يكون تاريخ الحقل :attribute في :date أو بعده.',
    'alpha' => 'يجب أن يحتوي الحقل :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي الحقل :attribute على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي الحقل :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون الحقل :attribute مصفوفة.',
    'ascii' => 'يجب أن يحتوي الحقل :attribute على أحرف وأرقام ورموز أحادية البايت فقط.',
    'before' => 'يجب أن يكون تاريخ الحقل :attribute قبل :date.',
    'before_or_equal' => 'يجب أن يكون تاريخ الحقل :attribute في :date أو قبله.',
    'between' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على عدد عناصر بين :min و :max.',
        'file' => 'يجب أن يكون حجم الحقل :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute بين :min و :max.',
        'string' => 'يجب أن يتراوح طول الحقل :attribute بين :min و :max أحرف.',
    ],
    'valid_regex' => 'التعبير النمطي غير صالح.',
    'boolean' => 'يجب أن يكون حقل السمة صحيحا أو خاطئا.',
    'can' => 'يحتوي الحقل :attribute على قيمة غير مصرح بها.',
    'confirmed' => 'تأكيد الحقل :attribute غير مطابق.',
    'contains' => 'يفتقد الحقل :attribute إلى قيمة مطلوبة.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'يجب أن يحتوي الحقل :attribute على تاريخ صالح.',
    'date_equals' => 'يجب أن يساوي تاريخ الحقل :attribute التاريخ :date.',
    'date_format' => 'يجب أن يتطابق تنسيق الحقل :attribute مع :format.',
    'decimal' => 'يجب أن يحتوي الحقل :attribute على :decimal من المنازل العشرية.',
    'declined' => 'يجب رفض الحقل :attribute.',
    'declined_if' => 'يجب رفض الحقل :attribute عندما تكون قيمة :other هي :value.',
    'different' => 'يجب أن تختلف قيمة الحقل :attribute عن :other.',
    'digits' => 'يجب أن يتكون الحقل :attribute من :digits أرقام.',
    'digits_between' => 'يجب أن يتراوح عدد أرقام الحقل :attribute بين :min و :max.',
    'dimensions' => 'أبعاد الصورة في الحقل :attribute غير صالحة.',
    'distinct' => 'يحتوي :attribute على قيمة مكررة.',
    'doesnt_end_with' => 'يجب ألا ينتهي الحقل :attribute بأي من القيم التالية: :values.',
    'doesnt_start_with' => 'يجب ألا يبدأ الحقل :attribute بأي من القيم التالية: :values.',
    'email' => 'يجب أن يحتوي الحقل :attribute على عنوان بريد إلكتروني صالح.',
    'ends_with' => 'يجب أن ينتهي الحقل :attribute بإحدى القيم التالية: :values.',
    'enum' => ':attribute المحدد غير صالح.',
    'exists' => ':attribute المحدد غير صالح.',
    'extensions' => 'يجب أن يكون امتداد الملف في الحقل :attribute أحد الامتدادات التالية: :values.',
    'file' => 'يجب أن يكون الحقل :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي :attribute على قيمة.',
    'gt' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على أكثر من :value عناصر.',
        'file' => 'يجب أن يزيد حجم الحقل :attribute على :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أكبر من :value.',
        'string' => 'يجب أن يزيد طول الحقل :attribute على :value أحرف.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على :value عناصر على الأقل.',
        'file' => 'يجب ألا يقل حجم الحقل :attribute عن :value كيلوبايت.',
        'numeric' => 'يجب ألا تقل قيمة الحقل :attribute عن :value.',
        'string' => 'يجب ألا يقل طول الحقل :attribute عن :value أحرف.',
    ],
    'hex_color' => 'يجب أن يحتوي الحقل :attribute على رمز لون سداسي عشري صالح.',
    'image' => 'يجب أن يكون الحقل :attribute صورة.',
    'import_field_empty' => 'لا يمكن أن تكون قيمة :fieldname فارغة.',
    'in' => ':attribute المحدد غير صالح.',
    'in_array' => 'يجب أن تكون قيمة الحقل :attribute موجودة في :other.',
    'integer' => 'يجب أن يكون الحقل :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يحتوي الحقل :attribute على عنوان IP صالح.',
    'ipv4' => 'يجب أن يحتوي الحقل :attribute على عنوان IPv4 صالح.',
    'ipv6' => 'يجب أن يحتوي الحقل :attribute على عنوان IPv6 صالح.',
    'json' => 'يجب أن يحتوي الحقل :attribute على نص JSON صالح.',
    'list' => 'يجب أن يكون الحقل :attribute قائمة.',
    'lowercase' => 'يجب أن تكون أحرف الحقل :attribute صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على أقل من :value عناصر.',
        'file' => 'يجب أن يقل حجم الحقل :attribute عن :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أقل من :value.',
        'string' => 'يجب أن يقل طول الحقل :attribute عن :value أحرف.',
    ],
    'lte' => [
        'array' => 'يجب ألا يزيد عدد عناصر الحقل :attribute على :value.',
        'file' => 'يجب ألا يزيد حجم الحقل :attribute على :value كيلوبايت.',
        'numeric' => 'يجب ألا تزيد قيمة الحقل :attribute على :value.',
        'string' => 'يجب ألا يزيد طول الحقل :attribute على :value أحرف.',
    ],
    'mac_address' => 'يجب أن يحتوي الحقل :attribute على عنوان MAC صالح.',
    'max' => [
        'array' => 'يجب ألا يزيد عدد عناصر الحقل :attribute على :max.',
        'file' => 'يجب ألا يزيد حجم الحقل :attribute على :max كيلوبايت.',
        'numeric' => 'يجب ألا تزيد قيمة الحقل :attribute على :max.',
        'string' => 'يجب ألا يزيد طول الحقل :attribute على :max أحرف.',
    ],
    'max_digits' => 'يجب ألا يزيد عدد أرقام الحقل :attribute على :max.',
    'mimes' => 'يجب أن يكون نوع الملف في الحقل :attribute أحد الأنواع التالية: :values.',
    'mimetypes' => 'يجب أن يكون نوع الملف في الحقل :attribute أحد الأنواع التالية: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على :min عناصر على الأقل.',
        'file' => 'يجب ألا يقل حجم الحقل :attribute عن :min كيلوبايت.',
        'numeric' => 'يجب ألا تقل قيمة الحقل :attribute عن :min.',
        'string' => 'يجب ألا يقل طول الحقل :attribute عن :min أحرف.',
    ],
    'min_digits' => 'يجب ألا يقل عدد أرقام الحقل :attribute عن :min.',
    'missing' => 'يجب ألا يكون الحقل :attribute موجودًا.',
    'missing_if' => 'يجب ألا يكون الحقل :attribute موجودًا عندما تكون قيمة :other هي :value.',
    'missing_unless' => 'يجب ألا يكون الحقل :attribute موجودًا إلا عندما تكون قيمة :other هي :value.',
    'missing_with' => 'يجب ألا يكون الحقل :attribute موجودًا عند وجود :values.',
    'missing_with_all' => 'يجب ألا يكون الحقل :attribute موجودًا عند وجود :values.',
    'multiple_of' => 'يجب أن تكون قيمة الحقل :attribute من مضاعفات :value.',
    'not_in' => ':attribute المحدد غير صالح.',
    'not_regex' => 'تنسيق الحقل :attribute غير صالح.',
    'numeric' => 'يجب أن يكون الحقل :attribute رقمًا.',
    'password' => [
        'letters' => 'يجب أن يحتوي الحقل :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي الحقل :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي الحقل :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي الحقل :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهر :attribute في تسريب بيانات سابق. اختر قيمة أخرى للحقل :attribute.',
    ],
    'percent' => 'يجب أن تتراوح القيمة الدنيا للإهلاك بين 0 و100 عندما يكون نوع الإهلاك نسبة مئوية.',

    'present' => 'يجب أن يكون :attribute موجود.',
    'present_if' => 'يجب وجود الحقل :attribute عندما تكون قيمة :other هي :value.',
    'present_unless' => 'يجب وجود الحقل :attribute ما لم تكن قيمة :other هي :value.',
    'present_with' => 'يجب وجود الحقل :attribute عند وجود :values.',
    'present_with_all' => 'يجب وجود الحقل :attribute عند وجود :values.',
    'prohibited' => 'يُحظر وجود الحقل :attribute.',
    'prohibited_if' => 'يُحظر وجود الحقل :attribute عندما تكون قيمة :other هي :value.',
    'prohibited_unless' => 'يُحظر وجود الحقل :attribute ما لم تكن قيمة :other ضمن :values.',
    'prohibits' => 'يمنع وجود الحقل :attribute وجود الحقل :other.',
    'regex' => 'تنسيق الحقل :attribute غير صالح.',
    'required' => 'الحقل :attribute اجباري.',
    'required_array_keys' => 'يجب أن يتضمن الحقل :attribute إدخالات للمفاتيح التالية: :values.',
    'required_if' => 'الحقل :attribute اجباري عندما يكون :other يساوي :value.',
    'required_if_accepted' => 'الحقل :attribute مطلوب عند قبول :other.',
    'required_if_declined' => 'الحقل :attribute مطلوب عند رفض :other.',
    'required_unless' => 'الحقل :attribute اجباري ما لم يكن :other ما بين :values.',
    'required_with' => 'الحقل :attribute اجباري عندما يكون :values موجودا.',
    'required_with_all' => 'الحقل :attribute مطلوب عند وجود :values.',
    'required_without' => 'الحقل :attribute اجباري عندما تكون :values غير موجودة.',
    'required_without_all' => 'الحقل :attribute اجباري عندما لا يكون اي من :values موجودة.',
    'same' => 'يجب أن تتطابق قيمة الحقل :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على :size عناصر.',
        'file' => 'يجب أن يكون حجم الحقل :attribute هو :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute هي :size.',
        'string' => 'يجب أن يبلغ طول الحقل :attribute عدد :size من الأحرف.',
    ],
    'starts_with' => 'يجب أن يبدأ الحقل :attribute بإحدى القيم التالية: :values.',
    'string' => 'يجب أن يكون :attribute عبارة عن سلسلة نصية.',
    'two_column_unique_undeleted' => ':attribute يجب أن يكون فريداً عبر :table1 و :table2. ',
    'unique_undeleted' => ':attribute يجب ان تكون فريدة.',
    'non_circular' => 'يجب ألا تنشئ السمة مرجعًا دائريًا.',
    'parent_must_be_top_level' => 'يجب أن يكون :attribute المحدد عنصرًا رئيسيًا. يُسمح بمستوى واحد فقط من التداخل.',
    'must_have_no_children' => 'لهذا العنصر عناصر فرعية بالفعل، لذا لا يمكن تعيين عنصر رئيسي له.',
    'parent_within_scope' => 'يقع :attribute المحدد خارج نطاق صلاحياتك.',
    'not_array' => ':attribute لا يمكن أن يكون مصفوف.',
    'disallow_same_pwd_as_user_fields' => 'كلمة المرور لا يمكن أن تكون نفس اسم المستخدم.',
    'letters' => 'يجب أن تحتوي كلمة المرور على حرف واحد على الأقل.',
    'numbers' => 'يجب أن تحتوي كلمة المرور على رقم واحد على الأقل.',
    'case_diff' => 'كلمة المرور يجب أن تستخدم حالة مختلطة.',
    'symbols' => 'يجب أن تحتوي كلمة المرور على رموز.',
    'timezone' => 'يجب أن يحتوي الحقل :attribute على منطقة زمنية صالحة.',
    'unique' => 'لقد تم أخذ :attribute مسبقا.',
    'uploaded' => 'لقد فشل تحميل :attribute.',
    'uppercase' => 'يجب أن تكون أحرف الحقل :attribute كبيرة.',
    'url' => 'يجب أن يحتوي الحقل :attribute على رابط URL صالح.',
    'external_url' => 'يجب أن يحتوي الحقل :attribute على رابط URL خارجي صالح (http:// أو https://) لا يشير إلى عنوان خاص أو محلي.',
    'asset_tag_pattern_placeholder' => 'يجب أن يتضمن نمط رمز الأصل عنصرًا نائبًا واحدًا على الأقل من :placeholders، وإلا فستحصل جميع الأصول المزامنة على الرمز نفسه، ولن يُحفظ سوى الأصل الأول.',
    'custom_http_extras_json' => [
        'not_json' => 'يجب أن تكون الإضافات المخصصة بتنسيق JSON صالح. خطأ التحليل: :error',
        'not_array' => 'يجب أن تكون الإضافات المخصصة مصفوفة JSON (تبدأ بـ [ وتنتهي بـ ]).',
        'entry_not_object' => 'يجب أن يكون الإدخال رقم #:index في الإضافات المخصصة كائن JSON يحتوي على الحقول key وlabel وpath.',
        'missing_field' => 'يفتقد الإدخال رقم #:index في الإضافات المخصصة إلى الحقل المطلوب ":field".',
    ],
    'ulid' => 'يجب أن يحتوي الحقل :attribute على معرّف ULID صالح.',
    'uuid' => 'يجب أن يحتوي الحقل :attribute على معرّف UUID صالح.',
    'valid_css_color' => 'يجب أن يحتوي الحقل :attribute على لون CSS صالح (hex أو rgb أو rgba أو hsl أو hsla).',
    'eloquent_model_class' => 'يجب أن يحتوي الحقل :attribute على اسم الصنف المؤهل بالكامل لطراز Eloquent.',
    'fmcs_company' => 'الحقل :attribute مطلوب لأن دعم الشركات المتعددة الكامل مفعّل، ولا يُسمح بالعناصر غير التابعة لأي شركة.',
    'fmcs_location' => 'الموقع ":location" يتبع الشركة :location_company، وهي لا تطابق الشركة المحددة.',
    'is_unique_across_company_and_location' => 'يجب أن تكون قيمة :attribute فريدة ضمن الشركة والموقع المحددين.',

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

    'email_array' => 'عنوان بريد إلكتروني واحد أو أكثر غير صالح.',
    'checkboxes' => ':attribute يحتوي على خيارات غير صالحة.',
    'radio_buttons' => ':attribute غير صالح.',
    'image_file_too_large' => 'يجب ألا يتجاوز حجم الحقل :attribute القيمة :max.',

    'custom' => [
        'alpha_space' => 'يحتوي الحقل :attribute على حرف غير مسموح به.',

        'hashed_pass' => 'كلمة المرور الحالية غير صحيحة',
        'dumbpwd' => 'كلمة المرور هذه شائعة جدا.',
        'statuslabel_type' => 'يجب تحديد نوع تسمية حالة صالح',
        'custom_field_not_found' => 'يبدو أن هذا الحقل غير موجود. تحقق من أسماء الحقول المخصصة.',
        'custom_field_not_found_on_model' => 'يبدو أن هذا الحقل موجود، لكنه غير متاح ضمن مجموعة حقول طراز الأصل هذا.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => 'يجب أن يكون :attribute تاريخ صالح بتنسيق YYY-MM-DD',
        'last_audit_date.date_format' => 'يجب أن يكون :attribute تاريخًا صحيحًا في تنسيق YYY-MM-DD hh:mm:ss',
        'expiration_date.date_format' => 'يجب أن يكون :attribute تاريخ صالح بتنسيق YYY-MM-DD',
        'termination_date.date_format' => 'يجب أن يكون :attribute تاريخ صالح بتنسيق YYY-MM-DD',
        'expected_checkin.date_format' => 'يجب أن يكون :attribute تاريخ صالح بتنسيق YYY-MM-DD',
        'start_date.date_format' => 'يجب أن يكون :attribute تاريخ صالح بتنسيق YYY-MM-DD',
        'end_date.date_format' => 'يجب أن يكون :attribute تاريخ صالح بتنسيق YYY-MM-DD',
        'invalid_value_in_field' => 'القيمة غير صالحة المدرجة في هذا الحقل',

        'ldap_username_field' => [
            'not_in' => 'قد لا يعمل <code>sAMAccountName</code> بسبب اختلاف حالة الأحرف. استخدم <code>samaccountname</code> بالأحرف الصغيرة بدلًا منه.',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> is probably not a valid auth filter. You probably want <code>uid=</code> '],
        'ldap_filter' => ['regex' => 'This value should probably not be wrapped in parentheses.'],

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
        'serials.*' => 'الرقم التسلسلي',
        'asset_tags.*' => 'رمز الأصل',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'القيمة غير صالحة المدرجة في هذا الحقل',
        'required' => 'هذا الحقل مطلوب',
        'email' => 'أدخل عنوان بريد إلكتروني صالحًا',
    ],

];
