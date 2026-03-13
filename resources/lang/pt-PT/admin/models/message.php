<?php

return array(

    'deleted' => 'Modelo de ativo apagado',
    'does_not_exist' => 'O Modelo não existe.',
    'no_association' => 'AVISO! O modelo de artigo para este item é inválido ou está em falta!',
    'no_association_fix' => 'Isto estragará as coisas de maneiras estranhas e horríveis. Edite este artigo agora para lhe atribuir um modelo.',
    'assoc_users'	 => 'Este modelo está atualmente associado com pelo menos um artigo e não pode ser removido. Por favor, remova os artigos e depois tente novamente. ',
    'invalid_category_type' => 'This category must be an asset category.',

    'create' => array(
        'error'   => 'O Modelo não foi criado. Por favor tente novamente.',
        'success' => 'Modelo criado com sucesso.',
        'duplicate_set' => 'Já existe um Modelo de artigo com esse nome, fabricante e número de modelo.',
    ),

    'update' => array(
        'error'   => 'O Modelo não foi atualizado. Por favor tente novamente',
        'success' => 'Modelo atualizado com sucesso.',
    ),

    'delete' => array(
        'confirm'   => 'Tem a certeza que pretende remover este modelo de artigo?',
        'error'   => 'Ocorreu um problema ao remover o modelo. Por favor, tente novamente.',
        'success' => 'O modelo foi removido com sucesso.'
    ),

    'restore' => array(
        'error'   		=> 'O Modelo não foi restaurado, por favor tente novamente',
        'success' 		=> 'Modelo restaurado com sucesso.'
    ),

    'bulkedit' => array(
        'error'   		=> 'Nenhum campo foi alterado, portanto, nada foi atualizado.',
        'success' 		=> 'Modelo foi atualizado com sucesso. |:model_count modelos atualizados com sucesso.',
        'warn'          => 'Você está prestes a atualizar as propriedades do seguinte modelo: Você está prestes a editar as propriedades dos seguintes :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'Nenhum modelo selecionado, por isso nenhum modelo foi eliminado.',
        'success' 		    => 'Modelo apagado!|:success_count modelos apagados!',
        'success_partial' 	=> ':sucess_count modelo(s) eliminados, no entanto :fail_count não foram eliminados, porque ainda têm artigos associados.'
    ),

);
