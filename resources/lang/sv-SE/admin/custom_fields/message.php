<?php

return [

    'field' => [
        'invalid' => 'Fältet existerar inte.',
        'already_added' => 'Fältet har redan lagts till',
        'none_selected' => 'Inget fält valt',

        'create' => [
            'error' => 'Fältet kunde inte skapas. Vänligen försök igen.',
            'success' => 'Fält skapat.',
            'assoc_success' => 'Fältet lades till i fältuppsättningen.',
        ],

        'update' => [
            'error' => 'Fältet kunde inte uppdateras. Vänligen försök igen.',
            'success' => 'Fältet uppdaterat.',
        ],

        'delete' => [
            'confirm' => 'Är du säker på att du vill radera det här fältet?',
            'error' => 'Kunde inte ta bort fältet. Vänligen försök igen.',
            'success' => 'Fält raderat.',
            'in_use' => 'Fältet är fortfarande i bruk.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fältuppsättningen existerar inte',

        'create' => [
            'error' => 'Fältuppsättningen kunde inte skapas. Vänligen försök igen.',
            'success' => 'Fält skapat.',
        ],

        'update' => [
            'error' => 'Fältuppsättningen kunde inte uppdateras. Vänligen försök igen.',
            'success' => 'Fält uppdaterat.',
        ],

        'delete' => [
            'confirm' => 'Är du säker på att du vill radera det här fältet?',
            'error' => 'Det gick inte att ta bort fältet. Vänligen försök igen.',
            'success' => 'Fält raderat.',
            'in_use' => 'Fältuppsättningen är fortfarande i bruk.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Fel vid validering av standardvärden för för fältuppsättningen.',

    ],

];
