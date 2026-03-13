<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Nid yw\'r ased rydych yn ceisio perthnasu i\'r trwydded yma yn bodoli.',
    'owner_doesnt_match_asset' => 'Nid y defnyddiwr sydd wedi nodi yw perchenog yr ased rydych yn ceisio perthnasu ir trwydded yma.',
    'assoc_users'	 => 'Ar hyn o bryd mae\'r trwydded yma allan gan ddefnyddiwr ac ni ellir ei ddileu. Cofnodwch yr trwyddedyn ol i fewn yn gyntaf, ac yna ceisiwch ei ddileu eto. ',
    'select_asset_or_person' => 'Rhaid i chi ddewis ased neu defnyddiwr ond nid y ddau.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Ni crewyd y trwydded, ceisiwch eto o. g. y. dd.',
        'success' => 'Trwydded wedi creu yn llwyddiannus.'
    ),

    'deletefile' => array(
        'error'   => 'Ffeil heb ei ddileu. Ceisiwch eto o. g. y. dd.',
        'success' => 'Ffeil wedi dileu yn llwyddiannus.',
    ),

    'upload' => array(
        'error'   => 'Ffeil(iau) heb ei uwchlwytho. Ceisiwch eto o. g. y. dd.',
        'success' => 'Ffeil(iau) wedi uwchlwytho yn llwyddiannus.',
        'nofiles' => 'Ni wnaethoch chi ddewis unrhyw ffeiliau i\'w uwchlwytho, neu mae\'r ffeil rydych chi\'n ceisio ei huwchlwytho yn rhy fawr',
        'invalidfiles' => 'Mae un neu mwy o\'r ffeiliau unai yn rhy fawr neu ddim y math cywir. Derbynir png, gif, fjp, doc, docx, pdf a txt.',
    ),

    'update' => array(
        'error'   => 'Ni diweddarwyd y trwydded, ceisiwch eto o. g. y. dd',
        'success' => 'Trwydded wedi diweddaru\'n llwyddiannus.'
    ),

    'delete' => array(
        'confirm'   => 'Ydych chi\'n siwr eich bod eisiau dileu\'r trwydded yma?',
        'error'   => 'Nid oedd yn bosib dileu\'r trwydded. Ceisiwch eto o. g. y. dd.',
        'success' => 'Trwydded wedi dileu yn llwyddiannus.'
    ),

    'checkout' => array(
        'error'   => 'Nid oedd yn bosib nodi\'r trwydded allan. Ceisiwch eto o. g. y. dd.',
        'success' => 'Trwydded wedi nodi allan yn llwyddiannus',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Nid oedd yn bosib nodi\'r trwydded i mewn. Ceisiwch eto o. g. y. dd.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Trwydded wedi nodi i fewn yn llwyddiannus'
    ),

);
