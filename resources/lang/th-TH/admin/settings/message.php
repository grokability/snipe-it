<?php

return [

    'update' => [
        'error'                 => 'เกิด error ระหว่างการอัพเดตข้อมูล ',
        'success'               => 'อัพเดตการตั้งค่าเรียบร้อยแล้ว',
    ],
    'backup' => [
        'delete_confirm'        => 'คุณแน่ใจที่จะลบข้อมูลสำรองนี้? การดำเนินการนี้จะไม่สามารถกู้คืนได้ ',
        'file_deleted'          => 'ลบข้อมูลสำรองเรียบร้อยแล้ว ',
        'generated'             => 'ไฟล์ข้อมูลสำรองถูกสร้างเรียบร้อยแล้ว',
        'file_not_found'        => 'ไม่พบไฟล์ข้อมูลสำรองบนเซิฟเวอร์',
        'restore_warning'       => 'Yes, restore it. I acknowledge that this will overwrite any existing data currently in the database. This will also log out all of your existing users (including you).',
        'restore_confirm'       => 'Are you sure you wish to restore your database from :filename?'
    ],
    'restore' => [
        'success'               => 'Your system backup has been restored. Please log in again.'
    ],
    'purge' => [
        'error'     => 'เกิดข้อผิดพลาดขณะล้างข้อมูล',
        'validation_failed'     => 'การยืนยันการล้างข้อมูลของคุณไม่ถูกต้อง โปรดพิมพ์คำว่า "DELETE" ในช่องยืนยัน',
        'success'               => 'ล้างระเบียนเรียบร้อยแล้ว',
    ],
    'mail' => [
        'sending' => 'Sending Test Email...',
        'success' => 'Mail sent!',
        'error' => 'Mail could not be sent.',
        'additional' => 'No additional error message provided. Check your mail settings and your app log.'
    ],
    'ldap' => [
        'testing' => 'Testing LDAP Connection, Binding & Query ...',
        '500' => '500 Server Error. Please check your server logs for more information.',
        'error' => 'Something went wrong :(',
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
        'success_pt1' => 'Success! Check the ',
        'success_pt2' => ' channel for your test message, and be sure to click SAVE below to store your settings.',
        '500' => '500 Server Error.',
        'error' => 'Something went wrong. :app responded with: :error_message',
        'error_redirect' => 'ERROR: 301/302 :endpoint returns a redirect. For security reasons, we don’t follow redirects. Please use the actual endpoint.',
        'error_misc' => 'Something went wrong. :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
