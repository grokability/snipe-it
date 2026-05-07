<?php

return [

    'does_not_exist' => 'A categoria não existe.',
    'assoc_models' => 'Esta categoria está no momento associada a pelo menos um modelo e não pode ser excluída. Atualize seus modelos para não referenciarem mais esta categoria e tente novamente. ',
    'assoc_items' => 'Esta categoria está no momento associada a pelo menos um modelo e não pode ser excluída. Atualize seus :asset_type para não referenciarem mais esta categoria e tente novamente. ',

    'create' => [
        'error' => 'A categoria não foi criada. Tente novamente.',
        'success' => 'Categoria criada com sucesso.',
    ],

    'update' => [
        'error' => 'A categoria não foi atualizada, tente novamente',
        'success' => 'Categoria atualizada com sucesso.',
        'cannot_change_category_type' => 'Você não pode alterar o tipo de categoria uma vez que ele foi criado',
    ],

    'delete' => [
        'confirm' => 'Tem certeza de que deseja excluir esta categoria?',
        'error' => 'Houve um problema ao excluir a categoria. Tente novamente.',
        'success' => 'A categoria foi excluída com sucesso.',
        'bulk_success' => 'As categorias foram excluídas com sucesso.',
        'partial_success' => 'Categorias excluídas com sucesso. Veja as informações adicionais abaixo. :count categorias foram excluídas com sucesso. Veja as informações adicionais abaixo.',
    ],

];
