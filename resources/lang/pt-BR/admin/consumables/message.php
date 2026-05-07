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
    ],

    'checkout' => [
        'error' => 'Consumível não foi verificado, por favor tente novamente',
        'success' => 'Realizada a saída do consumível com êxito.',
        'user_does_not_exist' => 'Esse usuário é inválido. Por favor, tente novamente.',
        'unavailable' => 'Não há consumíveis suficientes para este checkout. Por favor, verifique a quantidade restante. ',
    ],

    'checkin' => [
        'error' => 'Consumível não foi verificado, por favor tente novamente',
        'success' => 'Realizado o check-in do consumível com sucesso.',
        'user_does_not_exist' => 'Esse Usuário é inválido. Por favor, tente novamente.',
    ],

];
