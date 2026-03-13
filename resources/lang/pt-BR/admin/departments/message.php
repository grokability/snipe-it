<?php

return array(

    'does_not_exist' => 'Departamento não existe.',
    'department_already_exists' => 'Já existe um departamento com esse nome neste local da empresa. Ou escolha um nome mais específico para este departamento. ',
    'assoc_users'	 => 'Este departamento esta atualmente associado a pelo menos um usuário e não pode ser deletado. Por favor atualize seus usuários para não fazer mais referência a este departamento e tente novamente. ',
    'create' => array(
        'error'   => 'O departamento não foi criado, por favor tente novamente.',
        'success' => 'Departamento criado com sucesso.'
    ),
    'update' => array(
        'error'   => 'Departamento não foi atualizado, por favor, tente novamente',
        'success' => 'Departamento atualizado com sucesso.'
    ),
    'delete' => array(
        'confirm'   	=> 'Tem certeza que deseja excluir este departamento?',
        'error'   => 'Houve um problema de exclusão no departamento. Por favor, tente novamente.',
        'success' => 'O departamento foi excluído com sucesso.'
    )

);
