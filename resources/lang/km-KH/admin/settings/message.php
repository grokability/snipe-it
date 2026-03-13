<?php

return [

    'update' => [
        'error'                 => 'កំហុសបានកើតឡើងខណៈពេលកំពុងធ្វើបច្ចុប្បន្នភាព។ ',
        'success'               => 'បានធ្វើបច្ចុប្បន្នភាពការកំណត់ដោយជោគជ័យ។',
    ],
    'backup' => [
        'delete_confirm'        => 'តើអ្នកប្រាកដថាចង់លុបឯកសារបម្រុងទុកនេះទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។ ',
        'file_deleted'          => 'ឯកសារបម្រុងទុកត្រូវបានលុបដោយជោគជ័យ។ ',
        'generated'             => 'ឯកសារបម្រុងទុកថ្មីត្រូវបានបង្កើតដោយជោគជ័យ។',
        'file_not_found'        => 'ឯកសារបម្រុងទុកនោះមិនអាចត្រូវបានរកឃើញនៅលើម៉ាស៊ីនមេទេ។',
        'restore_warning'       => 'បាទ ស្ដារវាឡើងវិញ។ ខ្ញុំទទួលស្គាល់ថាវានឹងសរសេរជាន់លើទិន្នន័យដែលមានស្រាប់ណាមួយនាពេលបច្ចុប្បន្ននៅក្នុងមូលដ្ឋានទិន្នន័យ។ វាក៏នឹងចេញពីអ្នកប្រើប្រាស់ដែលមានស្រាប់របស់អ្នកទាំងអស់ (រួមទាំងអ្នក)',
        'restore_confirm'       => 'តើ​អ្នក​ប្រាកដ​ថា​អ្នក​ចង់​ស្ដារ​មូលដ្ឋាន​ទិន្នន័យ​របស់​អ្នក​ពី :filename?'
    ],
    'restore' => [
        'success'               => 'ការបម្រុងទុកប្រព័ន្ធរបស់អ្នកត្រូវបានស្ដារឡើងវិញ។ សូមចូលម្តងទៀត។'
    ],
    'purge' => [
        'error'     => 'កំហុសបានកើតឡើងខណៈពេលកំពុងសម្អាត។ ',
        'validation_failed'     => 'ការបញ្ជាក់ការសម្អាតរបស់អ្នកមិនត្រឹមត្រូវទេ។ សូមវាយពាក្យ "DELETE" នៅក្នុងប្រអប់បញ្ជាក់។',
        'success'               => 'កំណត់ត្រាដែលបានលុបត្រូវបានសម្អាតដោយជោគជ័យ។',
    ],
    'mail' => [
        'sending' => 'កំពុងផ្ញើអ៊ីមែលសាកល្បង...',
        'success' => 'បាន​ផ្ញើ​សំបុត្រ!',
        'error' => 'សំបុត្រមិនអាចផ្ញើបានទេ។',
        'additional' => 'គ្មាន​សារ​បញ្ហា​បន្ថែម​ត្រូវ​បាន​ផ្ដល់​ឱ្យ​។ ពិនិត្យការកំណត់សំបុត្ររបស់អ្នក និងកំណត់ហេតុកម្មវិធីរបស់អ្នក។'
    ],
    'ldap' => [
        'testing' => 'កំពុងសាកល្បងការតភ្ជាប់ LDAP Binding និង Query...',
        '500' => '500 កំហុសម៉ាស៊ីនមេ។ សូមពិនិត្យមើលកំណត់ហេតុម៉ាស៊ីនមេរបស់អ្នកសម្រាប់ព័ត៌មានបន្ថែម។',
        'error' => 'មាន​អ្វីមួយ​មិន​ប្រក្រតី :(',
        'sync_success' => 'គំរូនៃអ្នកប្រើប្រាស់ 10 នាក់បានត្រឡប់ពីម៉ាស៊ីនមេ LDAP ដោយផ្អែកលើការកំណត់របស់អ្នក៖',
        'testing_authentication' => 'កំពុងសាកល្បងការផ្ទៀងផ្ទាត់ LDAP...',
        'authentication_success' => 'អ្នកប្រើប្រាស់បានផ្ទៀងផ្ទាត់ភាពត្រឹមត្រូវប្រឆាំងនឹង LDAP ដោយជោគជ័យ!'
    ],
    'labels' => [
        'null_template' => 'Label template not found. Please select a template.',
        ],
    'webhook' => [
        'sending' => 'កំពុងផ្ញើ៖ សារសាកល្បងកម្មវិធី...',
        'success' => 'ការរួមបញ្ចូល៖ webhook_name របស់អ្នកដំណើរការ!',
        'success_pt1' => 'ជោគជ័យ! ពិនិត្យ ',
        'success_pt2' => ' ឆានែលសម្រាប់សារសាកល្បងរបស់អ្នក ហើយត្រូវប្រាកដថាចុច រក្សាទុកខាងក្រោម ដើម្បីរក្សាទុកការកំណត់របស់អ្នក។',
        '500' => '500 កំហុសម៉ាស៊ីនមេ។',
        'error' => 'មាន​អ្វីមួយ​មិន​ប្រក្រតី។ :app បានឆ្លើយតបជាមួយ៖ :error_message',
        'error_redirect' => 'កំហុស៖ 301/302៖ ចំណុចបញ្ចប់ត្រឡប់ការបញ្ជូនបន្ត។ សម្រាប់ហេតុផលសុវត្ថិភាព យើងមិនធ្វើតាមការបញ្ជូនបន្តទេ។ សូមប្រើចំណុចបញ្ចប់ពិតប្រាកដ។',
        'error_misc' => 'មាន​អ្វីមួយ​មិន​ប្រក្រតី។ :( ',
        'webhook_fail' => ' webhook notification failed: Check to make sure the URL is still valid.',
        'webhook_channel_not_found' => ' webhook channel not found.',
        'ms_teams_deprecation' => 'The selected Microsoft Teams webhook URL will be deprecated Dec 31st, 2025. Please use a workflow URL. Microsoft\'s documentation on creating a workflow can be found <a href="https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498" target="_blank"> here.</a>',
    ],
    'location_scoping' => [
        'not_saved' => 'Your settings were not saved.',
        'mismatch' => 'There is 1 item in the database that need your attention before you can enable location scoping.|There are :count items in the database that need your attention before you can enable location scoping.',
    ],
];
