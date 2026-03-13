<?php

return array(

    'does_not_exist' => 'A categoria não existe.',
    'assoc_models'	 => 'Esta categoria está associada a pelo menos um modelo e não pode ser apagada. Por favor atualize o modelo para não estar referenciado a esta categoria e tente de novo. ',
    'assoc_items'	 => 'Esta categoria está no momento associada a pelo menos um modelo e não pode ser excluída. Atualize os seus :asset_type para não referenciarem mais esta categoria e tente novamente. ',

    'create' => array(
        'error'   => 'A categoria não foi criada, por favor tenta novamente.',
        'success' => 'A categoria foi criada com sucesso.'
    ),

    'update' => array(
        'error'   => 'A categoria não foi actualizada, por favor tenta novamente',
        'success' => 'A categoria foi actualizada com sucesso.',
        'cannot_change_category_type'   => 'Não pode alterar o tipo de categoria depois de ter sido criado',
    ),

    'delete' => array(
        'confirm'                => 'Tens a certeza que queres eliminar esta categoria?',
        'error'                  => 'Houve um problema a eliminar a categoria. Por favor tenta novamente.',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
