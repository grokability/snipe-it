<?php

return [
    'custom_fields'		        => 'فیلدهای سفارشی',
    'manage'                    => 'مدیریت',
    'field'		                => 'فیلد',
    'about_fieldsets_title'		=> 'درباره ی تنظیمات فیلد',
    'about_fieldsets_text'		=> 'مجموعه‌های فیلد به شما امکان می‌دهند گروه‌هایی از فیلدهای سفارشی ایجاد کنید که اغلب برای انواع مدل دارایی خاص دوباره استفاده می‌شوند.
',
    'custom_format'             => 'فرمت Regex سفارشی...
',
    'encrypt_field'      	        => 'مقدار این فیلد را در پایگاه داده رمزگذاری کنید',
    'encrypt_field_help'      => 'هشدار: رمزگذاری یک فیلد باعث می شود آن را جستجو نکنید.',
    'encrypted'      	        => 'رمزگذاری شده',
    'fieldset'      	        => 'تنظیمات فیلد',
    'qty_fields'      	      => 'فیلد های Qty',
    'fieldsets'      	        => 'تنظیمات فیلد',
    'fieldset_name'           => 'نام تنظیمات فیلد',
    'field_name'              => 'نام فیلد',
    'field_values'            => 'ارزشهای میدان',
    'field_values_help'       => 'گزینه های انتخابی را اضافه کنید، در هر خط. خطوط خالی به غیر از خط اول نادیده گرفته می شوند.',
    'field_element'           => 'ایجاد یک عنصر',
    'field_element_short'     => 'عنصر',
    'field_format'            => 'شکل دادن',
    'field_custom_format'     => 'شکل دادن سفارشی',
    'field_custom_format_help'     => 'این فیلد اجازه می دهد تا شما از یک عبارت منظم( regex) برای اعتبار استفاده کنید. این باید با "regex:" شروع شود - برای مثال، برای تایید اینکه یک مقدار فیلد سفارشی شامل IMEI معتبر (15 رقم عددی) است، شما از <code>regex: / ^[0-9]{15}$ /</code> استفاده کنید.',
    'required'   		          => 'نیازمندی',
    'req'   		              => 'نیازمندی',
    'used_by_models'   		    => 'استفاده شده توسط مدل ها',
    'order'   		            => 'سفارش',
    'create_fieldset'         => 'تنظیمات فیلد جدید',
    'update_fieldset'         => 'Update Fieldset',
    'fieldset_does_not_exist'   => 'Fieldset :id does not exist',
    'fieldset_updated'         => 'Fieldset updated',
    'create_fieldset_title' => 'ایجاد یک عنصر جدید',
    'create_field'            => 'فیلد سفارشی جدید',
    'create_field_title' => 'یک فیلد سفارشی جدید ایجاد کنید
',
    'value_encrypted'      	        => 'The value of this field is encrypted in the database. Only users with permission to view encrypted custom fields will be able to view the decrypted value',
    'show_in_email'     => 'Include the value of this field in checkout emails sent to the user? Encrypted fields cannot be included in emails',
    'show_in_email_short' => 'Include in emails',
    'help_text' => 'متن راهنما',
    'help_text_description' => 'این متن اختیاری است که هنگام ویرایش یک دارایی برای ارائه زمینه در فیلد در زیر عناصر فرم ظاهر می شود.
',
    'about_custom_fields_title' => 'درباره فیلدهای سفارشی
',
    'about_custom_fields_text' => 'فیلدهای سفارشی به شما این امکان را می دهند که ویژگی های دلخواه را به دارایی ها اضافه کنید.
',
    'add_field_to_fieldset' => 'فیلد را به مجموعه فیلد اضافه کنید
',
    'make_optional' => 'مورد نیاز - کلیک کنید تا اختیاری شود
',
    'make_required' => 'اختیاری - کلیک کنید تا مورد نیاز است
',
    'reorder' => 'سفارش مجدد',
    'db_field' => 'فیلد DB
',
    'db_convert_warning' => 'هشدار. این فیلد در جدول فیلدهای سفارشی به عنوان <code>:db_column</code> است اما باید <code>: انتظار می رود</code> باشد.
',
    'is_unique' => 'این مقدار باید در همه دارایی ها منحصر به فرد باشد
',
    'unique' => 'منحصر بفرد
',
    'display_in_user_view' => 'Allow the checked out user to view these values in their View Assigned Assets page',
    'display_in_user_view_table' => 'Visible to User',
    'auto_add_to_fieldsets' => 'Automatically add this to every new fieldset',
    'add_to_preexisting_fieldsets' => 'Add to any existing fieldsets',
    'show_in_listview' => 'Show in list views by default. Authorized users will still be able to show/hide via the column selector',
    'show_in_listview_short' => 'Show in lists',
    'show_in_requestable_list_short' => 'Show in requestable assets list',
    'show_in_requestable_list' => 'Show value in requestable assets list. Encrypted fields will not be shown',
    'encrypted_options' => 'This field is encrypted, so some display options will not be available.',
    'display_checkin' => 'Display in checkin forms',
    'display_checkout' => 'Display in checkout forms',
    'display_audit' => 'Display in audit forms',
    'types' => [
        'text' => 'Text Box',
        'listbox' => 'List Box',
        'textarea' => 'Textarea (multi-line)',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio Buttons',
    ],
];
