<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Piegādātājs neeksistē.',


    'create' => array(
        'error'   => 'Piegādātājs netika izveidots, lūdzu, mēģiniet vēlreiz.',
        'success' => 'Piegādātājs veiksmīgi izveidots.'
    ),

    'update' => array(
        'error'   => 'Piegādātājs netika atjaunināts, lūdzu, mēģiniet vēlreiz',
        'success' => 'Piegādātājs ir veiksmīgi atjaunināts'
    ),

    'delete' => array(
        'confirm'   => 'Vai tiešām vēlaties dzēst šo piegādātāju?',
        'error'   => 'Radās problēma, izlaižot piegādātāju. Lūdzu mēģiniet vēlreiz.',
        'success' => 'Piegādātājs tika veiksmīgi dzēsts.',
        'assoc_assets'	 => 'Šis piegādātājs pašlaik ir saistīts ar :asset_count aktīvu(-iem), un to nevar dzēst. Lūdzu, atjauniniet savus aktīvus, lai tie vairs neatsauktos uz šo piegādātāju, tad mēģiniet vēlreiz. ',
        'assoc_licenses'	 => 'Šis piegādātājs pašlaik ir saistīts ar :licenses_count licenci(-ēm), un to nevar dzēst. Lūdzu, atjauniniet savas licences, lai tās vairs neatsauktos uz šo piegādātāju, tad mēģiniet vēlreiz. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
