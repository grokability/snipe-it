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

    'accepted' => 'Isian kolom :attribute harus diterima.',
    'accepted_if' => 'Isian kolom :attribute harus diterima jika :other adalah :value.',
    'active_url' => 'Isian kolom :attribute harus berupa URL yang valid.',
    'after' => 'Isian kolom :attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => 'Isian kolom :attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => 'Isian kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Isian kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Isian kolom :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Kolom :attribute harus berupa array.',
    'ascii' => 'Kolom :attribute hanya boleh berisi karakter alfanumerik dan simbol satu byte.',
    'before' => 'Kolom :attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => 'Kolom :attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Isian kolom :attribute harus memiliki antara :min dan :max item.',
        'file' => 'Isian kolom :attribute harus antara :min dan :max kilobyte.',
        'numeric' => 'Isian kolom :attribute harus antara :min dan :max.',
        'string' => 'Panjang isian kolom :attribute harus antara :min dan :max karakter.',
    ],
    'valid_regex' => 'The regular expression is invalid.',
    'boolean' => 'Bidang atribut: harus benar atau salah.',
    'can' => 'Kolom :attribute berisi nilai yang tidak sah.',
    'confirmed' => 'Konfirmasi kolom :attribute tidak sesuai.',
    'contains' => 'Kolom :attribute belum diisi.',
    'current_password' => 'Kata sandi salah',
    'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
    'date_equals' => 'Kolom :attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => 'Kolom :attribute harus sesuai dengan format :format.',
    'decimal' => 'Kolom :attribute harus memiliki :decimal angka di belakang koma.',
    'declined' => 'Isian kolom :attribute harus ditolak.',
    'declined_if' => 'Isian kolom :attribute harus ditolak jika :other adalah :value.',
    'different' => 'Isian kolom :attribute dan :other harus berbeda.',
    'digits' => 'Isian kolom :attribute harus terdiri dari :digits digit.',
    'digits_between' => 'Isian kolom :attribute harus terdiri dari antara :min dan :max digit.',
    'dimensions' => 'Dimensi gambar pada kolom :attribute tidak valid.',
    'distinct' => 'Bidang atribut: memiliki nilai duplikat.',
    'doesnt_end_with' => 'Isian kolom :attribute tidak boleh diakhiri dengan salah satu dari berikut ini: :value.',
    'doesnt_start_with' => 'Isian kolom :attribute tidak boleh dimulai dengan salah satu dari berikut ini: :values.',
    'email' => 'Isian kolom :attribute harus alamat email yang valid.',
    'ends_with' => 'Isian kolom :attribute harus diakhiri dengan salah satu dari berikut: :values.',
    'enum' => ':attribute yang di pilih tidak benar.',
    'exists' => ':attribute yang di pilih tidak benar.',
    'extensions' => 'Isian kolom :attribute harus memiliki salah satu ekstensi berikut: :values.',
    'file' => 'Kolom :attribute harus berupa sebuah file.',
    'filled' => 'Bidang atribut: harus memiliki nilai.',
    'gt' => [
        'array' => 'Isian kolom :attribute harus berjumlah lebih dari :value item.',
        'file' => 'Ukuran isian kolom :attribute harus lebih besar dari :value kilobyte.',
        'numeric' => 'Isian kolom :attribute harus lebih besar dari :value.',
        'string' => 'Panjang isian kolom :attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Isian kolom :attribute harus memiliki :value item atau lebih.',
        'file' => 'Ukuran isian kolom :attribute harus lebih besar dari atau sama dengan :value kilobyte.',
        'numeric' => 'Isian kolom :attribute harus lebih besar dari atau sama dengan :value.',
        'string' => 'Panjang isian kolom :attribute harus minimal :value karakter.',
    ],
    'hex_color' => 'Isian kolom :attribute harus berupa kode warna heksadesimal yang valid.',
    'image' => 'Isian kolom :attribute harus berupa gambar.',
    'import_field_empty'    => 'Nilai untuk :fieldname tidak boleh kosong.',
    'in' => ':attribute yang di pilih tidak benar.',
    'in_array' => 'Isian kolom :attribute harus ada di :other.',
    'integer' => 'Isian kolom :attribute harus berupa bilangan bulat.',
    'ip' => 'Isian kolom :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Isian kolom :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Isian kolom :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Isian kolom :attribute harus berupa string JSON yang valid.',
    'list' => 'Isian kolom :attribute harus berupa daftar.',
    'lowercase' => 'Isian kolom :attribute harus huruf kecil.',
    'lt' => [
        'array' => 'Isian kolom :attribute harus berjumlah kurang dari :value item.',
        'file' => 'Isian kolom :attribute harus kurang dari :value kilobyte.',
        'numeric' => 'Isian kolom :attribute harus kurang dari :value.',
        'string' => 'Panjang isian kolom :attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Isian kolom :attribute tidak boleh lebih dari :value.',
        'file' => 'Ukuran isian kolom :attribute harus kurang dari atau sama dengan :value kilobyte.',
        'numeric' => 'Isian kolom :attribute harus kurang dari atau sama dengan :value.',
        'string' => 'Panjang isian kolom :attribute harus kurang dari atau sama dengan :value karakter.',
    ],
    'mac_address' => 'Isian kolom :attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => 'Isian kolom :attribute tidak boleh lebih dari :max.',
        'file' => 'Ukuran isian kolom :attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => 'Panjang isian kolom :attribute tidak boleh lebih dari :max karakter.',
        'string' => 'Panjang isian kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits' => 'Isian kolom :attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => 'Isian kolom :attribute harus berupa file dengan tipe: :values.',
    'mimetypes' => 'Isian kolom :attribute harus berupa file dengan tipe: :values.',
    'min' => [
        'array' => 'Isian kolom :attribute harus memiliki setidaknya :min item.',
        'file' => 'Ukuran isian kolom :attribute harus setidaknya :min kilobyte.',
        'numeric' => 'Isian kolom :attribute harus minimal :min.',
        'string' => 'Panjang isian kolom :attribute harus minimal :min karakter.',
    ],
    'min_digits' => 'Isian kolom :attribute harus minimal :min digit.',
    'missing' => 'Isian kolom :attribute harus kosong.',
    'missing_if' => 'Isian kolom :attribute harus kosong ketika :other adalah :value.',
    'missing_unless' => 'Isian kolom :attribute harus kosong kecuali :other adalah :value.',
    'missing_with' => 'Isian kolom :attribute harus kosong ketika :values ada.',
    'missing_with_all' => 'Isian kolom :attribute harus kosong ketika :values ada.',
    'multiple_of' => 'Isian kolom :attribute harus kelipatan dari :value.',
    'not_in' => ':attribute yang di pilih tidak benar.',
    'not_regex' => 'Format isian kolom :attribute tidak valid.',
    'numeric' => 'Isian kolom :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Isian kolom :attribute harus berisi minimal satu huruf.',
        'mixed' => 'Isian kolom :attribute harus berisi minimal satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Isian kolom :attribute harus berisi minimal satu angka.',
        'symbols' => 'Isian kolom :attribute harus berisi minimal satu simbol.',
        'uncompromised' => ' :attribute yang diberikan telah muncul dalam daftar kebocoran data. Silakan pilih :attribute yang berbeda',
    ],
    'percent'       => 'Nilai minimum depresiasi harus antara 0 dan 100 ketika tipe depresiasi adalah persentase.',

    'present' => 'Bidang atribut: harus ada.',
    'present_if' => 'Isian kolom :attribute harus ada ketika :other adalah :value.',
    'present_unless' => 'Isian kolom :attribute harus ada kecuali :other adalah :value.',
    'present_with' => 'Isian kolom :attribute harus ada ketika :values ada.',
    'present_with_all' => 'Isian kolom :attribute harus ada ketika :values ada.',
    'prohibited' => 'Isian kolom :attribute dilarang.',
    'prohibited_if' => 'Isian kolom :attribute dilarang ketika :other adalah :value.',
    'prohibited_unless' => 'Isian kolom :attribute dilarang kecuali :other ada di dalam :values.',
    'prohibits' => 'Isian kolom :attribute melarang :other untuk ada.',
    'regex' => 'Format isian kolom :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib di-isi.',
    'required_array_keys' => 'Isian kolom :attribute harus berisi entri untuk: :values.',
    'required_if' => 'Kolom :attribute wajib di-isi ketika :other nya :value.',
    'required_if_accepted' => 'Isian kolom :attribute wajib diisi ketika :other diterima.',
    'required_if_declined' => 'Isian kolom :attribute wajib diisi ketika :other ditolak.',
    'required_unless' => 'Bidang atribut: diperlukan kecuali: lainnya ada dalam: nilai.',
    'required_with' => 'Kolom :attribute wajib di-isi ketika terdapat :values.',
    'required_with_all' => 'Isian kolom :attribute wajib diisi ketika :values ada.',
    'required_without' => 'Kolom :attribute wajib di-isi ketika :values tidak ada.',
    'required_without_all' => 'Bidang atribut: diperlukan bila tidak ada: nilai yang ada.',
    'same' => 'Isian kolom :attribute harus cocok dengan :other.',
    'size' => [
        'array' => 'Isian kolom :attribute harus berisi :size item.',
        'file' => 'Ukuran isian kolom :attribute harus :size kilobyte.',
        'numeric' => 'Isian kolom :attribute harus :size.',
        'string' => 'Panjang isian kolom :attribute harus :size karakter.',
    ],
    'starts_with' => 'Isian kolom :attribute harus dimulai dengan salah satu dari berikut ini: :values.',
    'string'               => 'The: atribut harus berupa string.',
    'two_column_unique_undeleted' => ':attribute harus unik di antara :table1 dan :table2.',
    'unique_undeleted'     => ':attribute harus unik.',
    'non_circular'         => ':attribute tidak boleh membuat referensi melingkar.',
    'not_array'            => ':attribute tidak boleh berupa array.',
    'disallow_same_pwd_as_user_fields' => 'Kata sandi tidak boleh sama dengan nama pengguna.',
    'letters'              => 'Kata sandi harus terdiri dari minimal satu huruf.',
    'numbers'              => 'Kata sandi harus terdiri dari minimal satu angka.',
    'case_diff'            => 'Kata sandi harus menggunakan kombinasi huruf besar dan kecil.',
    'symbols'              => 'Kata sandi harus berisi simbol.',
    'timezone' => 'Isian kolom :attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => 'Atribut: gagal diupload.',
    'uppercase' => 'Isian kolom :attribute harus huruf besar.',
    'url' => 'Isian kolom :attribute harus berupa URL yang valid.',
    'ulid' => 'Isian kolom :attribute harus berupa ULID yang valid.',
    'uuid' => 'Isian kolom :attribute harus berupa UUID yang valid.',
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

    'email_array'      => 'Satu atau lebih alamat email tidak benar.',
    'checkboxes'           => ':attribute berisi opsi yang tidak valid.',
    'radio_buttons'        => ':attribute tidak valid.',
    
    'custom' => [
        'alpha_space' => 'Bidang atribut: berisi karakter yang tidak diizinkan.',

        'hashed_pass'      => 'Kata sandi Anda saat ini salah',
        'dumbpwd'          => 'Password itu terlalu umum',
        'statuslabel_type' => 'Anda harus memilih jenis label status yang valid',
        'custom_field_not_found'          => 'Kolom ini tampaknya tidak ada, harap periksa kembali nama kolom kustom Anda.',
        'custom_field_not_found_on_model' => 'Kolom ini tampaknya ada, tetapi tidak tersedia pada set kolom Model Aset ini.',

        // date_format validation with slightly less stupid messages. It duplicates a lot, but it gets the job done :(
        // We use this because the default error message for date_format reflects php Y-m-d, which non-PHP
        // people won't know how to format.
        'purchase_date.date_format'     => ' :attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD',
        'last_audit_date.date_format'   =>  ':attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD hh:mm:ss',
        'expiration_date.date_format'   =>  ':attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD',
        'termination_date.date_format'  =>  ' :attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD',
        'expected_checkin.date_format'  =>  ':attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD',
        'start_date.date_format'        =>  ':attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD',
        'end_date.date_format'          =>  ':attribute harus berupa tanggal yang valid dalam format YYYY-MM-DD',
        'invalid_value_in_field' => 'Nilai tidak valid disertakan dalam isian kolom ini',

        'ldap_username_field' => [
            'not_in' =>         '<code>sAMAccountName</code> (kombinasi huruf besar dan kecil) kemungkinan tidak akan berfungsi. Anda sebaiknya menggunakan <code>samaccountname</code> (huruf kecil) sebagai gantinya.'
        ],
        'ldap_auth_filter_query' => ['not_in' => '<code>uid=samaccountname</code> mungkin bukan filter autentikasi yang valid. Anda mungkin menginginkan <code>uid=<code>'],
        'ldap_filter' => ['regex' => 'Nilai ini sebaiknya tidak berada dalam tanda kurung.'],

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
        'invalid_value_in_field' => 'Nilai tidak valid disertakan dalam isian kolom ini',
        'required' => 'Kolom ini wajib diisi',
        'email' => 'Silakan masukkan alamat email yang valid',
    ],


];
