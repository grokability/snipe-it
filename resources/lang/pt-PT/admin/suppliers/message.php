<?php

return array(

    'deleted' => 'Fornecedor excluído',
    'does_not_exist' => 'Fornecedor não existente.',


    'create' => array(
        'error'   => 'Não foi possível criar o Fornecedor, por favor tente novamente.',
        'success' => 'Fornecedor criado com sucesso.'
    ),

    'update' => array(
        'error'   => 'Não foi possível atualizar o Fornecedor, por favor tente novamente',
        'success' => 'Fornecedor atualizado com sucesso.'
    ),

    'delete' => array(
        'confirm'   => 'Tem a certeza que pretende remover este fornecedor?',
        'error'   => 'Ocorreu um problema ao remover este fornecedor. Por favor, tente novamente.',
        'success' => 'Fornecedor removido com sucesso.',
        'assoc_assets'	 => 'Este fornecedor esta atualmente associado a :asset_count artigo(s) e não pode ser eliminado. Por favor, atualize os artigos para que não referenciem este fornecedor e tente novamente.',
        'assoc_licenses'	 => 'Este fornecedor esta atualmente associado a :licenses_count licença(s) e não pode ser eliminado. Por favor, atualize as suas licenças para que não referenciem este fornecedor e tente novamente.',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
