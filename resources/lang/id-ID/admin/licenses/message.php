<?php

return array(

    'does_not_exist' => 'Lisensi tidak ada atau Anda tidak memiliki izin untuk melihatnya.',
    'user_does_not_exist' => 'Pengguna tidak ada atau Anda tidak memiliki izin untuk melihatnya.',
    'asset_does_not_exist' 	=> 'Aset yang hendak di asosiasikan dengan lisensi ini tidak ada.',
    'owner_doesnt_match_asset' => 'Aset yang hendak di asosiasikan dengan lisensi ini di miliki oleh seseorang yang tidak masuk dalam daftar.',
    'assoc_users'	 => 'Lisensi ini sudah diberikan kepada pengguna dan tidak dapat di hapus. Silahkan cek lisensi terlebih dahulu kemudian coba hapus kembali. ',
    'select_asset_or_person' => 'Anda harus memilih aset atau pengguna, namun tidak keduanya.',
    'not_found' => 'Berkas Lisensi tidak ditemukan',
    'seats_available' => ':seat_count slot lisensi tersedia',


    'create' => array(
        'error'   => 'Gagal membuat lisensi, silahkan coba kembali.',
        'success' => 'Sukses membuat lisensi.'
    ),

    'deletefile' => array(
        'error'   => 'Berkas belum terhapus. Silahkan coba kembali.',
        'success' => 'Berkas sukses di hapus.',
    ),

    'upload' => array(
        'error'   => 'Berkas belum terunggah. Silakan coba kembali.',
        'success' => 'Berkas sukses terunggah.',
        'nofiles' => 'Anda belum memilih berkas untuk di unggah, atau berkas yang akan di unggah terlalu besar ukurannya',
        'invalidfiles' => 'Satu atau lebih file Anda terlalu besar atau merupakan jenis filetype yang tidak diizinkan. Filetype yang diperbolehkan adalah png, gif, jpg, jpeg, doc, docx, pdf, txt, zip, rar, rtf, xml, dan lic.',
    ),

    'update' => array(
        'error'   => 'Gagal memperbarui lisensi, silahkan coba kembali',
        'success' => 'Sukses perbarui lisensi.'
    ),

    'delete' => array(
        'confirm'   => 'Apakah Anda yakin untuk menghapus lisensi ini?',
        'error'   => 'Terdapat kesalahan pada saat penghapusan lisensi ini. Silahkan coba kembali.',
        'success' => 'Lisensi telah berhasil dihapus.'
    ),

    'checkout' => array(
        'error'   => 'Terdapat kesalahan pada saat pemberian lisensi ini. Silahkan coba kembali.',
        'success' => 'Lisensi telah berhasil diberikan',
        'not_enough_seats' => 'Jumlah slot lisensi yang tersedia tidak mencukupi untuk dipinjam atau diambil',
        'mismatch' => 'Slot lisensi yang diberikan tidak cocok dengan lisensi',
        'unavailable' => 'Slot lisensi ini tidak tersedia untuk dipinjam atau diambil.',
        'license_is_inactive' => 'This license is expired or terminated.',
    ),

    'checkin' => array(
        'error'   => 'Terdapat kesalahan pada saat penerimaan lisensi ini. Silahkan coba kembali.',
        'not_reassignable' => 'Seat has been used',
        'success' => 'Lisensi telah berhasil diterima'
    ),

);
