<?php

return array(

    'does_not_exist' => 'Nid yw\'r ategolyn [:id] yn bodoli.',
    'not_found' => 'That accessory was not found.',
    'assoc_users'	 => 'Mae\'r ategolyn yma hefo :count eitem wedi nodi allan i defnyddwyr. Nodwch yr ategolion yn ol i fewn ac yna ceisiwch eto. ',

    'create' => array(
        'error'   => 'Ni crewyd yr ategolyn, ceisiwch eto o.g.y.dd.',
        'success' => 'Ategolyn wedi creu yn llwyddiannus.'
    ),

    'update' => array(
        'error'   => 'Ni diweddarwyd yr ategolyn, ceisiwch eto o.g.y.dd',
        'success' => 'Diweddarwyd yr ategolyn yn llwyddiannus.'
    ),

    'delete' => array(
        'confirm'   => 'Ydych chi\'n sicr eich bod eisiau dileu\'r eitem hwn?',
        'error'   => 'Nid oedd yn bosib dileu\'r eitem. Ceisiwch eto o.g.y.dd.',
        'success' => 'Ategolyn wedi dileu yn llwyddiannus.'
    ),

     'checkout' => array(
        'error'   		=> 'Ategolyn heb ei nodi allan, ceisiwch eto o. g. y. dd',
        'success' 		=> 'Ategolyn wedi nodi allan yn llwyddiannus.',
        'unavailable'   => 'Accessory is not available for checkout. Check quantity available',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o.g.y.dd.',
         'checkout_qty' => array(
            'lte'  => 'There is currently only one available accessory of this type, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.|There are :number_currently_remaining total available accessories, and you are trying to check out :checkout_qty. Please adjust the checkout quantity or the total stock of this accessory and try again.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Nid oedd yn bosib nodi\'r ategolyn i fewn, ceisiwch eto o.g.y.dd',
        'success' 		=> 'Ategolyn wedi nodi i fewn yn llwyddiannus.',
        'user_does_not_exist' => 'Nid yw\'r defnyddiwr yna yn ddilys. Ceisiwch eto o.g.y.dd.'
    )


);
