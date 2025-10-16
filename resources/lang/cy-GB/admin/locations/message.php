<?php

return array(

    'does_not_exist' => 'Nid yw\'r lleoliad yn bodoli.',
    'assoc_users'    => 'This location is not currently deletable because it is the location of record for at least one item or user, has assets assigned to it, or is the parent location of another location. Please update your records to no longer reference this location and try again ',
    'assoc_assets'	 => 'Mae\'r lleoliad yma wedi perthnasu i oleiaf un ased a nid yw\'n bosib dileu. Diweddarwch eich asedau i beidio cyfeirio at y lleoliad yma ac yna ceisiwch eto. ',
    'assoc_child_loc'	 => 'Mae\'r lleoliad yma yn rhiant i oleiaf un lleoliad a nid yw\'n bosib dileu. Diweddarwch eich lleoliadau i beidio cyfeirio at y lleoliad yma ac yna ceisiwch eto. ',
    'assigned_assets' => 'Assigned Assets',
    'current_location' => 'Current Location',
    'open_map' => 'Open in :map_provider_icon Maps',
    'deleted_warning' => 'This location has been deleted. Please restore it before attempting to make any changes.',


    'create' => array(
        'error'   => 'Ni crewyd y lleoliad, ceisiwch eto o.g.y.dd.',
        'success' => 'Lleoliad wedi creu yn llwyddiannus.'
    ),

    'update' => array(
        'error'   => 'Ni diweddarwyd y lleoliad, ceisiwch eto o.g.y.dd',
        'success' => 'Lleoliad wedi diweddaru\'n llwyddiannus.'
    ),

    'restore' => array(
        'error'   => 'Location was not restored, please try again',
        'success' => 'Location restored successfully.'
    ),

    'delete' => array(
        'confirm'   	=> 'Ydych chi\'n siwr eich bod eisiau dileu\'r lleoliad yma?',
        'error'   => 'Nid oedd yn bosib dileu\'r lleoliad. Ceisiwch eto o.g.y.dd.',
        'success' => 'Lleoliad wedi dileu\'n llwyddiannus.'
    )

);
