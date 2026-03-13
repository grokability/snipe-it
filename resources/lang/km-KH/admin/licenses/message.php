<?php

return array(

    'does_not_exist' => 'អាជ្ញាប័ណ្ណមិនមានទេ ឬអ្នកមិនមានសិទ្ធិមើលវា។',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'ទ្រព្យសកម្មដែលអ្នកកំពុងព្យាយាមភ្ជាប់ជាមួយអាជ្ញាប័ណ្ណនេះមិនមានទេ។',
    'owner_doesnt_match_asset' => 'ទ្រព្យសកម្មដែលអ្នកកំពុងព្យាយាមភ្ជាប់ជាមួយអាជ្ញាប័ណ្ណនេះគឺជាកម្មសិទ្ធិរបស់នរណាម្នាក់ក្រៅពីបុគ្គលដែលបានជ្រើសរើសនៅក្នុងបញ្ជីទម្លាក់ចុះ។',
    'assoc_users'	 => 'This license is currently checked out to a user and cannot be deleted. Please check the license in first, and then try deleting again. ',
    'select_asset_or_person' => 'អ្នកត្រូវតែជ្រើសរើសទ្រព្យសកម្ម ឬអ្នកប្រើប្រាស់ ប៉ុន្តែមិនមែនទាំងពីរទេ។',
    'not_found' => 'រកមិនឃើញអាជ្ញាប័ណ្ណទេ។',
    'seats_available' => ': seat_count កៅអីមាន',


    'create' => array(
        'error'   => 'អាជ្ញាប័ណ្ណមិនត្រូវបានបង្កើតទេ សូមព្យាយាមម្តងទៀត។',
        'success' => 'អាជ្ញាប័ណ្ណត្រូវបានបង្កើតដោយជោគជ័យ។'
    ),

    'deletefile' => array(
        'error'   => 'ឯកសារមិនត្រូវបានលុបទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'ឯកសារត្រូវបានលុបដោយជោគជ័យ។',
    ),

    'upload' => array(
        'error'   => 'ឯកសារ (ច្រើន) មិនត្រូវបានផ្ទុកឡើងទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'ឯកសារ (ច្រើន) ដែលបានបង្ហោះដោយជោគជ័យ។',
        'nofiles' => 'អ្នកមិនបានជ្រើសរើសឯកសារណាមួយសម្រាប់ផ្ទុកឡើង ឬឯកសារដែលអ្នកកំពុងព្យាយាមផ្ទុកឡើងមានទំហំធំពេក',
        'invalidfiles' => 'ឯកសារមួយ ឬច្រើនរបស់អ្នកមានទំហំធំពេក ឬជាប្រភេទឯកសារដែលមិនត្រូវបានអនុញ្ញាត។ ប្រភេទឯកសារដែលបានអនុញ្ញាតគឺ png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, និង lic ។',
    ),

    'update' => array(
        'error'   => 'អាជ្ញាប័ណ្ណមិនត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'បានធ្វើបច្ចុប្បន្នភាពអាជ្ញាប័ណ្ណដោយជោគជ័យ។'
    ),

    'delete' => array(
        'confirm'   => 'តើអ្នកប្រាកដថាចង់លុបអាជ្ញាប័ណ្ណនេះទេ?',
        'error'   => 'មានបញ្ហាក្នុងការលុបអាជ្ញាប័ណ្ណ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'អាជ្ញាប័ណ្ណត្រូវបានលុបដោយជោគជ័យ។'
    ),

    'checkout' => array(
        'error'   => 'There was an issue checking out the license. Please try again.',
        'success' => 'The license was checked out successfully',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'There was an issue checking in the license. Please try again.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'The license was checked in successfully'
    ),

);
