<?php

return [

    'invalid_category_type' => 'A categoria deve ser uma categoria de consumível.',
    'does_not_exist' => 'O consumível não existe.',

    'create' => [
        'error' => 'O Consumível não foi criado, por favor, tente novamente.',
        'success' => 'Consumível criado com sucesso.',
    ],

    'update' => [
        'error' => 'Consumível não foi atualizado, por favor tente novamente',
        'success' => 'Consumível atualizado com sucesso.',
    ],

    'delete' => [
        'confirm' => 'Você realmente quer apagar este suprimento?',
        'error' => 'Houve um problema ao excluir o consumível. Por favor, tente novamente.',
        'success' => 'O suprimento foi deletado com sucesso.',
        'bulk_success' => 'Consumable deleted successfully.|:count consumables were deleted successfully.',
        'partial_success' => ':count consumable was deleted successfully, but others could not be deleted. See below for details.|:count consumables were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'Consumível não foi verificado, por favor tente novamente',
        'success' => 'Realizada a saída do consumível com êxito.',
        'user_does_not_exist' => 'Esse usuário é inválido. Por favor, tente novamente.',
        'unavailable' => 'Não há consumíveis suficientes para este checkout. Por favor, verifique a quantidade restante. ',
    ],

    'consumed' => [
        'success' => 'Distribution marked as consumed.',
        'bulk_success' => ':count distribution was marked as consumed.|:count distributions were marked as consumed.',
        'already' => 'That distribution was already marked as consumed.',
        'error' => 'Could not mark the distribution as consumed, please try again.',
        'no_rows_selected' => 'No valid distributions were selected.',
    ],

    'checkin' => [
        'error' => 'Consumível não foi verificado, por favor tente novamente',
        'success' => 'Realizado o check-in do consumível com sucesso.',
        'user_does_not_exist' => 'Esse Usuário é inválido. Por favor, tente novamente.',
    ],

    'restore' => [
        'error' => 'Consumable was not restored, please try again',
        'success' => 'Consumable restored successfully.',
    ],

    'bulk' => [
        'no_consumables_selected' => 'No consumables were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nenhum campo foi alterado, então nada foi atualizado.',
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
