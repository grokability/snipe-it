<?php

return [

    'field' => [
        'invalid' => 'Field tersebut tidak ada.',
        'already_added' => 'Field sudah di tambahkan',
        'none_selected' => 'Tidak ada kolom yang dipilih',

        'create' => [
            'error' => 'Field gagal di buat, silahkan coba kembali.',
            'success' => 'Field telah sukses di buat.',
            'assoc_success' => 'Field sukses di tambahkan ke fieldset.',
        ],

        'update' => [
            'error' => 'Field tidak terbaharui, silahkan coba kembali',
            'success' => 'Field sukses diperbarui.',
        ],

        'delete' => [
            'confirm' => 'Apakah Anda yakin untuk menghapus field ini?',
            'error' => 'Terdapat kesalahan pada saat penghapusan field ini. Silahkan coba kembali.',
            'success' => 'Field telah berhasil dihapus.',
            'in_use' => 'Field sedang digunakan.',
            'in_use_named' => ':name is still attached to at least one fieldset and cannot be deleted.',
            'bulk_success' => 'Field deleted successfully.|:count fields were deleted successfully.',
            'partial_success' => ':count field was deleted successfully, but others could not be deleted. See below for details.|:count fields were deleted successfully, but others could not be deleted. See below for details.',
        ],

    ],

    'fieldset' => [

        'does_not_exist' => 'Fieldset tidak ada',

        'create' => [
            'error' => 'Fieldset gagal di buat, silahkan coba kembali.',
            'success' => 'Fieldset telah sukses di buat.',
        ],

        'update' => [
            'error' => 'Fieldset tidak terbaharui, silahkan coba kembali',
            'success' => 'Fieldset sukses diperbarui.',
        ],

        'delete' => [
            'confirm' => 'Apakah Anda yakin untuk menghapus fieldset ini?',
            'error' => 'Terdapat kesalahan pada saat penghapusan fieldset ini. Silahkan coba kembali.',
            'success' => 'Fieldset telah berhasil dihapus.',
            'in_use' => 'Fieldset sedang digunakan.',
            'in_use_named' => ':name is still attached to at least one asset model and cannot be deleted.',
            'bulk_success' => 'Fieldset deleted successfully.|:count fieldsets were deleted successfully.',
            'partial_success' => ':count fieldset was deleted successfully, but others could not be deleted. See below for details.|:count fieldsets were deleted successfully, but others could not be deleted. See below for details.',
        ],

        'clone' => [
            'success' => 'Fieldset berhasil dikloning.',
        ],

    ],

    'fieldset_default_value' => [

        'error' => 'Terjadi kesalahan saat memvalidasi nilai default kelompok kolom.',

    ],

];
