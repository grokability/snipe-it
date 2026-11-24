<?php

return [

    'does_not_exist' => 'Este acessório [:id] não existe.',
    'not_found' => 'Esse acessório não foi encontrado.',
    'assoc_users' => 'Este acessório tem atualmente :count itens alocado para os usuários. Por favor, verifique em acessórios e e tente novamente. ',

    'create' => [
        'error' => 'Acessório não criado, Por favor tente novamente.',
        'success' => 'Acessório criado com sucesso.',
    ],

    'update' => [
        'error' => 'Acessório não atualizado, Por favor tente novamente',
        'success' => 'Acessório atualizado com sucesso.',
    ],

    'delete' => [
        'confirm' => 'Tem certeza de que deseja excluir este acessório?',
        'error' => 'Ocorreu um problema ao remover o acessório. Por favor, tente novamente.',
        'success' => 'O acessório foi excluído com sucesso.',
        'bulk_success' => 'Accessory deleted successfully.|:count accessories were deleted successfully.',
        'partial_success' => ':count accessório foi excluído com sucesso, mas outros não puderam ser excluídos. Veja abaixo para mais detalhes.:count acessórios foram excluídos com sucesso, mas outros não puderam ser excluídos. Veja abaixo para mais detalhes.',
    ],

    'checkout' => [
        'error' => 'O acessório não foi alocado, por favor tente novamente',
        'success' => 'Acessório alocado com sucesso.',
        'unavailable' => 'Acessório não está disponível para saída. Verifique a quantidade disponível',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
        'checkout_qty' => [
            'lte' => 'Atualmente há apenas um acessório disponível deste tipo, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente. Existem :number_currently_remaining total accessoris disponíveis, e você está tentando conferir :checkout_qty. Por favor, ajuste a quantidade do check-out ou o estoque total deste acessório e tente novamente.',
        ],

    ],

    'checkin' => [
        'error' => 'O acessório não foi devolvido, por favor, tente novamente',
        'success' => 'Acessório devolvido com sucesso.',
        'user_does_not_exist' => 'Este usuário é inválido. Tente novamente.',
    ],

    'restore' => [
        'error' => 'Acessório não criado, Por favor tente novamente',
        'success' => 'Accessory restored successfully.',
    ],

    'bulk' => [
        'no_accessories_selected' => 'No accessories were selected.',
        'no_action_selected' => 'Please pick an action from the dropdown.',
        'no_fields_updated' => 'Nenhum campo foi alterado, então nada foi atualizado.',
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

        'checkout' => [
            'nothing_selectable' => 'None of the selected accessories can be checked out. Archived accessories and accessories with no remaining stock are excluded.',
            'excluded_notice' => 'The following accessories were excluded from this checkout because they are archived or have no remaining stock:',
            'skipped_archived' => ':name was skipped because it is archived.',
            'skipped_unavailable' => ':name was skipped because no units are currently available.',
            'skipped_company_mismatch' => ':name was skipped because it belongs to a different company (:item_company) than the selected target.',
            'summary' => ':checked_out of :total accessories were checked out.',
        ],
    ],
];
