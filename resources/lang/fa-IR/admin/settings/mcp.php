<?php

return [
    'nav_label' => 'MCP',
    'approved_oauth_clients' => 'کلاینت‌های OAuth تأییدشده',
    'pending_oauth_clients' => 'کلاینت‌های OAuth در انتظار تأیید',
    'show_revoked_toggle_show' => 'نمایش باطل‌شده‌ها',
    'show_revoked_toggle_hide' => 'پنهان‌سازی باطل‌شده‌ها',

    // Settings page.
    'settings_title' => 'پروتکل زمینه مدل (MCP)',
    'settings_intro' => 'کلاینت‌های OAuth که از طریق ثبت پویا (DCR) ثبت‌نام می‌کنند، چنانچه نشانی بازگشت (Redirect URL) آن‌ها یک آدرس محلی (<code>localhost</code>، <code>127.0.0.1</code>) یا یک طرح‌واره URI مورد اعتماد نباشد، در صف انتظار زیر قرار می‌گیرند. کلاینت‌های در انتظار نمی‌توانند فرایند OAuth را تا زمانی که شما آن‌ها را تأیید کنید تکمیل نمایند. کلاینت‌های ایجادشده توسط مدیر و ثبت‌نام‌های دارای تأیید خودکار مستقیماً به برگه «تأییدشده» منتقل می‌شوند.',

    'trusted_schemes_label' => 'طرح‌واره‌های سفارشی URI مورد اعتماد',
    'trusted_schemes_help' => 'یک طرح‌واره در هر خط (به عنوان مثال: <code>com.example.myapp</code>). برنامه‌های تلفن همراهی که با طرح‌واره‌ای در این فهرست ثبت شوند، از صف انتظار عبور می‌کنند. برنامه رسمی تلفن همراه Snipe-IT همیشه مورد اعتماد است. خطوطی که با # شروع می‌شوند به عنوان توضیح (Comment) در نظر گرفته می‌شوند.',
    'trusted_domains_label' => 'پیشوندهای دامنه بازگشت مورد اعتماد',
    'trusted_domains_help' => 'یک پیشوند (http(s در هر خط (به عنوان مثال: <code>https://client.example.com</code>). هر URI بازگشتی که با یکی از این پیشوندها شروع شود، از صف انتظار عبور می‌کند. آدرس‌های محلی (localhost، 127.0.0.1، [::1]) همیشه مورد اعتماد هستند. خطوطی که با # شروع می‌شوند به عنوان توضیح در نظر گرفته می‌شوند.',
    'trusted_lists_saved' => 'فهرست‌های مورد اعتماد ذخیره شدند.',

    // Pending queue.
    'pending_header' => 'کلاینت‌های OAuth در انتظار تأیید',
    'pending_empty' => 'هیچ کلاینت OAuth در انتظار تأیید نیست.',
    'pending_col_name' => 'نام کلاینت',
    'pending_col_redirect' => 'URI بازگشت',
    'pending_col_registered_ip' => 'IP ثبت‌شده',
    'pending_col_registered_at' => 'تاریخ ثبت',
    'pending_col_actions' => 'اقدامات',
    'pending_action_approve' => 'تأیید',
    'pending_action_reject' => 'رد کردن',
    'pending_action_reject_reason_placeholder' => 'دلیل اختیاری برای رد کردن',

    'approved_success' => 'کلاینت OAuth تأیید شد.',
    'rejected_success' => 'کلاینت OAuth رد شد.',
    'action_requires_superuser' => 'فقط کاربران ارشد (Superusers) می‌توانند کلاینت‌های OAuth را تأیید یا رد کنند.',
    'client_not_found' => 'آن کلاینت OAuth یافت نشد.',

    // Reject modal.
    'reject_modal_title' => 'رد کلاینت OAuth',
    'reject_modal_intro' => 'رد کلاینت از تکمیل هرگونه فرایند OAuth در آینده توسط آن جلوگیری می‌کند. اگر کلاینت توسط شخص معتبری ثبت شده است، دلیل رد کردن را قید کنید تا از علت آن مطلع شود.',
    'reject_modal_reason_label' => 'دلیل (اختیاری)',

    // End-user-facing messages when a pending/rejected client's
    // authorize link is opened or token exchange is attempted.
    'client_not_approved_title' => 'کلاینت OAuth تأیید نشده است',
    'client_not_approved_name' => 'نام کلاینت:',
    'client_not_approved_redirect' => 'URI بازگشت:',
    'client_pending_message' => 'این کلاینت OAuth در انتظار تأیید مدیر سیستم است. در صورتی که انتظار داشتید این برنامه تأیید شده باشد، با مدیر اسنایپ‌آی‌تی خود تماس بگیرید.',
    'client_rejected_message' => 'این کلاینت OAuth توسط مدیر رد شده است و دیگر قابل استفاده نیست.',

    // MCP consent screen callout + REST API rejection message.
    'consent_redirect_warning' => 'پس از تأیید، شما به این نشانی هدایت خواهید شد. اگر آن را نمی‌شناسید، ادامه ندهید.',
    'rest_api_rejects_mcp_only_token' => 'این توکن OAuth فقط برای استفاده در MCP محدود شده است و نمی‌تواند برای REST API استفاده شود.',

    // AlertMenu entry.
    'alert_menu_pending_count_one' => ':count کلاینت OAuth در انتظار تأیید شماست',
    'alert_menu_pending_count_many' => ':count کلاینت OAuth در انتظار تأیید شما هستند',

    'dashboard_tile_label' => 'کلاینت‌های OAuth در انتظار تأیید',

    // Email notification fired to active superusers when a new DCR-
    // registered client lands in the pending queue.
    'notify_pending_subject' => 'یک کلاینت OAuth جدید در انتظار تأیید شما است',
    'notify_pending_greeting' => 'سلام،',
    'notify_pending_intro' => 'یک کلاینت OAuth جدید به‌صورت خودکار ثبت‌نام کرده است و برای تکمیل فرایند OAuth در انتظار بررسی شماست.',
    'notify_pending_client_name' => 'نام کلاینت:',
    'notify_pending_redirect' => 'URI بازگشت:',
    'notify_pending_ip' => 'ثبت‌شده از IP:',
    'notify_pending_action' => 'بررسی کلاینت‌های در انتظار',
    'notify_pending_outro' => 'اگر این کلاینت را نمی‌شناسید، آن را در کنسول مدیریت رد کنید.',

    // Email notification fired to other superusers when a peer has
    // just approved or rejected a pending client.
    'notify_action_approved_subject' => 'یک کلاینت OAuth تأیید شد',
    'notify_action_rejected_subject' => 'یک کلاینت OAuth رد شد',
    'notify_action_greeting' => 'سلام،',
    'notify_action_approved_intro' => ':actor یک کلاینت OAuth در انتظار را تأیید کرد.',
    'notify_action_rejected_intro' => ':actor یک کلاینت OAuth در انتظار را رد کرد.',
    'notify_action_client_name' => 'نام کلاینت:',
    'notify_action_redirect' => 'URI بازگشت:',
    'notify_action_reason' => 'دلیل:',
    'notify_action_action' => 'بررسی کلاینت‌های OAuth',
];
