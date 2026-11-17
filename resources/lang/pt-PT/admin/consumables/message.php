<?php

return [

    'invalid_category_type' => 'A categoria deve ser uma categoria de consumo.',
    'does_not_exist' => 'O consumível não existe.',

    'create' => [
        'error' => 'Não foi possível criar o consumível. Por favor, tente novamente.',
        'success' => 'Consumível criado com sucesso.',
    ],

    'update' => [
        'error' => 'Consumível não atualizado. Por favor, tente novamente',
        'success' => 'Consumível atualizado com sucesso.',
    ],

    'delete' => [
        'confirm' => 'Tem a certeza que deseja remover este consumível?',
        'error' => 'Ocorreu um problema ao remover o consumível. Por favor, tente novamente.',
        'success' => 'Consumível removido com sucesso.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'O consumível não foi alocado. Por favor, tente novamente',
        'success' => 'Consumível alocado com sucesso.',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
        'unavailable' => 'Não há consumíveis suficientes para esta entrega. Por favor, verifique a quantidade restante. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'O consumível não foi devolvido. Por favor, tente novamente',
        'success' => 'Consumível devolvido com sucesso.',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nenhum campo foi alterado, portanto, nada foi atualizado.',
        'skipped_unauthorized' => ':count consumable(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count consumable was updated.|:count consumables were updated.',
            'none_updated' => 'The selected consumable could not be updated. See errors below for details.|None of the selected consumables could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count consumable was restored.|:count consumables were restored.',
        ],
    ],
];
