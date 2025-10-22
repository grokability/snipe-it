<?php

return array(

    'does_not_exist' => 'Hindi umiiral ang mga kategoryang ito.',
    'assoc_models'	 => 'Ang kategorya ay kasalukuyang naiugnay sa hindi bumaba sa isang modelo at hindi maaaring mai-delete. Mangyaring i-update ang iyong mga modelo upang hindi na mag-reperens sa katergoryang ito at muling subukan. ',
    'assoc_items'	 => 'Ang kategoryang ito ay kasalukuyang naiugnay sa hindi bumaba sa isang : asset_type at hindi maaaring mai-delete. Mangyaring i-update ang iyong asset :asset_type para hindi na magreperens sa kategoryang ito at muling subukan. ',

    'create' => array(
        'error'   => 'Hindi naisagawa ang kategoryang ito, mangyaring subukang muli.',
        'success' => 'Matagumpay na naisagwa ang kategorya.'
    ),

    'update' => array(
        'error'   => 'Hindi na-update ang kategorya, mangyaring subukang muli',
        'success' => 'Matagumpay na nai-update ang kategorya.',
        'cannot_change_category_type'   => 'You cannot change the category type once it has been created',
    ),

    'delete' => array(
        'confirm'                => 'Sigurado kaba na gusto mong i-delete ang kategoryang ito?',
        'error'                  => 'Mayroong isyu sa pag-delete ng kategoryan. Mangyaring subukang muli.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
