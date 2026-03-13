<?php

return array(

    'deleted' => 'Modelo de ativo excluído',
    'does_not_exist' => 'O modelo não existe.',
    'no_association' => 'ATENÇÃO! O modelo de ativo para este item é inválido ou está faltando!',
    'no_association_fix' => 'Isso quebrará as coisas de maneiras estranhas e horríveis. Edite este equipamento agora para atribuir um modelo a ele.',
    'assoc_users'	 => 'Este modelo está no momento associado com um ou mais ativos e não pode ser excluído. Exclua os ativos e então tente excluir novamente. ',
    'invalid_category_type' => 'Esta categoria deve ser uma categoria de ativo.',

    'create' => array(
        'error'   => 'O modelo não foi criado, tente novamente.',
        'success' => 'Modelo criado com sucesso.',
        'duplicate_set' => 'Um modelo de ativo com este nome, desse fabricante e desse modelo já existe.',
    ),

    'update' => array(
        'error'   => 'O modelo não foi atualizado, tente novamente',
        'success' => 'Modelo atualizado com sucesso.',
    ),

    'delete' => array(
        'confirm'   => 'Tem certeza de que quer excluir este modelo de ativo?',
        'error'   => 'Houve um problema ao deletar o modelo. Por favor, tente novamente.',
        'success' => 'O modelo foi excluído com sucesso.'
    ),

    'restore' => array(
        'error'   		=> 'O modelo não foi restaurado, tente novamente',
        'success' 		=> 'Modelo restaurado com sucesso.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Nenhum campo foi alterado, então nada foi atualizado.',
        'success' 		=> 'Modelo foi atualizado com sucesso. |:model_count modelos atualizados com sucesso.',
        'warn'          => 'Você está prestes a atualizar as propriedades do seguinte modelo: Você está prestes a editar as propriedades dos seguintes :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Nenhum modelo foi selecionado, então nada foi deletado.',
        'success' 		    => 'Modelo excluído!|:success_count modelos deletados!',
        'success_partial' 	=> ':success_count model(s) foram deletados,no entando :fail_count não pode ser excluído porque eles ainda possuem ativos associados a eles.'
    ),

);
