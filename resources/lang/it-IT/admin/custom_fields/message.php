<?php

return [

    'field' => [
        'invalid' => 'Il campo non esiste.',
        'already_added' => 'Campo già aggiunto',
        'none_selected' => 'Nessun campo selezionato',

        'create' => [
            'error' => 'Campo non creato, riprova.',
            'success' => 'Campo creato con successo.',
            'assoc_success' => 'Campo aggiunto correttamente al fieldset.',
        ],

        'update' => [
            'error' => 'Campo non aggiornato, riprova',
            'success' => 'Campo aggiornato con successo.',
        ],

        'delete' => [
            'confirm' => 'Si è sicuri di voler cancellare questo campo?',
            'error' => 'C\'è stato un problema cancellando il campo. Riprova.',
            'success' => 'Campo cancellato con successo.',
            'in_use' => 'Campo ancora in uso.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset non esiste',

        'create' => [
            'error' => 'Il Fieldset non è stato creato, riprova.',
            'success' => 'Fieldset creato con successo.',
        ],

        'update' => [
            'error' => 'Il Fieldset non è stato aggiornato, riprova',
            'success' => 'Fieldset aggiornato con successo.',
        ],

        'delete' => [
            'confirm' => 'Si è sicuri di voler cancellare questo fieldset?',
            'error' => 'C\'è stato un problema cancellando il fieldset. Riprova.',
            'success' => 'Fieldset cancellato con successo.',
            'in_use' => 'Fieldset ancora in uso.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Errore durante la convalida dei valori fieldset predefiniti.',

    ],

];
