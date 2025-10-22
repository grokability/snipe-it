<?php

return [
    'ad'				        => 'دایرکتوری فعال',
    'ad_domain'				    => 'دامنه فعال دایرکتوری',
    'ad_domain_help'			=> 'این گاهی اوقات دامنه ایمیل شماست اما همیشه اینطور نیست.',
    'ad_append_domain_label'    => 'نام دامنه را اضافه کنید
',
    'ad_append_domain'          => 'نام دامنه را به قسمت نام کاربری اضافه کنید
',
    'ad_append_domain_help'     => 'کاربر نیازی به نوشتن "username@domain.local" ندارد، آنها فقط می توانند "username" را تایپ کنند.
',
    'admin_cc_email'            => 'ایمیل CC
',
    'admin_cc_email_help'       => 'Send a copy of checkin/checkout emails to this address.',
    'admin_cc_always' => 'Always send copy upon checkin/checkout',
    'admin_cc_when_acceptance_required' => 'Only send copy upon checkout if acceptance is required',
    'admin_settings'            => 'Admin Settings',
    'is_ad'				        => 'این سرور Active Directory است',
    'alerts'                	=> 'هشدار',
    'alert_title'               => 'Update Notification Settings',
    'alert_email'				=> 'ارسال هشدار به',
    'alert_email_help'          => 'آدرس‌های ایمیل یا لیست‌های توزیعی که می‌خواهید هشدارها به آنها ارسال شود، با کاما از هم جدا شده‌اند.
',
    'alerts_enabled'			=> 'هشدارها فعال شد',
    'alert_interval'			=> 'آستانه ی انقضای هشدارها( به روز)',
    'alert_inv_threshold'		=> 'فهرست آستانه ی هشدار',
    'allow_user_skin'           => 'اجازه کاربر پوسته',
    'allow_user_skin_help_text' => 'علامت زدن این کادر به کاربر این امکان را می دهد که پوسته رابط کاربری را با پوسته دیگری لغو کند.
',
    'asset_ids'					=> 'ID حساب',
    'audit_interval'            => 'فاصله حسابرسی',
    'audit_interval_help'       => 'If you are required to regularly physically audit your assets, enter the interval in months that you use. If you update this value, all of the "next audit dates" for assets with an upcoming audit date will be updated.',
    'audit_warning_days'        => 'آستانه هشدار حسابرسی',
    'audit_warning_days_help'   => 'چند روز پیش باید به شما هشدار می دهیم هنگامی که دارایی ها برای حسابرسی مورد نیاز است؟',
    'auto_increment_assets'		=> 'برچسب‌های دارایی با افزایش خودکار را ایجاد کنید
',
    'auto_increment_prefix'		=> 'پیشوند (اختیاری)',
    'auto_incrementing_help'    => 'برای تنظیم، ابتدا برچسب‌های دارایی افزایش خودکار را فعال کنید
',
    'backups'					=> 'پشتیبان گیری',
    'backups_help'              => 'ایجاد، دانلود و بازیابی نسخه پشتیبان
',
    'backups_restoring'         => 'بازیابی از پشتیبان گیری
',
    'backups_clean' => 'Clean the backed-up database before restore',
    'backups_clean_helptext' => "This can be useful if you're changing between database versions",
    'backups_upload'            => 'نسخه پشتیبان را دانلود کنید',
    'backups_path'              => 'نسخه‌های پشتیبان روی سرور در <code>:path</code> ذخیره می‌شوند
',
    'backups_restore_warning'   => 'Use the restore button <small><span class="btn btn-xs btn-warning"><i class="text-white fas fa-retweet" aria-hidden="true"></i></span></small> to restore from a previous backup. (This does not currently work with S3 file storage.)<br><br>Your <strong>entire :app_name database and any uploaded files will be completely replaced</strong> by what\'s in the backup file  ',
    'backups_logged_out'         => 'همه کاربران موجود، از جمله شما، پس از تکمیل بازیابی شما از سیستم خارج می شوند.
',
    'backups_large'             => 'پشتیبان‌گیری‌های بسیار بزرگ ممکن است در تلاش بازیابی به پایان برسد و ممکن است همچنان نیاز باشد از طریق خط فرمان اجرا شوند.
',
    'barcode_settings'			=> 'تنظیمات بارکد',
    'confirm_purge'			    => 'تایید پاکسازی',
    'confirm_purge_help'		=> 'متن "DELETE" را در کادر زیر وارد کنید تا رکوردهای حذف شده خود را پاک کنید. این عمل قابل واگرد نیست و همه موارد و کاربران حذف شده را برای همیشه حذف می کند. (برای حفظ امنیت، ابتدا باید یک نسخه پشتیبان تهیه کنید.)
',
    'custom_css'				=> 'سفارشی CSS',
    'custom_css_placeholder'	=> 'Add your custom CSS',
    'custom_css_help'			=> 'هر ابطال CSS سفارشی می خواهید استفاده کنید را وارد کنید.از  برچسب های &lt;style&gt;&lt;/style&gt; استفاده نکنید.',
    'custom_forgot_pass_url'	=> 'URL تنظیم مجدد رمز عبور سفارشی
',
    'custom_forgot_pass_url_help'	=> 'این جایگزین URL داخلی رمز عبور فراموش شده در صفحه ورود می شود، که برای هدایت افراد به عملکرد بازنشانی رمز عبور LDAP داخلی یا میزبانی شده مفید است. این به طور موثر عملکرد رمز عبور فراموش شده توسط کاربر محلی را غیرفعال می کند.
',
    'dashboard_message'			=> 'پیام داشبورد
',
    'dashboard_message_help'	=> 'این متن برای هر کسی که اجازه مشاهده داشبورد را دارد در داشبورد ظاهر می شود.
',
    'default_currency'  		=> 'ارز پیش فرض',
    'default_eula_text'			=> 'EULA پیش فرض',
    'default_eula_text_placeholder' => 'Add your default EULA text',
    'default_language'			=> 'زبان پیش فرض',
    'default_eula_help_text'	=> 'همچنین می توانید  EULA های سفارشی به دسته های خاص دارایی مرتبط کنید.',
    'acceptance_note'           => 'Add a note for your decision (required if declining)',
    'display_asset_name'        => 'نمایش نام حساب',
    'display_checkout_date'     => 'نمایش تاریخ پرداخت',
    'display_eol'               => 'نمایش EOL در جدول',
    'display_qr'                => 'نمایش بارکد دو بعدی',
    'display_alt_barcode'		=> 'نمایش بارکد 1D',
    'barcode_type'				=> 'نوع بارکد 2D',
    'alt_barcode_type'			=> 'نوع بارکد 1D',
    'enabled'                   => 'فعال شد
',
    'eula_settings'				=> 'EULA تنظیمات',
    'eula_markdown'				=> 'این EULA اجازه می دهد تا <a href="https://help.github.com/articles/github-flavored-markdown/">Github با طعم markdown</a>.',
    'empty_row_count'           => 'Field Start Offset (Empty Rows)',
    'empty_row_count_help'      => 'Fields will begin populating after this many empty rows are skipped at the top of the label.',
    'favicon'                   => 'فاویکون',
    'favicon_format'            => 'انواع فایل های پذیرفته شده عبارتند از ico، png و gif. سایر فرمت های تصویر ممکن است در همه مرورگرها کار نکنند.
',
    'favicon_size'          => 'فاویکون ها باید تصاویر مربعی، 16x16 پیکسل باشند.
',
    'footer_text'               => 'متن پاورقی اضافی
',
    'footer_text_help'          => 'این متن در فوتر سمت راست ظاهر می شود. پیوندها با استفاده از <a href="https://help.github.com/articles/github-flavored-markdown/">نشان‌گذاری طعم‌دار Github</a> مجاز هستند. شکستگی خطوط، هدرها، تصاویر و غیره ممکن است منجر به نتایج غیر قابل پیش بینی شود.
',
    'footer_text_placeholder'   => 'Optional footer text',
    'general_settings'			=> 'تنظیمات عمومی',
    'general_settings_help'     => 'EULA پیش فرض و موارد دیگر
',
    'generate_backup'			=> 'تولید پشتیبان گیری',
    'google_workspaces'         => 'Google Workspaces',
    'header_color'              => 'رنگ هدر',
    'info'                      => 'این تنظیمات به شما اجازه سفارشی کردن جنبه های خاصی از نصب و راه اندازی خود را می دهد.',
    'label_logo_size'           => 'آرم های مربعی بهترین ظاهر را دارند - در سمت راست بالای هر برچسب دارایی نمایش داده می شوند.
',
    'laravel'                   => 'نسخه Laravel',
    'ldap'                      => 'LDAP',
    'ldap_default_group'        => 'Default Permissions Group',
    'ldap_default_group_info'   => 'Select a group to assign to newly synced users. Remember that a user takes on the permissions of the group they are assigned.',
    'no_default_group'          => 'No Default Group',
    'ldap_help'                 => 'دایرکتوری فعال',
    'ldap_client_tls_key'       => 'کلید TLS مشتری LDAP
',
    'ldap_client_tls_cert'      => 'گواهی TLS سمت مشتری LDAP
',
    'ldap_enabled'              => 'LDAP فعال شد.',
    'ldap_integration'          => 'ادغام LDAP',
    'ldap_settings'             => 'تنظیمات LDAP',
    'ldap_client_tls_cert_help' => 'گواهی TLS سمت کلاینت و کلید برای اتصالات LDAP معمولاً فقط در پیکربندی‌های Google Workspace با « LDAP ایمن» مفید هستند. هر دو مورد نیاز است.
',
    'ldap_location'             => 'LDAP Location Field',
'ldap_location_help'             => 'The LDAP Location field should be used if <strong>an OU is not being used in the Base Bind DN.</strong> Leave this blank if an OU search is being used.',
    'ldap_login_test_help'      => 'یک نام کاربری و رمز عبور LDAP معتبر از DN پایه ای که در بالا مشخص کرده اید وارد کنید تا بررسی کنید که آیا ورود به سیستم LDAP شما به درستی پیکربندی شده است یا خیر. ابتدا باید تنظیمات LDAP به روز شده خود را ذخیره کنید.
',
    'ldap_login_sync_help'      => 'This only tests that LDAP can sync and that your fields are mapped correctly. If your LDAP Authentication query is not correct, users may still not be able to login. YOU MUST SAVE YOUR UPDATED LDAP SETTINGS FIRST.',
    'ldap_manager'              => 'LDAP Manager Field',
    'ldap_server'               => 'سرویس دهنده LDAP',
    'ldap_server_help'          => 'This should start with ldap:// (for unencrypted) or ldaps:// (for TLS or SSL)',
    'ldap_server_cert'			=> 'اعتبار گواهی نامه LDAP SSL',
    'ldap_server_cert_ignore'	=> 'اجازه می دهد به گواهی های بی اعتبار SSL',
    'ldap_server_cert_help'		=> 'اگر از یک امضای SSL شخصی معتبر استفاده می کنید این گزینه را فعال کنید.',
    'ldap_tls'                  => 'از TLS استفاده کنید',
    'ldap_tls_help'             => 'این باید فقط در صورتی که STARTTLS را در سرور LDAP خود اجرا می کنید، بررسی شود.',
    'ldap_uname'                => 'حالت نام کاربری نامرئی LDAP',
    'ldap_dept'                 => 'LDAP Department Field',
    'ldap_phone'                => 'LDAP Phone Number Field',
    'ldap_jobtitle'             => 'LDAP Job Title Field',
    'ldap_country'              => 'LDAP Country Field',
    'ldap_pword'                => 'LDAP اتصال رمز عبور',
    'ldap_basedn'               => 'اتصال پایگاه DN',
    'ldap_filter'               => 'LDAP فیلتر',
    'ldap_pw_sync'              => 'Cache LDAP Passwords',
    'ldap_pw_sync_help'         => 'Uncheck this box if you do not wish to keep LDAP passwords cached as local hashed passwords. Disabling this means that your users may not be able to login if your LDAP server is unreachable for some reason.',
    'ldap_username_field'       => 'LDAP Username Field',
    'ldap_display_name'          => 'LDAP Display Name Field',
    'ldap_display_name_help'    => 'If you have a separate displayName field in your LDAP/AD, map it here and it will be used for displaying users within Snipe-IT.',
    'ldap_lname_field'          => 'LDAP Last Name Field',
    'ldap_fname_field'          => 'LDAP First Name Field',
    'ldap_auth_filter_query'    => 'تأیید اعتبار  پرس و جوLDAP',
    'ldap_version'              => 'نسخهٔ LDAP',
    'ldap_active_flag'          => ' پرچم فعالLDAP',
    'ldap_activated_flag_help'  => 'This value is used to determine whether a synced user can login to Snipe-IT. <strong>It does not affect the ability to check items in or out to them</strong>, and should be the <strong>attribute name</strong> within your AD/LDAP, <strong>not the value</strong>. <br><br>If this field is set to a field name that does not exist in your AD/LDAP, or the value in the AD/LDAP field is set to <code>0</code> or <code>false</code>, <strong>user login will be disabled</strong>. If the value in the AD/LDAP field is set to <code>1</code> or <code>true</code> or <em>any other text</em> means the user can log in. When the field is blank in your AD, we respect the <code>userAccountControl</code> attribute, which usually allows non-suspended users to log in.',
    'ldap_invert_active_flag'   => 'LDAP Invert Active Flag',
    'ldap_invert_active_flag_help'     => 'If enabled: when the value returned by LDAP Active Flag is <code>0</code> or <code>false</code> the user account will be active.',
    'ldap_emp_num'              => 'LDAP Employee Number Field',
    'ldap_email'                => 'LDAP Email Field',
    'ldap_mobile'               => 'LDAP Mobile Field',
    'ldap_address'              => 'LDAP Address Field',
    'ldap_city'                 => 'LDAP City Field',
    'ldap_state'                => 'LDAP State/Province Field',
    'ldap_zip'                  => 'LDAP Postal Code Field',
    'ldap_test'                 => 'تست LDAP
',
    'ldap_test_sync'            => 'تست همگام سازی LDAP
',
    'license'                   => 'مجوز نرم افزار
',
    'load_remote'               => 'Load Remote Avatars',
    'load_remote_help_text'		=> 'Uncheck this box if your install cannot load scripts from the outside internet. This will prevent Snipe-IT from trying load avatars from Gravatar or other outside sources.',
    'login'                     => 'تلاش برای ورود
',
    'login_attempt'             => 'تلاش برای ورود
',
    'login_ip'                  => 'آدرس IP',
    'login_success'             => 'موفقیت',
    'login_user_agent'          => 'عامل کاربر
',
    'login_help'                => 'لیست تلاش برای ورود به سیستم
',
    'login_note'                => 'توجه داشته باشید ورود',
    'login_note_placeholder'            => "If you do not have a login or have found a device belonging to this company, please call technical support at 888-555-1212. Thank you.",
    'login_note_help'           => 'به صورت دلخواه شامل چند جمله در صفحه ورود به سیستم خود، به عنوان مثال برای کمک به افرادی که یک دستگاه گم شده یا دزدیده شده را پیدا کرده اند. این فیلد <a href="https://help.github.com/articles/github-flavored-markdown/"> مارجین طعم Github</a> را می پذیرد',
    'login_remote_user_text'    => 'گزینه های ورود کاربر از راه دور
',
    'login_remote_user_enabled_text' => 'ورود با سربرگ کاربر راه دور را فعال کنید
',
    'login_remote_user_enabled_help' => 'این گزینه احراز هویت را از طریق هدر REMOTE_USER مطابق "واسط دروازه مشترک (rfc3875)" فعال می کند.
',
    'login_common_disabled_text' => 'سایر مکانیسم های احراز هویت را غیرفعال کنید
',
    'login_common_disabled_help' => 'این گزینه مکانیسم های دیگر احراز هویت را غیرفعال می کند. اگر مطمئن هستید که ورود به سیستم REMOTE_USER شما از قبل کار می کند، فقط این گزینه را فعال کنید
',
    'login_remote_user_custom_logout_url_text' => 'URL خروج سفارشی
',
    'login_remote_user_custom_logout_url_help' => 'اگر یک URL در اینجا ارائه شود، پس از خروج کاربر از Snipe-IT، کاربران به این URL هدایت می شوند. این برای بستن صحیح جلسات کاربر ارائه دهنده احراز هویت شما مفید است.
',
    'login_remote_user_header_name_text' => 'هدر نام کاربری سفارشی
',
    'login_remote_user_header_name_help' => 'به جای REMOTE_USER از هدر مشخص شده استفاده کنید
',
    'logo'                    	=> 'لوگو',
    'logo_print_assets'         => 'استفاده در چاپ
',
    'logo_print_assets_help'    => 'از نام تجاری در لیست دارایی های قابل چاپ استفاده کنید
',
    'full_multiple_companies_support_help_text' => 'محدود کردن کاربران (از جمله مدیران) اختصاص داده شده به شرکت ها برای دارایی های شرکت خود را.',
    'full_multiple_companies_support_text' => 'شرکت های متعدد پشتیبانی کامل',
    'scope_locations_fmcs_support_text'  => 'Scope Locations with Full Multiple Companies Support',
    'scope_locations_fmcs_support_help_text'  => 'Restrict locations to their selected company.',
    'scope_locations_fmcs_check_button' => 'Check Compatibility',
    'scope_locations_fmcs_test_needed' => 'Please Check Compatibility to enable this',
    'scope_locations_fmcs_support_disabled_text'  => 'This option is disabled because you have conflicting locations set for :count or more items.',
    'show_in_model_list'   => 'نمایش در مدل کشویی 
',
    'optional'					=> 'اختیاری',
    'per_page'                  => 'نتایج در هر صفحه',
    'php'                       => 'نسخه php',
    'php_info'                  => 'PHP info',
    'php_overview'              => 'PHP
',
    'php_overview_help'         => 'PHP System info
',
    'php_gd_info'               => 'شما باید  php-gd را نصب کنید تا QR کد ها را ببنید، به دستورالعمل های نصب نگاه کنید.',
    'php_gd_warning'            => 'php پردازش تصویر و تفاضل پلاگین نصب نشده است.',
    'pwd_secure_complexity'     => 'پیچیدگی گذرواژه',
    'pwd_secure_complexity_help' => 'هرکدام از پیچیدگیهای رمز عبور را که میخواهید اجرا کنید، انتخاب کنید.',
    'pwd_secure_complexity_disallow_same_pwd_as_user_fields' => 'رمز عبور نمی تواند با نام، نام خانوادگی، ایمیل یا نام کاربری یکی باشد
',
    'pwd_secure_complexity_letters' => 'حداقل یک حرف لازم است
',
    'pwd_secure_complexity_numbers' => 'حداقل به یک عدد نیاز دارید
',
    'pwd_secure_complexity_symbols' => 'حداقل به یک نماد نیاز دارید
',
    'pwd_secure_complexity_case_diff' => 'حداقل یک حروف بزرگ و یک حروف کوچک لازم است
',
    'pwd_secure_min'            => 'رمز عبور حداقل کاراکتر',
    'pwd_secure_min_help'       => 'حداقل مقدار مجاز 8 است
',
    'pwd_secure_uncommon'       => 'جلوگیری از کلمه عبور رایج',
    'pwd_secure_uncommon_help'  => 'این امر کاربران را از استفاده از گذرواژههای رایج از 10 هزار کلمه عبور که در نقض گزارش شده است، ممنوع می کند.',
    'qr_help'                   => 'کدهای QR اول به این مجموعه را فعال کنید',
    'qr_text'                   => 'متن QR کد',
    'saml'                      => 'SAML',
    'saml_title'                => 'تنظیمات SAML را به روز کنید
',
    'saml_help'                 => 'تنظیمات SAML
',
    'saml_enabled'              => 'SAML فعال است
',
    'saml_integration'          => 'یکپارچه سازی SAML
',
    'saml_sp_entityid'          => 'شناسه نهاد
',
    'saml_sp_acs_url'           => 'نشانی اینترنتی خدمات مصرف کننده ادعایی (ACS).
',
    'saml_sp_sls_url'           => 'URL سرویس خروج واحد (SLS).
',
    'saml_sp_x509cert'          => 'گواهی عمومی
',
    'saml_sp_metadata_url'      => 'URL فراداده
',
    'saml_idp_metadata'         => 'SAML IdP Metadata
',
    'saml_idp_metadata_help'    => 'می توانید با استفاده از یک URL یا فایل XML، فراداده IdP را مشخص کنید.
',
    'saml_attr_mapping_username' => 'نگاشت ویژگی - نام کاربری
',
    'saml_attr_mapping_username_help' => 'اگر نگاشت ویژگی مشخص نشده یا نامعتبر باشد از NameID استفاده خواهد شد.
',
    'saml_forcelogin_label'     => 'SAML Force Login',
    'saml_forcelogin'           => 'SAML را به عنوان ورود اولیه انتخاب کنید
',
    'saml_forcelogin_help'      => 'شما می توانید از \'/login?nosaml\' برای رفتن به صفحه ورود به سیستم معمولی استفاده کنید.
',
    'saml_slo_label'            => 'SAML خروج یکباره
',
    'saml_slo'                  => 'هنگام خروج، یک درخواست خروج به IdP ارسال کنید
',
    'saml_slo_help'             => 'این باعث می شود که کاربر در هنگام خروج ابتدا به IdP هدایت شود. اگر IdP به درستی SAML SLO آغاز شده با SP را پشتیبانی نمی کند، علامت را بردارید.
',
    'saml_custom_settings'      => 'تنظیمات سفارشی SAML
',
    'saml_custom_settings_help' => 'می توانید تنظیمات اضافی را برای کتابخانه onelogin/php-saml تعیین کنید. با مسئولیت خود استفاده کنید.
',
    'saml_download'             => 'دانلود متادیتا
',
    'setting'                   => 'تنظیمات',
    'settings'                  => 'تنظيمات',
    'show_alerts_in_menu'       => 'نمایش هشدارها در منوی بالا
',
    'show_archived_in_list'     => 'موارد بایگانی شده',
    'show_archived_in_list_text'     => 'دارایی‌های بایگانی‌شده را در فهرست «همه دارایی‌ها» نشان دهید
',
    'show_assigned_assets'      => 'نمایش دارایی های اختصاص داده شده به دارایی ها
',
    'show_assigned_assets_help' => 'دارایی هایی را که به سایر دارایی ها اختصاص داده شده اند در View User -> Assets، View User -> Info -> Print All Assigned و در Account -> View Assigned Assets نمایش دهید.
',
    'show_images_in_email'     => 'استفاده از عکس در ایمیل ها',
    'show_images_in_email_help'   => 'اگر نصب Snipe-IT شما پشت VPN یا شبکه بسته است و کاربران خارج از شبکه نمی توانند تصاویر ارائه شده از این نصب را در ایمیل های خود بارگیری کنند، علامت این کادر را بردارید.
',
    'site_name'                 => 'نام سایت',
    'integrations'               => 'Integrations',
    'slack'                     => 'Slack',
    'general_webhook'           => 'General Webhook',
    'ms_teams'                  => 'Microsoft Teams',
    'webhook'                   => ':app',
    'webhook_presave'           => 'Test to Save',
    'webhook_title'               => 'Update Webhook Settings',
    'webhook_help'                => 'Integration settings',
    'webhook_botname'             => ':app Botname',
    'webhook_channel'             => ':app Channel',
    'webhook_endpoint'            => ':app Endpoint',
    'webhook_integration'         => ':app Settings',
    'webhook_test'                 =>'Test :app integration',
    'webhook_integration_help'    => ':app integration is optional, however the endpoint and channel are required if you wish to use it. To configure :app integration, you must first <a href=":webhook_link" target="_new" rel="noopener">create an incoming webhook</a> on your :app account. Click on the <strong>Test :app Integration</strong> button to confirm your settings are correct before saving. ',
    'webhook_integration_help_button'    => 'Once you have saved your :app information, a test button will appear.',
    'webhook_test_help'           => 'Test whether your :app integration is configured correctly. YOU MUST SAVE YOUR UPDATED :app SETTINGS FIRST.',
    'shortcuts_enabled'         => 'Enable Shortcuts',
    'shortcuts_help_text'       => '<strong>Windows</strong>: Alt + Access key, <strong>Mac</strong>: Control + Option + Access key',
    'snipe_version'  			=> 'نسخه Snipe_IT',
    'support_footer'            => 'پشتیبانی از پیوندهای پاورقی
',
    'support_footer_help'       => 'مشخص کنید چه کسی پیوندهای اطلاعات پشتیبانی Snipe-IT و راهنمای کاربران را ببیند
',
    'version_footer'            => 'نسخه در پاورقی
',
    'version_footer_help'       => 'مشخص کنید چه کسی نسخه و شماره ساخت Snipe-IT را ببیند.
',
    'system'                    => 'اطلاعات سیستم',
    'update'                    => 'به‌ روزرسانی تنظیمات',
    'value'                     => 'عنوان آیتم',
    'brand'                     => 'نام تجاری',
    'brand_help'                => 'لوگو، نام سایت
',
    'web_brand'                 => 'نوع برندینگ وب
',
    'about_settings_title'      => 'درباره تنظیمات',
    'about_settings_text'       => 'این تنظیمات به شما اجازه سفارشی کردن جنبه های خاصی از نصب و راه اندازی خود را می دهد.',
    'labels_per_page'           => 'برچسب ها در صفحه',
    'label_dimensions'          => 'ابعاد برچسب (اینچ)',
    'next_auto_tag_base'        => 'افزایش خودکار بعدی',
    'page_padding'              => 'صفحه حاشیه (اینچ)',
    'privacy_policy_link'       => 'ویرایش خط مشی حریم خصوصی',
    'privacy_policy'            => 'سیاست حفظ حریم خصوصی',
    'privacy_policy_link_help'  => 'اگر نشانی اینترنتی در اینجا گنجانده شده باشد، پیوندی به خط مشی رازداری شما در پاورقی برنامه و در هر ایمیلی که سیستم ارسال می‌کند، مطابق با GDPR قرار می‌گیرد.
',
    'purge'                     => 'پاکسازی حذف رکوردها',
    'purge_deleted'             => 'پاکسازی حذف شد
',
    'labels_display_bgutter'    => 'برچسب قطره قطره پایین',
    'labels_display_sgutter'    => 'برچسب سمت قطره قطره ',
    'labels_fontsize'           => 'اندازه نوع خط برچسب',
    'labels_pagewidth'          => 'عرض صفحه ی برچسب',
    'labels_pageheight'         => 'طول صفحه ی برچسب',
    'label_gutters'        => 'فاصله ی برچسب (اینچ)',
    'page_dimensions'        => 'ابعاد صفحه (اینچ)',
    'label_fields'          => 'فیلدهای قابل مشاهده ی برچسب',
    'inches'        => 'اینچ',
    'width_w'        => 'عرض',
    'height_h'        => 'ارتفاع',
    'show_url_in_emails'                => 'پیوند به Snipe-IT در ایمیل',
    'show_url_in_emails_help_text'      => 'اگر نمیخواهید پیوند به نصب Snipe-IT خود را در زیرپوشهای ایمیل خود پیگیری کنید، این کادر را بردارید. مفید است اگر اکثر کاربران شما هرگز وارد نشده باشند.',
    'text_pt'        => 'بالای صفحه',
    'thumbnail_max_h'   => 'حداکثر ریز عکسها',
    'thumbnail_max_h_help'   => 'حداکثر ارتفاع در پیکسل هایی که کوچک می شوند ممکن است در نمای لیست نمایش داده شود. حداقل 25، حداکثر 500.',
    'two_factor'        => 'دو عامل تایید هویت',
    'two_factor_secret'        => 'کد دو فاکتور',
    'two_factor_enrollment'        => 'ثبت نام دو عامل',
    'two_factor_enabled_text'        => 'فعال کردن دو عامل',
    'two_factor_reset'        => 'تنظیم مجدد دو راز فاکتور',
    'two_factor_reset_help'        => 'This will force the user to enroll their device with their authenticator app again. This can be useful if their currently enrolled device is lost or stolen. ',
    'two_factor_reset_success'          => 'دستگاه دو عامل با موفقیت تنظیم مجدد',
    'two_factor_reset_error'          => 'تنظیم مجدد دستگاه دو عامل انجام نشد',
    'two_factor_enabled_warning'        => 'فعال کردن دو عامل اگر آن را در حال حاضر فعال نیست، بلافاصله شما را مجبور به تایید با یک دستگاه ثبت نام Google Auth. اگر کسی در حال حاضر ثبت نام نکند، می توانید دستگاه خود را ثبت نام کنید.',
    'two_factor_enabled_help'        => 'با استفاده از Google Authenticator، احراز هویت دو طرفه روشن خواهد شد.',
    'two_factor_optional'        => 'انتخابی (کاربران مجاز می توانند فعال یا غیرفعال شوند)',
    'two_factor_required'        => 'مورد نیاز برای همه کاربران',
    'two_factor_disabled'        => 'معلول',
    'two_factor_enter_code'	=> 'کد دو فاکتور را وارد کنید',
    'two_factor_config_complete'	=> 'ارسال کد',
    'two_factor_enabled_edit_not_allowed' => 'سرپرست شما اجازه نمی دهد که این تنظیم را ویرایش کنید.',
    'two_factor_enrollment_text'	=> "احراز هویت دو عامل لازم است، اما دستگاه شما هنوز ثبت نشده است. برنامه Google Authenticator خود را باز کنید و کد QR زیر را برای ثبت نام دستگاه خود اسکن کنید. هنگامی که دستگاه خود را ثبت نام کردید، کد زیر را وارد کنید",
    'require_accept_signature'      => 'امضا لازم است',
    'require_accept_signature_help_text'      => 'فعال کردن این ویژگی، کاربران را مجبور به فیزیکی در پذیرش یک دارایی می کند.',
    'require_checkinout_notes'  => 'Require Notes on Checkin/Checkout',
    'require_checkinout_notes_help_text'    => 'Enabling this feature will require the note fields to be populated when checking in or checking out an asset.',
    'left'        => 'چپ',
    'right'        => 'راست',
    'top'        => 'بالا',
    'bottom'        => 'پایین',
    'vertical'        => 'عمودی',
    'horizontal'        => 'افقی',
    'unique_serial'                => 'شماره سریال منحصر به فرد
',
    'unique_serial_help_text'                => 'علامت زدن این کادر یک محدودیت منحصر به فرد را در سریال های دارایی اعمال می کند
',
    'zerofill_count'        => 'طول برچسب دارایی، از جمله zerofill',
    'username_format_help'   => 'این تنظیم تنها در صورتی در فرآیند وارد کردن استفاده می‌شود که نام کاربری ارائه نشده باشد و ما مجبور باشیم یک نام کاربری برای شما ایجاد کنیم.
',
    'oauth_title' => 'تنظیمات API OAuth
',
    'oauth_clients' => 'OAuth Clients',
    'oauth' => 'OAuth
',
    'oauth_help' => 'تنظیمات نقطه پایانی Oauth
',
    'oauth_no_clients' => 'You have not created any OAuth clients yet.',
    'oauth_secret' => 'Secret',
    'oauth_authorized_apps' => 'Authorized Applications',
    'oauth_redirect_url' => 'Redirect URL',
    'oauth_name_help' => ' Something your users will recognize and trust.',
    'oauth_scopes' => 'Scopes',
    'oauth_callback_url' => 'Your application authorization callback URL.',
    'create_client' => 'Create Client',
    'no_scopes' => 'No scopes',
    'asset_tag_title' => 'تنظیمات برچسب دارایی را به روز کنید
',
    'barcode_title' => 'تنظیمات بارکد را به روز کنید
',
    'barcodes' => 'بارکدها
',
    'barcodes_help_overview' => 'بارکد &amp; تنظیمات QR
',
    'barcodes_help' => 'با این کار سعی می شود بارکدهای کش شده را حذف کنید. این معمولاً فقط در صورتی استفاده می شود که تنظیمات بارکد شما تغییر کرده باشد، یا اگر URL Snipe-IT شما تغییر کرده باشد. در صورت دسترسی بعدی، بارکدها دوباره تولید خواهند شد.
',
    'barcodes_spinner' => 'تلاش برای حذف فایل ها...
',
    'barcode_delete_cache' => 'کش بارکد را حذف کنید
',
    'branding_title' => 'تنظیمات برندینگ را به روز کنید
',
    'general_title' => 'تنظیمات عمومی را به روز کنید
',
    'mail_test' => 'ارسال تست
',
    'mail_test_help' => 'با این کار یک ایمیل آزمایشی به :replyto ارسال می شود.
',
    'filter_by_keyword' => 'با این کار یک ایمیل آزمایشی به :replyto ارسال می شود.
',
    'security' => 'امنیت',
    'security_title' => 'تنظیمات امنیتی را به روز کنید
',
    'security_help' => 'دو عامل، محدودیت رمز عبور
',
    'groups_help' => 'گروه های مجوز حساب
',
    'localization' => 'بومی سازی
',
    'localization_title' => 'تنظیمات محلی سازی را به روز کنید
',
    'localization_help' => 'زبان، نمایش تاریخ
',
    'notifications' => 'اعلان‌ ها',
    'notifications_help' => 'Email Alerts & Audit Settings',
    'asset_tags_help' => 'افزایش و پیشوندها
',
    'labels' => 'برچسب ها',
    'labels_title' => 'تنظیمات برچسب را به روز کنید
',
    'labels_help' => 'Barcodes &amp; label settings',
    'purge_help' => 'پاک کردن رکوردهای حذف شده
',
    'ldap_extension_warning' => 'به نظر نمی رسد که برنامه افزودنی LDAP روی این سرور نصب یا فعال باشد. همچنان می‌توانید تنظیمات خود را ذخیره کنید، اما قبل از اینکه همگام‌سازی یا ورود به سیستم LDAP کار کند، باید افزونه LDAP را برای PHP فعال کنید.
',
    'ldap_ad' => 'LDAP/AD
',
    'ldap_test_label' => 'Test LDAP Sync',
    'ldap_test_login' => ' Test LDAP Login',
    'ldap_username_placeholder' => 'LDAP Username',
    'ldap_password_placeholder' => 'LDAP Password',
    'employee_number' => 'تعداد کارکنان
',
    'create_admin_user' => 'ایجاد کاربر جدید ::',
    'create_admin_success' => 'موفقیت! کاربر ادمین شما اضافه شد!
',
    'create_admin_redirect' => 'برای رفتن به ورود به برنامه خود اینجا را کلیک کنید!
',
    'setup_migrations' => 'مهاجرت های پایگاه داده ::
',
    'setup_no_migrations' => 'چیزی برای مهاجرت وجود نداشت. جداول پایگاه داده شما قبلاً تنظیم شده بود!
',
    'setup_successful_migrations' => 'جداول پایگاه داده شما ایجاد شده است
',
    'setup_migration_output' => 'خروجی مهاجرت:
',
    'setup_migration_create_user' => 'بعدی: ایجاد کاربر
',
    'ldap_settings_link' => 'صفحه تنظیمات LDAP
',
    'slack_test' => 'تست <i class="fab fa-slack"></i> یکپارچه سازی
',
    'status_label_name' => 'Status Label Name',
    'super_admin_only'  => 'Super Admin Only',
    'label2_enable'           => 'New Label Engine',
    'label2_enable_help'      => 'Switch to the new label engine. <b>Note: You will need to save this setting before setting others.</b>',
    'label2_template'         => 'Template',
    'label2_template_help'    => 'Select which template to use for label generation',
    'label2_title'            => 'عنوان',
    'label2_title_help'       => 'The title to show on labels that support it',
    'label2_title_help_phold' => 'The placeholder <code>{COMPANY}</code> will be replaced with the asset&apos;s company name',
    'label2_asset_logo'       => 'Use Asset Logo',
    'label2_asset_logo_help'  => 'Use the logo of the asset&apos;s assigned company, rather than the value at <code>:setting_name</code>',
    'label2_1d_type'          => '1D Barcode Type',
    'label2_1d_type_help'     => 'Format for 1D barcodes',
    'label2_2d_type'          => 'نوع بارکد 2D',
    'label2_2d_type_help'     => 'Format for 2D barcodes',
    'label2_2d_target'        => '2D Barcode Target',
    'label2_2d_target_help'   => 'The data that will be contained in the 2D barcode',
    'label2_fields'           => 'Field Definitions',
    'label2_fields_help'      => 'Fields can be added, removed, and reordered in the left column. For each field, multiple options for Label and DataSource can be added, removed, and reordered in the right column.',
    'purge_barcodes' => 'Purge Barcodes',
    'help_asterisk_bold'    => 'Text entered as <code>**text**</code> will be displayed as bold',
    'help_blank_to_use'     => 'Leave blank to use the value from <code>:setting_name</code>',
    'help_default_will_use' => '<code>:default</code> will use the value from <code>:setting_name</code>. <br>Note that the value of the barcodes must comply with the respective barcode spec in order to be successfully generated. Please see <a href="https://snipe-it.readme.io/docs/barcodes">the documentation <i class="fa fa-external-link"></i></a> for more details. ',
    'asset_id'              => 'Asset ID',
    'data'               => 'Data',
    'default'               => 'Default',
    'none'                  => 'None',
    'google_callback_help' => 'This should be entered as the callback URL in your Google OAuth app settings in your organization&apos;s <strong><a href="https://console.cloud.google.com/" target="_blank">Google developer console <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.',
    'google_login'      => 'Google Workspace Login Settings',
    'enable_google_login'  => 'Enable users to login with Google Workspace',
    'enable_google_login_help'  => 'Users will not be automatically provisioned. They must have an existing account here AND in Google Workspace, and their username here must match their Google Workspace email address. ',
    'mail_reply_to' => 'Mail Reply-To Address',
    'mail_from' => 'Mail From Address',
    'database_driver' => 'Database Driver',
    'bs_table_storage' => 'Table Storage',
    'timezone' => 'Timezone',
    'test_mail' => 'Test Mail',
    'profile_edit'          => 'Edit Profile',
    'profile_edit_help'          => 'Allow users to edit their own profiles.',
    'default_avatar' => 'Custom Default Avatar',
    'default_avatar_help' => 'This image will be displayed as a profile if a user does not have a profile photo.',
    'restore_default_avatar' => 'Restore <a href=":default_avatar" data-toggle="lightbox" data-type="image">original system default avatar</a>',
    'restore_default_avatar_help' => '',
    'due_checkin_days' => 'Due For Checkin Warning',
    'due_checkin_days_help' => 'How many days before the expected checkin of an asset should it be listed in the "Due for checkin" page?',
    'no_groups' => 'No groups have been created yet. Visit <code>Admin Settings > Permission Groups</code> to add one.',
    'text' => 'Text',
    'manager_view' => 'Manager View',
    'manager_view_enabled_text' => 'Enable Manager View',
    'manager_view_enabled_help' => 'Allow managers to view assigned items to their direct and indirect reports in their account view.',

    'username_formats' => [
        'username_format'		=> 'فرمت نام کاربری',
        'firstname_lastname_format'	=> 'نام خانوادگی (jane.smith)',
        'first_name_format'	        => 'First Name (jane)',
        'last_name_format'		=> 'Last Name (doe)',
        'filastname_format'			=> 'First Initial Last Name (jsmith)',
        'lastnamefirstinitial_format' =>  'Last Name First Initial (smithj)',
        'firstname_lastname_underscore_format' => 'First Name Last Name (jane_smith)',
        'firstinitial_lastname' => 'First Initial Last Name (j.smith)',
        'lastname_firstinitial' => 'Last Name First Initial (smith_j)',
        'lastname_dot_firstinitial_format' => 'Last Name First Initial (smith.j)',
        'firstnamelastname'     => 'First Name Last Name (janesmith)',
        'firstnamelastinitial'  => 'First Name Last Initial (janes)',
        'lastnamefirstname'      => 'فامیل نام کوچک (smith.jane)',
    ],

    'email_formats' => [
        'email_format'			=> 'فرمت ایمیل',
        'firstname_lastname_format'	=> 'نام خانوادگی (jane.smith@example.com)',
        'first_name_format'		=> 'نام (jane@example.com)',
        'last_name_format'		=> 'Last Name (doe@example.com)',
        'filastname_format'			=> 'اولین نام خانوادگی (jsmith@example.com)',
        'lastnamefirstinitial_format' =>  'فامیل نام میانه (smithj@example.com)',
        'firstname_lastname_underscore_format' => 'نام خانوادگی (jane.smith@example.com)',
        'firstinitial_lastname' => 'نام میانه فامیل (j.smith@example.com)',
        'lastname_firstinitial' => 'فامیل نام میانه (smith_j@example.com)',
        'lastname_dot_firstinitial_format' => 'فامیل نام میانه (smith.j@example.com)',
        'firstnamelastname'     => 'نام فامیل (janesmith@example.com)',
        'firstnamelastinitial'  => 'نام فامیل میانه (janes@example.com)',
        'lastnamefirstname'      => 'فامیل نام کوچک (smith.jane@example.com)',
    ],



    'logo_labels' => [
        'acceptance_pdf_logo'       => 'PDF Logo',
        'email_logo'                => 'آرم ایمیل
',
        'label_logo'                => 'لوگوی برچسب
',
        'logo'                      => 'Site Logo',
        'favicon'                   => 'فاویکون',
    ],

    'logo_help' => [
        'email_logo_size'       => 'لوگوهای مربعی شکل در ایمیل بهترین به نظر می رسند.
',
    ],

    'logo_option_types' => [
        'text' => 'Text',
        'logo' => 'لوگو',
        'logo_and_text' => 'Logo and Text',
    ],


    'legends' => [
        'checkin' => 'Checkin Preferences',
        'colors' => 'Colors & Skins',
        'dashboard' => 'Login & Dashboard Preferences',
        'email' => 'Email Preferences',
        'eula' => 'EULA & Acceptance Preferences',
        'footer' => 'Footer Preferences',
        'formats' => 'Default Formats',
        'general' => 'General',
        'intervals' => 'Intervals & Thresholds',
        'logos' => 'Logos & Display',
        'mapping' => 'LDAP Field Mapping',
        'test' => 'Test LDAP Connection',
        'misc' => 'Miscellaneous',
        'misc_display' => 'Miscellaneous Display Options',
        'profiles' => 'User Profiles',
        'server' => 'Server Settings',
        'scoping' => 'Scoping',
        'security' => 'Security Preferences',
    ],


    /* Keywords for settings overview help */
    'keywords' => [
        'brand'             => 'پاورقی، لوگو، چاپ، تم، پوسته، هدر، رنگ ها، رنگ، css
',
        'general_settings'  => 'company support, signature, acceptance, email format, username format, images, per page, thumbnail, eula, gravatar, tos, dashboard, privacy',
        'groups'            => 'مجوزها، گروه‌های مجوز، مجوزها
',
        'labels'            => 'labels, barcodes, barcode, sheets, print, upc, qr, 1d, 2d',
        'localization'      => 'محلی سازی، واحد پول، محلی، منطقه، منطقه زمانی، منطقه زمانی، بین المللی، بین المللی، زبان، زبان ها، ترجمه
',
        'php_overview'      => 'phpinfo, system, info',
        'purge'             => 'برای همیشه حذف کنید
',
        'security'          => 'رمز عبور، رمزهای عبور، الزامات، دو عاملی، دو عاملی، رمزهای عبور رایج، ورود از راه دور، خروج از سیستم، احراز هویت
',
        'notifications'     => 'alerts, email, notifications, audit, threshold, email alerts, cc',
    ],

];
