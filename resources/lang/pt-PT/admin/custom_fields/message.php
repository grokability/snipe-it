<?php

return [

    'field' => [
        'invalid' => 'Esse campo não existe.',
        'already_added' => 'Campo já adicionado',
        'none_selected' => 'Nenhum campo selecionado',

        'create' => [
            'error' => 'Campo não criado. Por favor tente novamente.',
            'success' => 'Campo criado com sucesso.',
            'assoc_success' => 'Campo adicionado ao conjunto de campos com sucesso.',
        ],

        'update' => [
            'error' => 'Campo não foi atualizado, por favor tente de novamente',
            'success' => 'Campo atualizado com sucesso.',
        ],

        'delete' => [
            'confirm' => 'Tem a certeza que deseja eliminar este campo?',
            'error' => 'Ocorreu um problema ao remover o campo. Por favor tente novamente.',
            'success' => 'O campo foi apagado com sucesso.',
            'in_use' => 'Campo continua em uso.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset não existe',

        'create' => [
            'error' => 'O Conjunto de campos não foi criado, por favor tente novamente.',
            'success' => 'Conjunto de campos criado com sucesso.',
        ],

        'update' => [
            'error' => 'Conjunto de campos não foi atualizado, por favor tente novamente',
            'success' => 'Conjunto de campos atualizado com sucesso.',
        ],

        'delete' => [
            'confirm' => 'Tem a certeza que deseja eliminar este conjunto de campos?',
            'error' => 'Ocorreu um problema ao apagar o conjunto de campos. Por favor tente novamente.',
            'success' => 'O conjunto de campos foi eliminado com sucesso.',
            'in_use' => 'Conjunto de campos continua em uso.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Erro ao validar valores padrão do conjunto de campos.',

    ],

];
