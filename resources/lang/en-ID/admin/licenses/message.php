<?php

return array(

    'does_not_exist' => 'License does not exist or you do not have permission to view it.',
    'user_does_not_exist' => 'User does not exist or you do not have permission to view them.',
    'asset_does_not_exist' 	=> 'Aset yang ingin Anda kaitkan dengan lisensi ini tidak ada.',
    'owner_doesnt_match_asset' => 'Aset yang ingin Anda kaitkan dengan lisensi ini dimiliki oleh orang lain selain orang yang dipilih di dropdown yang ditugaskan.',
    'assoc_users'	 => 'Lisensi ini saat ini diperiksa oleh pengguna dan tidak dapat dihapus. Mohon periksa dulu lisensinya, lalu coba hapus lagi. ',
    'select_asset_or_person' => 'Anda harus memilih aset atau pengguna, namun tidak keduanya.',
    'not_found' => 'License not found',
    'seats_available' => ':seat_count seats available',


    'create' => array(
        'error'   => 'Lisensi gagal dibuat, silahkan coba lagi.',
        'success' => 'Lisensi Berhasil dibuat.'
    ),

    'deletefile' => array(
        'error'   => 'File tidak terhapus Silahkan coba lagi.',
        'success' => 'File berhasil dihapus.',
    ),

    'upload' => array(
        'error'   => 'Berkas(s) tidak diunggah. Silahkan coba lagi.',
        'success' => 'Berkas(s) berhasil diunggah.',
        'nofiles' => 'Anda tidak memilih file untuk diunggah, atau file yang ingin Anda unggah terlalu besar',
        'invalidfiles' => 'Satu atau lebih berkas anda terlalu besar atau jenis berkas tidak dibolehkan. Jenis berkas yang dibolehkan adalah png, gif, jpg, doc, docx, pdf, dan txt.',
    ),

    'update' => array(
        'error'   => 'Lisensi gagal dibuat, silahkan coba lagi',
        'success' => 'Lisensi Berhasil dibuat.'
    ),

    'delete' => array(
        'confirm'   => 'Apakah Anda yakin ingin menghapus lisensi ini?',
        'error'   => 'Terjadi masalah saat menghapus lisensi. Silahkan coba lagi.',
        'success' => 'Lisensi berhasil dihapus.'
    ),

    'checkout' => array(
        'error'   => 'Terjadi masalah saat menghapus lisensi. Silahkan coba lagi.',
        'success' => 'Lisensi berhasil diperiksa',
        'not_enough_seats' => 'Not enough license seats available for checkout',
        'mismatch' => 'The license seat provided does not match the license',
        'unavailable' => 'This seat is not available for checkout.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Terjadi masalah saat menghapus lisensi. Silahkan coba lagi.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Lisensi berhasil diperiksa'
    ),

);
