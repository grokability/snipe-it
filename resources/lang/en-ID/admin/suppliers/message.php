<?php

return array(

    'deleted' => 'Deleted supplier',
    'does_not_exist' => 'Pemasok tidak ada.',


    'create' => array(
        'error'   => 'Pemasok belum dibuat, silahkan coba lagi.',
        'success' => 'Pemasok berhasil dibuat.'
    ),

    'update' => array(
        'error'   => 'Pemasok tidak diperbarui, silakan coba lagi',
        'success' => 'Pemasok berhasil diperbarui.'
    ),

    'delete' => array(
        'confirm'   => 'Yakin ingin menghapus pemasok ini?',
        'error'   => 'Terjadi masalah saat menghapus pemasok. Silahkan coba lagi.',
        'success' => 'Pemasok berhasil dihapus.',
        'assoc_assets'	 => 'Pemasok saat ini terkait dengan :asset_count asset(s) aset dan tidak dapat dihapus. Harap perbarui aset Anda agar tidak lagi mereferensikan pemasok ini dan coba lagi. ',
        'assoc_licenses'	 => 'Saat ini Pemasok terkait dengan :licenses_count licences(s) dan tidak dapat dihapus. Harap perbarui lisensi Anda agar tidak lagi mereferensikan pemasok ini dan coba lagi. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
