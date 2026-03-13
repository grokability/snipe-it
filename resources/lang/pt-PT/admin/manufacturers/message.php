<?php

return array(

    'support_url_help' => 'Variáveis <code>{LOCALE}</code>, <code>{SERIAL}</code>, <code>{MODEL_NUMBER}</code>, e <code>{MODEL_NAME}</code> pode ser usado na URL para ter esses valores auto-preenchidos quando visualizando assets - por exemplo, https://checkcoverage. pple.com/{LOCALE}/{SERIAL}.',
    'does_not_exist' => 'O fabricante não existe.',
    'assoc_users'	 => 'O fabricante está atualmente associado com pelo menos um modelo e não pode ser removido. Atualize os modelos para que não referenciem mais este fabricante e tente novamente. ',

    'create' => array(
        'error'   => 'Não foi possível criar o fabricante, por favor tente novamente.',
        'success' => 'Fabricante criado com sucesso.'
    ),

    'update' => array(
        'error'   => 'O fabricante não foi atualizado. Por favor, tente novamente',
        'success' => 'Fabricante atualizado com sucesso.'
    ),

    'restore' => array(
        'error'   => 'Não foi possível restaurar o fabricante, por favor tente novamente',
        'success' => 'Fabricante restaurado com sucesso.'
    ),

    'delete' => array(
        'confirm' => 'Tem a certeza que pretende remover este fabricante?',
        'error'   => 'Ocorreu um problema ao remover este fabricante. Por favor, tente novamente.',
        'success'                => 'Manufacturer deleted successfully.',
        'bulk_success'           => 'Manufacturers deleted successfully.',
        'partial_success'        => 'Manufacturer deleted successfully. See additional information below. | :count manufacturers were deleted successfully. See additional information below.',
    )

);
