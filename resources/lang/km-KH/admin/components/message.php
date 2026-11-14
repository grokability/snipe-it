<?php

return [

    'does_not_exist' => 'សមាសធាតុមិនមានទេ។',

    'create' => [
        'error' => 'សមាសធាតុមិនត្រូវបានបង្កើតទេ សូមព្យាយាមម្តងទៀត។',
        'success' => 'សមាសធាតុត្រូវបានបង្កើតដោយជោគជ័យ។',
    ],

    'update' => [
        'error' => 'សមាសធាតុមិនត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'សមាសធាតុត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'តើអ្នកប្រាកដថាចង់លុបសមាសភាគនេះទេ?',
        'error' => 'មាន​បញ្ហា​ក្នុង​ការ​លុប​សមាសភាគ។ សូមព្យាយាមម្តងទៀត។',
        'success' => 'សមាសភាគត្រូវបានលុបដោយជោគជ័យ។',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'សមាសធាតុមិនត្រូវបានប្រគល់អោយទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'សមាសធាតុត្រូវបានប្រគល់អោយដោយជោគជ័យ។',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'unavailable' => 'មិនមានសមាសធាតុគ្រប់គ្រាន់ទេ៖ :នៅសល់ដែលនៅសល់, :បានស្នើរសុំ ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'គ្មាន fields ត្រូវបានផ្លាស់ប្តូរ ដូច្នេះគ្មានអ្វីត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ។',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'សមាសធាតុមិនត្រូវបានប្រគល់មកវិញទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'សមាសធាតុបានប្រគល់មកវិញដោយជោគជ័យ។',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
    ],

];
