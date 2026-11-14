<?php

return [

    'does_not_exist' => 'Componente não existe.',

    'create' => [
        'error' => 'Componente não foi criada, por favor tente de novo.',
        'success' => 'Componente criado com sucesso.',
    ],

    'update' => [
        'error' => 'Componente não foi atualizado, por favor tente de novo',
        'success' => 'Componente atualizado com sucesso.',
    ],

    'restore' => [
        'error' => 'Component was not restored, please try again',
        'success' => 'Component restored successfully.',
    ],

    'delete' => [
        'confirm' => 'Tem a certeza que deseja eliminar este componente?',
        'error' => 'Existe um problema ao eliminar o componente. Por favor tente de novo.',
        'success' => 'O componente foi eliminado com sucesso.',
        'error_qty' => 'Some components of this type are still checked out. Please check them in and try again.',
    ],

    'checkout' => [
        'error' => 'O componente não foi atribuido, por favor tente de novo',
        'success' => 'Componente atribuido com sucesso.',
        'user_does_not_exist' => 'O utilizador é invalido. Por favor tente de novo.',
        'unavailable' => 'Não há componentes suficientes restantes: :remaining remaining, :requested ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nenhum campo foi alterado, portanto, nada foi atualizado.',
        'skipped_unauthorized' => ':count component(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count component was updated.|:count components were updated.',
            'none_updated' => 'The selected component could not be updated. See errors below for details.|None of the selected components could be updated. See errors below for details.',
        ],

        'delete' => [
            'success' => ':count component was deleted.|:count components were deleted.',
            'partial_success' => ':count component was deleted. See errors below for the rest.|:count components were deleted. See errors below for the rest.',
        ],

        'restore' => [
            'success' => ':count component was restored.|:count components were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out components were selected.',
            'success' => ':unit_count unit returned across :count checkout.|:unit_count units returned across :count checkouts.',
        ],
    ],

    'checkin' => [
        'error' => 'O componente não foi devolvido, por favor tente de novo',
        'success' => 'O componente foi devolvido com sucesso.',
        'user_does_not_exist' => 'O utilizador é invalido. Por favor tente de novo.',
    ],

];
