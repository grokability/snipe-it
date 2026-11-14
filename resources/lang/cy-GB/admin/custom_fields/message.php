<?php

return [

    'field' => [
        'invalid' => 'Nid yw\'r maes yn bodoli.',
        'already_added' => 'Maes eisoes yn bodoli',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Ni crewyd y maes, ceisiwch eto o. g. y. dd.',
            'success' => 'Maes wedi creu yn llwyddiannus.',
            'assoc_success' => 'Maes wedi ychwanegu\'n llwyddiannus ir set maes.',
        ],

        'update' => [
            'error' => 'Ni diweddarwyd y maes, ceisiwch eto o. g. y. dd',
            'success' => 'Maes wedi diweddaru\'n llwyddiannus.',
        ],

        'delete' => [
            'confirm' => 'Ydych chi\'n siwr eich bod eisiau dileu\'r maes yma?',
            'error' => 'Nid oedd yn bosib dileu\'r maes. Ceisiwch eto o. g. y. dd.',
            'success' => 'Maes wedi dileu\'n llwyddiannus.',
            'in_use' => 'Maes mewn defnydd.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Nid yw\'r set maes yn bodoli',

        'create' => [
            'error' => 'Ni crewyd y set maes, ceisiwch eto o. g. y. dd.',
            'success' => 'Set maes wedi creu yn llwyddiannus.',
        ],

        'update' => [
            'error' => 'Ni diweddarwyd y set maes, ceisiwch eto o. g. y. dd',
            'success' => 'Set maes wedi diweddaru\'n llwyddiannus.',
        ],

        'delete' => [
            'confirm' => 'Ydych chi\'n siwr eich bod eisiau dileu\'r set maes yma?',
            'error' => 'Nid oedd yn bosib dileu\'r set maes. Ceisiwch eto o. g. y. dd.',
            'success' => 'Set maes wedi dileu\'n llwyddiannus.',
            'in_use' => 'Set maes mewn defnydd.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
