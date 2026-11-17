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
        'confirm' => 'Tem a certeza que deseja excluir este componente?',
        'error' => 'Existe um problema ao excluir o componente. Por favor tente de novo.',
        'success' => 'O componente foi excluído com sucesso.',
        'error_qty' => 'Alguns componentes deste tipo ainda estão registrados. Por favor, verifique-os e tente novamente.',
    ],

    'checkout' => [
        'error' => 'O componente não foi atribuido, por favor tente de novo',
        'success' => 'Componente atribuido com sucesso.',
        'user_does_not_exist' => 'Esse Usuário é inválido. Por favor, tente novamente.',
        'unavailable' => 'Não há componentes suficientes restantes: :remaining restante, :requested requerido ',
    ],

    'bulk' => [
        'no_components_selected' => 'No components were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nenhum campo foi alterado, então nada foi atualizado.',
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
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
    ],

];
