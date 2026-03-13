<?php

return array(

    'does_not_exist' => 'គ្រឿងបន្លាស់ [:id] មិនមានទេ។',
    'not_found' => 'គ្រឿងបន្លាស់នោះមិនត្រូវបានរកឃើញទេ។',
    'assoc_users'	 => 'គ្រឿងបន្លាស់នេះបច្ចុប្បន្នមាន៖ រាប់ធាតុ checked out ដល់អ្នកប្រើប្រាស់។ សូមពិនិត្យមើលគ្រឿងបន្ថែម ហើយព្យាយាមម្តងទៀត។ ',

    'create' => array(
        'error'   => 'គ្រឿងបន្លាស់មិនត្រូវបានបង្កើតទេ សូមព្យាយាមម្តងទៀត។',
        'success' => 'គ្រឿងបន្លាស់ត្រូវបានបង្កើតដោយជោគជ័យ។'
    ),

    'update' => array(
        'error'   => 'គ្រឿងបន្លាស់មិនត្រូវបានអាប់ដេតទេ សូមព្យាយាមម្តងទៀត',
        'success' => 'គ្រឿងបន្លាស់ត្រូវបានអាប់ដេតដោយជោគជ័យ។'
    ),

    'delete' => array(
        'confirm'   => 'តើអ្នកប្រាកដថាចង់លុបគ្រឿងបន្លាស់នេះទេ?',
        'error'   => 'មានបញ្ហាក្នុងការលុបគ្រឿងបន្លាស់។ សូម​ព្យាយាម​ម្តង​ទៀត។',
        'success' => 'គ្រឿងបន្លាស់ត្រូវបានលុបដោយជោគជ័យ។'
    ),

     'checkout' => array(
        'error'   		=> 'គ្រឿងបន្លាស់មិនchecked outទេ សូមព្យាយាមម្តងទៀត',
        'success' 		=> 'គ្រឿងបន្លាស់ត្រូវchecked out ដោយជោគជ័យ។',
        'unavailable'   => 'គ្រឿងបន្លាស់មិនមានសម្រាប់ checkout ទេ។ ពិនិត្យបរិមាណដែលអាចប្រើបាន',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'គ្រឿងបន្លាស់មិនchecked inទេ សូមព្យាយាមម្តងទៀត',
        'success' 		=> 'Accessory checked in successfully.',
        'user_does_not_exist' => 'អ្នកប្រើប្រាស់នោះមិនត្រឹមត្រូវទេ។ សូម​ព្យាយាម​ម្តង​ទៀត។'
    )


);
