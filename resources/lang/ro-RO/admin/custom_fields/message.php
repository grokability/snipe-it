<?php

return [

    'field' => [
        'invalid' => 'Acest câmp nu există.',
        'already_added' => 'Câmpul deja adăugat',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Câmpul nu a fost creat, încercați din nou.',
            'success' => 'Câmpul a fost creat.',
            'assoc_success' => 'Câmpul a fost adăugat cu succes la setul de câmp',
        ],

        'update' => [
            'error' => 'Câmpul nu a fost actualizat, încercați din nou',
            'success' => 'Câmpul sa actualizat cu succes.',
        ],

        'delete' => [
            'confirm' => 'Sigur doriți să ștergeți acest câmp?',
            'error' => 'A apărut o problemă cu ștergerea câmpului. Vă rugăm să încercați din nou.',
            'success' => 'Câmpul a fost șters cu succes.',
            'in_use' => 'Câmpul este încă în uz.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Setul de câmp nu există',

        'create' => [
            'error' => 'Setul de câmp nu a fost creat, încercați din nou.',
            'success' => 'Setul de campuri a fost creat cu succes.',
        ],

        'update' => [
            'error' => 'Setul de câmp nu a fost actualizat, încercați din nou',
            'success' => 'Setul de câmpuri sa actualizat cu succes.',
        ],

        'delete' => [
            'confirm' => 'Sigur doriți să ștergeți acest set de câmpuri?',
            'error' => 'A apărut o problemă la ștergerea setului de câmpuri. Vă rugăm să încercați din nou.',
            'success' => 'Setul de câmpuri a fost șters cu succes.',
            'in_use' => 'Setul de câmp este încă în uz.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Eroare la validarea valorilor prestabilite de câmp.',

    ],

];
