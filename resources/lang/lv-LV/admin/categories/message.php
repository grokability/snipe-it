<?php

return array(

    'does_not_exist' => 'Kategorija nav.',
    'assoc_models'	 => 'Šobrīd šī kategorija ir saistīta ar vismaz vienu modeli, un to nevar izdzēst. Lūdzu, atjauniniet savus modeļus, lai vairs nerindrinātu šo kategoriju, un mēģiniet vēlreiz.',
    'assoc_items'	 => 'Šobrīd šī kategorija ir saistīta ar vismaz vienu: asset_type un to nevar izdzēst. Lūdzu, atjauniniet savu: asset_type, lai vairs nenorādītu šo kategoriju, un mēģiniet vēlreiz.',

    'create' => array(
        'error'   => 'Kategorija nav izveidota, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Kategorija veiksmīgi izveidota.'
    ),

    'update' => array(
        'error'   => 'Kategorija nav atjaunināta, lūdzu, mēģiniet vēlreiz',
        'success' => 'Kategorija ir veiksmīgi atjaunināta.',
        'cannot_change_category_type'   => 'You cannot change the category type once it has been created',
    ),

    'delete' => array(
        'confirm'                => 'Vai tiešām vēlaties dzēst šo kategoriju?',
        'error'                  => 'Radās problēma, dzēšot kategoriju. Lūdzu mēģiniet vēlreiz.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
