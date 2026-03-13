<?php

return [

    'does_not_exist' => 'Statuslabel bestaat niet.',
    'deleted_label' => 'Verwijderd status label',
    'assoc_assets'	 => 'Dit statuslabel is tenminste met één asset gekoppeld en kan niet verwijderd worden. Zorg ervoor dat assets geen gebruik maken van dit statuslabel en probeer het nogmaals. ',

    'create' => [
        'error'   => 'Statuslabel is niet aangemaakt, probeer het nogmaals.',
        'success' => 'Statuslabel succesvol aangemaakt.',
    ],

    'update' => [
        'error'   => 'Statuslabel is niet bijgewerkt, probeer het nogmaals',
        'success' => 'Statuslabel succesvol bijgewerkt.',
    ],

    'delete' => [
        'confirm'   => 'Weet je zeker dat je dit statuslabel wil verwijderen?',
        'error'   => 'Er is iets mis gegaan tijdens het verwijderen van het statuslabel, probeer het nogmaals.',
        'success' => 'Het statuslabel is succesvol verwijderd.',
    ],

    'help' => [
        'undeployable'   => 'Deze assets kunnen niet aan iemand worden toegewezen.',
        'deployable'   => 'Deze assets kunnen worden uitgecheckt. Zodra ze zijn toegewezen, nemen ze een meta-status van <i class="fas fa-circle text-blue"></i> <strong>Ingezet</strong>.',
        'archived'   => 'Deze assets kunnen niet uitgecheckt worden en worden alleen weergegeven in de gearchiveerde weergave. Dit is nuttig om informatie te bewaren over assets voor budgetteren/historische doeleinden, maar om deze buiten de dagelijkse asset-lijst te houden.',
        'pending'   => 'Deze assets kunnen nog niet aan iemand worden toegewezen, vaak gebruikt voor items die in reparatie zijn, maar naar verwachting zullen ze weer in omloop komen.',
    ],

];
