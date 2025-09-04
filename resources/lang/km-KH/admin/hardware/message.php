<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'ទ្រព្យសកម្មមិនមានទេ។',
    'does_not_exist_var' => 'ទ្រព្យសកម្មដែលមានស្លាក៖ asset_tag រកមិនឃើញទេ។',
    'no_tag' 	         => 'មិនមានស្លាកទ្រព្យសម្បត្តិត្រូវបានផ្តល់ឱ្យទេ។',
    'does_not_exist_or_not_requestable' => 'ទ្រព្យសកម្មនោះមិនមានទេ ឬមិនអាចស្នើសុំបាន។',
    'assoc_users'	 	 => 'This asset is currently checked out to a user and cannot be deleted. Please check the asset in first, and then try deleting again. ',
    'warning_audit_date_mismatch' 	=> 'កាលបរិច្ឆេទសវនកម្មបន្ទាប់របស់ទ្រព្យសកម្មនេះ (:next_audit_date) គឺមុនកាលបរិច្ឆេទសវនកម្មចុងក្រោយ (:last_audit_date)។ សូមធ្វើបច្ចុប្បន្នភាពកាលបរិច្ឆេទសវនកម្មបន្ទាប់។',
    'labels_generated'   => 'ស្លាកត្រូវបានបង្កើតដោយជោគជ័យ។',
    'error_generating_labels' => 'កំហុសខណៈពេលបង្កើតស្លាក។',
    'no_assets_selected' => 'មិនបានជ្រើសរើសទ្រព្យសម្បត្តិទេ។',

    'create' => [
        'error'   		=> 'ទ្រព្យសកម្មមិនត្រូវបានបង្កើតទេ សូមព្យាយាមម្តងទៀត។ :(',
        'success' 		=> 'ទ្រព្យសកម្មត្រូវបានបង្កើតដោយជោគជ័យ។ :)',
        'success_linked' => 'ទ្រព្យសកម្មជាមួយស្លាក៖ ស្លាកត្រូវបានបង្កើតដោយជោគជ័យ។ <strong><a href=":link" style="color: white;">ចុចទីនេះដើម្បីមើល</a></strong>។',
        'multi_success_linked' => 'ទ្រព្យសកម្មជាមួយស្លាក៖ តំណត្រូវបានបង្កើតដោយជោគជ័យ។|: ទ្រព្យសម្បត្តិរាប់ត្រូវបានបង្កើតដោយជោគជ័យ។ ៖ តំណភ្ជាប់។',
        'partial_failure' => 'ទ្រព្យសកម្មមិនអាចបង្កើតបានទេ។ ហេតុផល៖ :failures|:រាប់ទ្រព្យសកម្មមិនអាចបង្កើតបានទេ។ មូលហេតុ៖៖ បរាជ័យ',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'ទ្រព្យសកម្មមិនត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ សូមព្យាយាមម្តងទៀត',
        'success' 			=> 'ទ្រព្យសកម្មបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។',
        'encrypted_warning' => 'Asset updated successfully, but encrypted custom fields were not due to permissions',
        'nothing_updated'	=>  'គ្មាន​វាល​ត្រូវ​បាន​ជ្រើស ដូច្នេះ​មិន​មាន​អ្វី​ត្រូវ​បាន​ធ្វើ​បច្ចុប្បន្នភាព​។',
        'no_assets_selected'  =>  'គ្មានទ្រព្យសម្បត្តិត្រូវបានជ្រើសរើស ដូច្នេះគ្មានអ្វីត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ។',
        'assets_do_not_exist_or_are_invalid' => 'ទ្រព្យសកម្មដែលបានជ្រើសរើសមិនអាចធ្វើបច្ចុប្បន្នភាពបានទេ។',
    ],

    'restore' => [
        'error'   		=> 'ទ្រព្យសកម្មមិនត្រូវបានស្ដារឡើងវិញទេ សូមព្យាយាមម្តងទៀត',
        'success' 		=> 'ទ្រព្យសកម្មត្រូវបានស្ដារឡើងវិញដោយជោគជ័យ។',
        'bulk_success' 		=> 'ទ្រព្យសកម្មត្រូវបានស្ដារឡើងវិញដោយជោគជ័យ។',
        'nothing_updated'   => 'គ្មានទ្រព្យសម្បត្តិត្រូវបានជ្រើសរើស ដូច្នេះគ្មានអ្វីត្រូវបានស្ដារឡើងវិញទេ។', 
    ],

    'audit' => [
        'error'   		=> 'សវនកម្មទ្រព្យសកម្មមិនជោគជ័យ៖ : error ',
        'success' 		=> 'សវនកម្មទ្រព្យសកម្មត្រូវបានកត់ត្រាដោយជោគជ័យ។',
    ],


    'deletefile' => [
        'error'   => 'ឯកសារមិនត្រូវបានលុបទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'ឯកសារត្រូវបានលុបដោយជោគជ័យ។',
    ],

    'upload' => [
        'error'   => 'ឯកសារ(ច្រើន) មិនត្រូវបានផ្ទុកឡើងទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'ឯកសារ(ច្រើន) ដែលបានបង្ហោះដោយជោគជ័យ។',
        'nofiles' => 'អ្នកមិនបានជ្រើសរើសឯកសារណាមួយសម្រាប់ផ្ទុកឡើង ឬឯកសារដែលអ្នកកំពុងព្យាយាមផ្ទុកឡើងមានទំហំធំពេក',
        'invalidfiles' => 'ឯកសារមួយ ឬច្រើនរបស់អ្នកមានទំហំធំពេក ឬជាប្រភេទឯកសារដែលមិនត្រូវបានអនុញ្ញាត។ ប្រភេទឯកសារដែលបានអនុញ្ញាតគឺ png, gif, jpg, doc, docx, pdf, និង txt ។',
    ],

    'import' => [
        'import_button'         => 'ដំណើរការនាំចូល',
        'error'                 => 'ធាតុមួយចំនួនមិនបាននាំចូលត្រឹមត្រូវ។',
        'errorDetail'           => 'ធាតុខាងក្រោមមិនត្រូវបាននាំចូលទេ ដោយសារមានកំហុស។',
        'success'               => 'ឯកសាររបស់អ្នកត្រូវបាននាំចូល',
        'file_delete_success'   => 'ឯកសាររបស់អ្នកត្រូវបានលុបដោយជោគជ័យ',
        'file_delete_error'      => 'ឯកសារមិនអាចលុបបានទេ។',
        'file_missing' => 'បាត់ឯកសារដែលបានជ្រើសរើស',
        'file_already_deleted' => 'ឯកសារដែលបានជ្រើសរើសត្រូវបានលុបរួចហើយ',
        'header_row_has_malformed_characters' => 'គុណលក្ខណៈមួយ ឬច្រើននៅក្នុងជួរបឋមកថាមានតួអក្សរ UTF-8 ខុសទម្រង់',
        'content_row_has_malformed_characters' => 'គុណលក្ខណៈមួយ ឬច្រើននៅក្នុងជួរទីមួយនៃមាតិកាមានតួអក្សរ UTF-8 ខុសទម្រង់',
        'transliterate_failure' => 'Transliteration from :encoding to UTF-8 failed due to invalid characters in input'
    ],


    'delete' => [
        'confirm'   	=> 'តើអ្នកប្រាកដថាចង់លុបទ្រព្យសម្បត្តិនេះទេ?',
        'error'   		=> 'មានបញ្ហាក្នុងការលុបទ្រព្យសម្បត្តិ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'assigned_to_error' => '{1}Asset Tag: :asset_tag is currently checked out. Check in this device before deletion.|[2,*]Asset Tags: :asset_tag are currently checked out. Check in these devices before deletion.',
        'nothing_updated'   => 'មិនមានទ្រព្យសម្បត្តិត្រូវបានជ្រើសរើស ដូច្នេះគ្មានអ្វីត្រូវបានលុបទេ។',
        'success' 		=> 'ទ្រព្យសកម្មត្រូវបានលុបដោយជោគជ័យ។',
    ],

    'checkout' => [
        'error'   		=> 'ទ្រព្យសកម្មមិនត្រូវបាន checked out ទេ សូមព្យាយាមម្តងទៀត',
        'success' 		=> 'ទ្រព្យសកម្មបាន checked out ដោយជោគជ័យ។',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'not_available' => 'ទ្រព្យ​សកម្ម​នោះ​មិន​អាច​ checkout បាន​ទេ!',
        'no_assets_selected' => 'អ្នកត្រូវតែជ្រើសរើសយ៉ាងហោចណាស់ទ្រព្យសកម្មមួយពីបញ្ជី',
    ],

    'multi-checkout' => [
        'error'   => 'Asset was not checked out, please try again|Assets were not checked out, please try again',
        'success' => 'Asset checked out successfully.|Assets checked out successfully.',
    ],

    'checkin' => [
        'error'   		=> 'ទ្រព្យសកម្មមិនត្រូវ checked in ទេ, សូមព្យាយាមម្តងទៀត',
        'success' 		=> 'ទ្រព្យសកម្មបាន checked in ជោគជ័យ។',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'already_checked_in'  => 'ទ្រព្យសកម្មនោះត្រូវ checked in រួចហើយ។',

    ],

    'requests' => [
        'error'   		=> 'Request was not successful, please try again.',
        'success' 		=> 'Request successfully submitted.',
        'canceled'      => 'Request successfully canceled.',
        'cancel'        => 'បោះបង់ការស្នើសុំធាតុនេះ។',
    ],

];
