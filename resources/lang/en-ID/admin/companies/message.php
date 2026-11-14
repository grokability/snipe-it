<?php

return [
    'does_not_exist' => 'Perusahaan tidak ada.',
    'deleted' => 'Deleted company',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Perusahaan tidak Tersimapn, Coba lagi.',
        'success' => 'Perusahaan sukses dibuat.',
    ],
    'update' => [
        'error' => 'Perusahaan tidak diperbaharui, coba lagi',
        'success' => 'Perusahaan sukses diperbaharui.',
    ],
    'delete' => [
        'confirm' => 'Anda yakin ingin menghapus perusahaan ini?',
        'error' => 'Ada masalah menghapus perusahaan. Silakan coba lagi.',
        'success' => 'Perusahaan berhasil dihapus.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
