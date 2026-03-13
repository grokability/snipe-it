<?php

return [

    'does_not_exist' => '狀態標籤不存在',
    'deleted_label' => 'Deleted Status Label',
    'assoc_assets'	 => '至少還有一個資產與此狀態標籤關聯，目前不能被删除，請確認後重試。 ',

    'create' => [
        'error'   => '新增狀態標籤失敗，請重試。',
        'success' => '新增狀態標籤成功。',
    ],

    'update' => [
        'error'   => '更新狀態標籤失敗，請重試。',
        'success' => '更新狀態標籤成功。',
    ],

    'delete' => [
        'confirm'   => '您確定要刪除此狀態標籤嗎？',
        'error'   => '刪除狀態標籤失敗，請重試。',
        'success' => '刪除狀態標籤成功。',
    ],

    'help' => [
        'undeployable'   => '這些資產不能分配給任何人。',
        'deployable'   => '這些資產可以被借出。一旦分配，它們將呈現 <i class="fas fa-circle text-blue"></i> <strong>已部署</strong> 的元狀態。',
        'archived'   => '這些資產無法簽出，只會顯示在“存檔”視圖中。這有助於保留有關資產的預算/歷史目的信息，但將其保留在日常資產清單之外。',
        'pending'   => '這些資產不能分配給任何人，經常用於修理的物品，但預計將重新流通。',
    ],

];
