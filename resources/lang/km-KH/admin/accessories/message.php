<?php

return [

    'does_not_exist' => 'គ្រឿងបន្លាស់ [:id] មិនមានទេ។',
    'not_found' => 'គ្រឿងបន្លាស់នោះមិនត្រូវបានរកឃើញទេ។',
    'assoc_users' => 'គ្រឿងបន្លាស់នេះបច្ចុប្បន្នមាន៖ រាប់ធាតុ checked out ដល់អ្នកប្រើប្រាស់។ សូមពិនិត្យមើលគ្រឿងបន្ថែម ហើយព្យាយាមម្តងទៀត។ ',

    'create' => [
        'error' => 'គ្រឿងបន្លាស់មិនត្រូវបានបង្កើតទេ សូមព្យាយាមម្តងទៀត។',
        'success' => 'គ្រឿងបន្លាស់ត្រូវបានបង្កើតដោយជោគជ័យ។',
    ],

    'update' => [
        'error' => 'គ្រឿងបន្លាស់មិនត្រូវបានអាប់ដេតទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'គ្រឿងបន្លាស់ត្រូវបានអាប់ដេតដោយជោគជ័យ។',
    ],

    'delete' => [
        'confirm' => 'តើអ្នកប្រាកដថាចង់លុបគ្រឿងបន្លាស់នេះទេ?',
        'error' => 'មានបញ្ហាក្នុងការលុបគ្រឿងបន្លាស់។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'គ្រឿងបន្លាស់ត្រូវបានលុបដោយជោគជ័យ។',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'គ្រឿងបន្លាស់មិនchecked outទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'គ្រឿងបន្លាស់ត្រូវchecked out ដោយជោគជ័យ។',
        'unavailable' => 'គ្រឿងបន្លាស់មិនមានសម្រាប់ checkout ទេ។ ពិនិត្យបរិមាណដែលអាចប្រើបាន',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'checkout_qty' => [
            'lte' => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
        ],

    ],

    'checkin' => [
        'error' => 'គ្រឿងបន្លាស់មិនchecked inទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'Accessory checked in successfully.',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'គ្មាន fields ត្រូវបានផ្លាស់ប្តូរ ដូច្នេះគ្មានអ្វីត្រូវបានធ្វើបច្ចុប្បន្នភាពទេ។',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
