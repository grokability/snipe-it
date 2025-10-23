<?php

return array(

    'does_not_exist' => 'カテゴリーが存在しません。',
    'assoc_models'	 => 'このカテゴリーは１つ以上の型番に関連付けられているため削除できません。このカテゴリーを参照しないようにモ型番を更新して再度実行してください。 ',
    'assoc_items'	 => 'このカテゴリーは１つ以上の資産に関連付けられているため削除できません。このカテゴリーを参照しないように資産を更新して、再度実行してください。 ',

    'create' => array(
        'error'   => 'カテゴリーが作成されていません。再度実行してください。',
        'success' => 'カテゴリーの作成に成功しました。'
    ),

    'update' => array(
        'error'   => 'カテゴリーは更新されませんでした。再度実行してください。',
        'success' => 'カテゴリーの更新に成功しました。',
        'cannot_change_category_type'   => '一度作成されたカテゴリタイプを変更することはできません',
    ),

    'delete' => array(
        'confirm'                => 'このカテゴリーを本当に削除しますか？',
        'error'                  => 'このカテゴリーを削除する際に問題がおきました。再度実行してください。',
        'success'                => 'Category was deleted successfully.',
        'bulk_success'           => 'Categories were deleted successfully.',
        'partial_success'        => 'Category deleted successfully. See additional information below. | :count categories were deleted successfully. See additional information below.',
    )

);
