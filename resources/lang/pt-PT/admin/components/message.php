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

    'checkin' => [
        'error' => 'O componente não foi devolvido, por favor tente de novo',
        'success' => 'O componente foi devolvido com sucesso.',
        'user_does_not_exist' => 'O utilizador é invalido. Por favor tente de novo.',
    ],

];
