<?php

return [

    'field' => [
        'invalid' => 'Bidang tersebut tidak tersedia.',
        'already_added' => 'Bidang sudah ditambahkan',
        'none_selected' => 'No field selected',

        'create' => [
            'error' => 'Bidang gagal dibuat, silahkan coba lagi.',
            'success' => 'Kategori Berhasil dibuat.',
            'assoc_success' => 'Kategori berhasil ditambahkan ke fieldset.',
        ],

        'update' => [
            'error' => 'Kategori gagal diupdate, silahkan coba lagi',
            'success' => 'Kategori Berhasil dibuat.',
        ],

        'delete' => [
            'confirm' => 'Apakah Anda yakin ingin menghapus kategori ini?',
            'error' => 'Terjadi masalah saat menghapus kategori. Silahkan coba lagi.',
            'success' => 'Kategori berhasil dihapus.',
            'in_use' => 'Kategori masih digunakan.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Kategori tidak ada',

        'create' => [
            'error' => 'Kategori gagal dibuat, silahkan coba lagi.',
            'success' => 'Kategori Berhasil dibuat.',
        ],

        'update' => [
            'error' => 'Kategori gagal diupdate, silahkan coba lagi',
            'success' => 'Kategori Berhasil diperbarui.',
        ],

        'delete' => [
            'confirm' => 'Apakah Anda yakin ingin menghapus kategori ini?',
            'error' => 'Terjadi masalah saat menghapus kategori. Silahkan coba lagi.',
            'success' => 'Kategori berhasil dihapus.',
            'in_use' => 'Kategori masih digunakan.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset cloned successfully.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Error validating default fieldset values.',

    ],

];
