<?php

return [

    'does_not_exist' => 'Rótulo de situação não existe.',
    'deleted_label' => 'Rótulo de situação excluído',
    'assoc_assets'	 => 'Este rótulo de situação está associado com pelo menos um Ativo e não pode ser removido. Por favor atualize seus ativos para não referenciarem este rótulo e tente novamente. ',

    'create' => [
        'error'   => 'Rótulo de situação não foi criado, por favor tente novamente.',
        'success' => 'Rótulo de situação criado com sucesso.',
    ],

    'update' => [
        'error'   => 'Rótulo de situação não foi atualizado, por favor tente novamente',
        'success' => 'Rótulo de situação atualizado com sucesso.',
    ],

    'delete' => [
        'confirm'   => 'Tem certeza que deseja deletar este Rótulo de Situação?',
        'error'   => 'Ocorreu um problema ao deletar o Rótulo de Situação. Por favor tente novamente.',
        'success' => 'O Rótulo de Situação foi deletado com sucesso.',
    ],

    'help' => [
        'undeployable'   => 'Esses ativos não podem ser atribuídos a ninguém.',
        'deployable'   => 'Esses ativos podem ser retirados. Uma vez que são retirados, eles assumirão ums Situação meta de <i class="fas fa-circle text-blue"></i> <strong>Implementado</strong>.',
        'archived'   => 'Esses ativos não podem ser verificados, e só aparecerão na visão arquivada. Isso é útil para manter informações sobre recursos para fins orçamentários / históricos, mas mantendo-os fora da lista de ativos do dia-a-dia.',
        'pending'   => 'Esses ativos ainda não podem ser atribuídos a ninguém, muitas vezes usado para itens que estão fora para reparo, mas é esperado que retornem à circulação.',
    ],

];
