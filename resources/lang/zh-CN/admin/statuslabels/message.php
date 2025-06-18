<?php

return [

    'does_not_exist' => '状态标签不存在',
    'deleted_label' => '已删除的状态标签',
    'assoc_assets'	 => '删除失败，该状态标签已与其它资产关联。请先更新资产以取消关联，然后重试。 ',

    'create' => [
        'error'   => '状态标签未被创建，请重试',
        'success' => '状态标签已成功创建',
    ],

    'update' => [
        'error'   => '状态标签未被更新，请重试',
        'success' => '状态标签已成功更新',
    ],

    'delete' => [
        'confirm'   => '你是否确认删除此状态标签？',
        'error'   => '删除状态标签发生错误，请重试',
        'success' => '状态标签删除成功。',
    ],

    'help' => [
        'undeployable'   => '这些资产不能分配给任何人。',
        'deployable'   => '这些资产可以被签出。一旦分配了它们，它们将成为状态<i class="fas fa-circle text-blue"></i> <strong>已分配</strong>。',
        'archived'   => '这些资产无法签出，只会显示在“存档”视图中。这有助于保留有关资产的预算/历史目的信息，但将其保留在日常资产清单之外。',
        'pending'   => '这些正在维修的资产还不能分配，但预计会重新投入使用。',
    ],

];
