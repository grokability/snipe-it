<?php

return array(

    'does_not_exist' => 'A categoria não existe.',
    'assoc_models'	 => 'Esta categoria está no momento associada a pelo menos um modelo e não pode ser excluída. Atualize seus modelos para não referenciarem mais esta categoria e tente novamente. ',
    'assoc_items'	 => 'Esta categoria está no momento associada a pelo menos um modelo e não pode ser excluída. Atualize seus :asset_type para não referenciarem mais esta categoria e tente novamente. ',

    'create' => array(
        'error'   => 'A categoria não foi criada. Tente novamente.',
        'success' => 'Categoria criada com sucesso.'
    ),

    'update' => array(
        'error'   => 'A categoria não foi atualizada, tente novamente',
        'success' => 'Categoria atualizada com sucesso.',
        'cannot_change_category_type'   => 'Você não pode alterar o tipo de categoria uma vez que ele foi criado',
    ),

    'delete' => array(
        'confirm'                => 'Tem certeza de que deseja excluir esta categoria?',
        'error'                  => 'Houve um problema ao excluir a categoria. Tente novamente.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
