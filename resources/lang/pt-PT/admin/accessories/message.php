<?php

return [

    'does_not_exist' => 'O acessório [:id] não existe.',
    'not_found' => 'Esse acessório não foi encontrado.',
    'assoc_users' => 'Esta acessório tem atualmente :count items alocados a utilizadores. Por favor, devolva-os e tente novamente. ',

    'create' => [
        'error' => 'Acessório não foi criado, por favor tente novamente.',
        'success' => 'Acessório criado com sucesso.',
    ],

    'update' => [
        'error' => 'Acessório não foi actualizado, por favor tente novamente',
        'success' => 'Acessório actualizado com sucesso.',
    ],

    'delete' => [
        'confirm' => 'Tem a certeza que pretende remover este acessório?',
        'error' => 'Ocorreu um problema ao remover o acessório. Por favor, tente novamente.',
        'success' => 'O acessório foi removido com sucesso.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessory was deleted successfully, but others could not be deleted. See below for details.|:count accessories were deleted successfully, but others could not be deleted. See below for details.',
    ],

    'checkout' => [
        'error' => 'O acessório não foi alocado. Por favor, tente novamente',
        'success' => 'Acessório alocado com sucesso.',
        'unavailable' => 'O acessório não está disponível para check-out. Verifique a quantidade disponível',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
        'checkout_qty' => [
            'lte' => 'Atualmente há apenas um acessório disponível deste tipo, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente. Existem :number_currently_remaining total accessoris disponíveis, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente.',
        ],

    ],

    'checkin' => [
        'error' => 'O acessório não foi devolvido. Por favor, tente novamente',
        'success' => 'Acessório devolvido com sucesso.',
        'user_does_not_exist' => 'O utilizador é inválido. Por favor, tente novamente.',
    ],

    'restore' => [
        'error' => 'Accessory was not restored, please try again',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nenhum campo foi alterado, portanto, nada foi atualizado.',
        'skipped_unauthorized' => ':count accessory(s) were skipped because you do not have permission to update them.',

        'update' => [
            'success' => ':count accessory was updated.|:count accessories were updated.',
            'none_updated' => 'The selected accessory could not be updated. See errors below for details.|None of the selected accessories could be updated. See errors below for details.',
        ],

        'restore' => [
            'success' => ':count accessory was restored.|:count accessories were restored.',
        ],

        'checkin_selected' => [
            'no_rows_selected' => 'No checked-out accessories were selected.',
            'success' => ':count accessory checkout was returned.|:count accessory checkouts were returned.',
        ],
    ],
];
