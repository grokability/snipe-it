<?php

return [
    'does_not_exist' => 'Perusahaan tidak ada.',
    'deleted' => 'Company dihapus',
    'assoc_users' => 'This company is currently associated with at least one other item (location, user, license, asset, etc.) in Snipe-IT and cannot be deleted. Please update your models to no longer reference this company and try again. ',
    'create' => [
        'error' => 'Perusahaan belum terbuat, silahkan coba kembali.',
        'success' => 'Perusahaan telah berhasil dibuat.',
    ],
    'update' => [
        'error' => 'Perusahaan belum terperbaharui, silahkan coba kembali',
        'success' => 'Perusahaan telah berhasil di perbarui.',
    ],
    'delete' => [
        'confirm' => 'Apakah Anda yakin untuk menghapus perusahaan ini?',
        'error' => 'Terdapat kesalahan pada saat penghapusan perusahaan ini. Silahkan coba kembali.',
        'success' => 'Perusahaan telah berhasil dihapus.',
        'bulk_success' => 'Company deleted successfully.|:count companies were deleted successfully.',
        'partial_success' => 'Company deleted successfully. See additional information below. | :count companies were deleted successfully. See additional information below.',
    ],
    'webhook_fields_require_superuser' => 'Webhook configuration fields (:fields) can only be modified by a superuser.',
];
