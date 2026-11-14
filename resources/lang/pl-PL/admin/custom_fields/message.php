<?php

return [

    'field' => [
        'invalid' => 'Pole nie istnieje.',
        'already_added' => 'Pole już istnieje',
        'none_selected' => 'Nie wybrano żadnego pola',

        'create' => [
            'error' => 'Pole nie zostało utworzone. Spróbuj ponownie.',
            'success' => 'Pole utworzone pomyślnie.',
            'assoc_success' => 'Pole pomyślnie dadane do zestawu pól.',
        ],

        'update' => [
            'error' => 'Pole nie zostało zaktualizowane, spróbuj ponownie',
            'success' => 'Pole zaktualizowane pomyślnie.',
        ],

        'delete' => [
            'confirm' => 'Czy na pewno chcesz usunąć to pole?',
            'error' => 'Wystąpił błąd podczas usuwania pola. Spróbuj ponownie.',
            'success' => 'Pole zostało usunięte pomyślnie.',
            'in_use' => 'Pole jest wciąż w użytku.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Zestaw pól nie istnieje',

        'create' => [
            'error' => 'Zestaw pól nie został utworzony, spróbuj ponownie.',
            'success' => 'Zestaw pól utworzono pomyślnie.',
        ],

        'update' => [
            'error' => 'Zestaw pól nie został zaktualizowany, spróbuj ponownie',
            'success' => 'Zestaw pól zaktualizowany pomyślnie.',
        ],

        'delete' => [
            'confirm' => 'Czy na pewno chcesz usunąć ten zestaw pól?',
            'error' => 'Wystąpił błąd podczas usuwania zestawu pól. Spróbuj ponownie.',
            'success' => 'Zestaw pól usunięto pomyślnie.',
            'in_use' => 'Zestaw pól jest nadal w użyciu.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Błąd podczas sprawdzania domyślnych wartości zestawu pól.',

    ],

];
