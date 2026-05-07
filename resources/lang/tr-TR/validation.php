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

    'accepted' => 'Nitelik hanesi kabul edilmelidir.',
    'accepted_if' => ':other alanı :value olduğunda, :attribute alanı onaylanmalıdır',
    'active_url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'after' => ':attribute alanı, :date tarihinden sonraki bir tarih olmalıdır.',
    'after_or_equal' => ':attribute alanı, :date tarihinden sonra veya bu tarihle aynı bir tarih olmalıdır.',
    'alpha' => ':attribute alanı yalnızca harfler içermelidir.',
    'alpha_dash' => ':attribute alanı yalnızca harfler, rakamlar, tire ve alt çizgi karakterleri içermelidir.',
    'alpha_num' => ':attribute alanı yalnızca harf ve rakamlar içermelidir.',
    'array' => ':attribute alanı bir dizi olmalıdır.',
    'ascii' => ':attribute alanı yalnızca tek baytlık alfasayısal karakterler ve semboller içermelidir.',
    'before' => ':attribute alanı, :date tarihinden önceki bir tarih olmalıdır.',
    'before_or_equal' => ':attribute alanı, :date tarihinden önceki veya bu tarihle aynı bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute alanı :min ve :max öğeleri arasında olmalıdır.',
        'file' => ':attribute alanı :min ve :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute alanı :min ve :max arasında olmalıdır.',
        'string' => ':attribute alanı :min ve :max karakterleri arasında olmalıdır.',
    ],
    'valid_regex' => 'Regular expression geçersiz.',
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'can' => ':attribute alanı yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute alanı onayı eşleşmiyor.',
    'contains' => ':attribute alanında gerekli bir değer eksik.',
    'current_password' => 'Şifre yanlış.',
    'date' => ':attribute alanı geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute alanı :date ile aynı tarih olmalıdır.',
    'date_format' => ':attribute alanı :format biçimiyle eşleşmelidir.',
    'decimal' => ':attribute alanı :decimal ondalık basamak sayısına sahip olmalıdır.',
    'declined' => ':attribute alanı reddedilmelidir.',
    'declined_if' => ':attribute alanı, :other :value olduğunda reddedilmelidir.',
    'different' => ':attribute alanı ve :other farklı olmalıdır.',
    'digits' => ':attribute alanı :digits rakamlarından oluşmalıdır.',
    'digits_between' => ':attribute alanı :min ve :max rakamları arasında olmalıdır.',
    'dimensions' => ':attribute alanı geçersiz resim boyutlarına sahip.',
    'distinct' => ': Öznitelik alanı yinelenen bir değere sahip.',
    'doesnt_end_with' => ':attribute alanı :values değerlerinden biriyle bitmemelidir.',
    'doesnt_start_with' => ':attribute alanı :values değerlerinden biriyle başlamamalıdır.',
    'email' => ':attribute alanı geçerli bir e-posta adresi olmalıdır.',
    'ends_with' => ':attribute alanı  :values değerlerinden biriyle bitmelidir.',
    'enum' => ':attribute geçersiz.',
    'exists' => ':attribute seçim geçersiz.',
    'extensions' => ':attribute alanı :values uzantılardan birine sahip olmalıdır',
    'file' => ':attribute alanı bir dosya olmalıdır.',
    'filled' => ': Attribute alanının bir değeri olmalıdır.',
    'gt' => [
        'array' => ':attribute alanı :value öğelerinden daha fazla öğeye sahip olmalıdır.',
        'file' => ':attribute alanı :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute alanı :value alanından büyük olmalıdır.',
        'string' => ':attribute alanı :value karakterden daha büyük olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute alanı :value öğesi veya daha fazlasını içermelidir.',
        'file' => ':attribute alanı :value kilobayttan büyük veya eşit olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden büyük veya eşit olmalıdır.',
        'string' => ':attribute alanı :value karakterinden büyük veya eşit olmalıdır.',
    ],
    'hex_color' => ':attribute alanı geçerli bir onaltılık renk olmalıdır.',
    'image' => ':attribute alanı bir resim olmalıdır.',
    'import_field_empty' => 'Bu değer için :alan adı boş olamaz.',
    'in' => ':attribute geçersiz.',
    'in_array' => ':attribute alanı :other içinde bulunmalıdır.',
    'integer' => ':attribute alanı bir tam sayı olmalıdır.',
    'ip' => ':attribute alanı geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute alanı geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute alanı geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute alanı geçerli bir JSON dizesi olmalıdır.',
    'list' => ':attribute alanı bir liste olmalıdır.',
    'lowercase' => ':attribute alanı küçük harflerden oluşmalıdır.',
    'lt' => [
        'array' => ':attribute alanı :value öğeden daha az içermelidir.',
        'file' => ':attribute alanı :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden küçük olmalıdır.',
        'string' => ':attribute alanı :value karakterden daha az olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute alanı :value öğeden fazla içermemelidir.',
        'file' => ':attribute alanı :value kilobayttan küçük veya eşit olmalıdır.',
        'numeric' => ':attribute alanı :value değerinden küçük veya eşit olmalıdır.',
        'string' => ':attribute alanı :value karakterden küçük veya eşit olmalıdır.',
    ],
    'mac_address' => ':attribute alanı geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute alanı :max öğeden fazla içermemelidir.',
        'file' => ':attribute alanı :max kilobayttan büyük olmamalıdır.',
        'numeric' => ':attribute alanı :max değerinden büyük olmamalıdır.',
        'string' => ':attribute alanı :max karakterden büyük olmamalıdır.',
    ],
    'max_digits' => ':attribute alanı :max basamaktan fazla olmamalıdır.',
    'mimes' => ':attribute alanı şu türlerden biri olmalıdır: :values.',
    'mimetypes' => ':attribute alanı şu dosya türlerinden biri olmalıdır: :values.',
    'min' => [
        'array' => ':attribute alanı en az :min öğe içermelidir.',
        'file' => ':attribute alanı en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute alanı en az :min olmalıdır.',
        'string' => ':attribute alanı en az :min karakter olmalıdır.',
    ],
    'min_digits' => ':attribute alanı en az :min basamak içermelidir.',
    'missing' => ':attribute alanı bulunmamalıdır.',
    'missing_if' => ':other alanı :value olduğunda :attribute alanı bulunmamalıdır.',
    'missing_unless' => ':other alanı :value olmadıkça :attribute alanı bulunmamalıdır.',
    'missing_with' => ':values mevcut olduğunda :attribute alanı bulunmamalıdır.',
    'missing_with_all' => ':values mevcut olduğunda :attribute alanı bulunmamalıdır.',
    'multiple_of' => ':attribute alanı :value değerinin katı olmalıdır.',
    'not_in' => ':attribute geçersiz.',
    'not_regex' => ':attribute alanının biçimi geçersiz.',
    'numeric' => ':attribute alanı bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute alanı en az bir harf içermelidir.',
        'mixed' => ':attribute alanı en az bir büyük harf ve bir küçük harf içermelidir.',
        'numbers' => ':attribute alanı en az bir sayı içermelidir.',
        'symbols' => ':attribute alanı en az bir sembol içermelidir.',
        'uncompromised' => 'Verilen :attribute bir veri sızıntısında ortaya çıkmıştır. Lütfen farklı bir :attribute seçin.',
    ],
    'percent' => 'Amortisman türü yüzde olduğunda, minimum amortisman 0 ile 100 arasında olmalıdır.',

    'present' => ': Attribute alanı bulunmalıdır.',
    'present_if' => ':other alanı :value olduğunda :attribute alanı mevcut olmalıdır.',
    'present_unless' => ':other alanı :value olmadıkça :attribute alanı mevcut olmalıdır.',
    'present_with' => ':values mevcut olduğunda :attribute alanı mevcut olmalıdır.',
    'present_with_all' => ':values mevcut olduğunda :attribute alanı mevcut olmalıdır.',
    'prohibited' => ':attribute alanı yasaklanmıştır.',
    'prohibited_if' => ':other alanı :value olduğunda :attribute alanı yasaklanmıştır.',
    'prohibited_unless' => ':other alanı :values içinde olmadıkça :attribute alanı yasaklanmıştır.',
    'prohibits' => ':attribute alanı mevcut olduğunda :other alanı bulunmamalıdır.',
    'regex' => ':attribute alanının biçimi geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_array_keys' => ':attribute alanı şu değerler için girişler içermelidir: :values.',
    'required_if' => ':attribute :other :value geçersiz.',
    'required_if_accepted' => ':other kabul edildiğinde :attribute alanı zorunludur.',
    'required_if_declined' => ':other reddedildiğinde :attribute alanı zorunludur.',
    'required_unless' => ': Attribute alanı, aşağıdaki koşullar haricinde: other is in: values.',
    'required_with' => ':attribute :values geçersiz.',
    'required_with_all' => ':values mevcut olduğunda :attribute alanı zorunludur.',
    'required_without' => ':attribute :values geçersiz.',
    'required_without_all' => ': Özellik alanının hiçbiri: değerleri mevcut değilse gereklidir.',
    'same' => ':attribute alanı :other ile eşleşmelidir.',
    'size' => [
        'array' => ':attribute alanı :size öğe içermelidir.',
        'file' => ':attribute alanı :size kilobayt olmalıdır.',
        'numeric' => ':attribute alanı :size olmalıdır.',
        'string' => ':attribute alanı :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute alanı aşağıdakilerden biri ile başlamalıdır: :values.',
    'string' => ': Özniteliği bir dize olmalıdır.',
    'two_column_unique_undeleted' => ':attribute :table1 ve :table2 genelinde benzersiz olmalıdır. ',
    'unique_undeleted' => ':attribute benzersiz olmalıdır.',
    'non_circular' => ':attribute döngüsel bir başvuru oluşturmamalıdır.',
    'not_array' => ':attribute bir dizi olamaz.',
    'disallow_same_pwd_as_user_fields' => 'Şifre kullanıcı adı ile aynı olamaz.',
    'letters' => 'Şifre en az bir harf içermelidir.',
    'numbers' => 'Şifre en az bir rakam içermelidir.',
    'case_diff' => 'Şifre hem büyük hem küçük harf içermelidir.',
    'symbols' => 'Şifre sembol içermelidir.',
    'timezone' => ':attribute alanı geçerli bir saat dilimi olmalıdır.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ': Özniteliği yüklenemedi.',
    'uppercase' => ':attribute alanı büyük harflerle olmalıdır.',
    'url' => ':attribute alanı geçerli bir URL olmalıdır.',
    'ulid' => ':attribute alanı geçerli bir ULID olmalıdır.',
    'uuid' => ':attribute alanı geçerli bir UUID olmalıdır.',
    'fmcs_location' => 'Tam çoklu şirket desteği ve konum kapsamı Yönetici Ayarları’nda etkinleştirilmiştir ve seçilen konum ile seçilen şirket birbiriyle uyumlu değildir.',
    'is_unique_across_company_and_location' => ':attribute, seçilen şirket ve konum içinde benzersiz olmalıdır.',

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

    'email_array' => 'Bir veya daha fazla e-posta adresi geçersiz.',
    'checkboxes' => ':attribute geçersiz seçenekler içeriyor.',
    'radio_buttons' => ':attribute geçersiz.',

    'custom' => [
        'alpha_space' => ': Attribute alanı, izin verilmeyen bir karakter içeriyor.',

        'hashed_pass' => 'Geçerli şifre yanlış',
        'dumbpwd' => 'Bu şifre çok yaygındır.',
        'statuslabel_type' => 'Geçerli bir durum etiketi türü seçmelisiniz',
        'custom_field_not_found' => 'Bu alan mevcut görünmüyor, lütfen özel alan adlarınızı kontrol edin.',
        'custom_field_not_found_on_model' => 'Bu alan mevcut görünüyor, ancak bu Varlık Modelinin alan kümesinde bulunmuyor.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format' => ':attribute YYYY-MM-DD tarih formatında olmalıdır',
        'last_audit_date.date_format' => ':attribute YYYY-MM-DD hh:mm:ss tarih formatında olmalıdır',
        'expiration_date.date_format' => ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'termination_date.date_format' => ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'expected_checkin.date_format' => ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'start_date.date_format' => ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'end_date.date_format' => ':attribute YYYY-MM-DD şeklinde geçerli bir tarih formatında olmalıdır',
        'invalid_value_in_field' => 'Bu alana geçersiz bir değer dahil edildi',

        'ldap_username_field' => [
            'not_in' => '<code>sAMAccountName</code> (karışık büyük/küçük harf) büyük olasılıkla çalışmayacaktır. Bunun yerine <code>samaccountname</code> (küçük harf) kullanmalısınız.',
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> muhtemelen geçerli bir kimlik doğrulama filtresi değildir. Muhtemelen <code>uid=</code> kullanmak istiyorsunuz '],
        'ldap_filter' => ['regex' => 'Bu değer muhtemelen parantez içine alınmamalıdır.'],

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
        'serials.*' => 'Seri Numarası',
        'asset_tags.*' => 'Demirbaş Etiketi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic Validation Messages - we use these in the jquery validation where we don't have
    | access to the :attribute
    |--------------------------------------------------------------------------
    */

    'generic' => [
        'invalid_value_in_field' => 'Bu alana geçersiz bir değer dahil edildi',
        'required' => 'Bu alan zorunludur',
        'email' => 'Lütfen geçerli bir e-posta adresi girin',
    ],

];
