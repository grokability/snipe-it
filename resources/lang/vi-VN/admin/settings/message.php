<?php

return [

    'update' => [
        'error'                 => 'Có lỗi xảy ra khi cập nhật. ',
        'success'               => 'Cập nhật cài đặt thành công.',
    ],
    'backup' => [
        'delete_confirm'        => 'Bạn có chắc chắn muốn xóa tệp sao lưu này? Hành động này không thể được hoàn tác.',
        'file_deleted'          => 'Tệp sao lưu đã được xoá thành công.',
        'generated'             => 'Một tập tin sao lưu mới được tạo thành công.',
        'file_not_found'        => 'Tập tin sao lưu không tìm thấy trên máy chủ.',
        'restore_warning'       => 'Yes, restore it. I acknowledge that this will overwrite any existing data currently in the database. This will also log out all of your existing users (including you).',
        'restore_confirm'       => 'Are you sure you wish to restore your database from :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'Đã xảy ra lỗi trong khi xóa.',
        'validation_failed'     => 'Xác nhận thanh lọc của bạn không chính xác. Vui lòng nhập từ "DELETE" vào hộp xác nhận.',
        'success'               => 'Đã xoá thành công hồ sơ thành công.',
    ],
    'mail' => [
        'sending' => 'Đang gửi email kiểm tra...',
        'success' => 'Đã gửi thư!',
        'error' => 'Không thể gửi được thư.',
        'additional' => 'No additional error message provided. Check your mail settings and your app log.'
    ],
    'ldap' => [
        'testing' => 'Testing LDAP Connection, Binding & Query ...',
        '500' => '500 Server Error. Please check your server logs for more information.',
        'error' => 'Đã xảy ra lỗi :(',
        'sync_success' => 'A sample of 10 users returned from the LDAP server based on your settings:',
        'testing_authentication' => 'Testing LDAP Authentication...',
        'authentication_success' => 'User authenticated against LDAP successfully!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'Sending :app test message...',
        'success' => 'Your :webhook_name Integration works!',
        'success_pt1' => 'Thành công! Kiểm tra tại ',
        'success_pt2' => ' channel for your test message, and be sure to click SAVE below to store your settings.',
        '500' => '500 Server Error.',
        'error' => 'Something went wrong. :app responded with: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => 'Đã xảy ra lỗi. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
