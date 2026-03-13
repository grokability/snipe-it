<?php

return array(

    'does_not_exist' => 'Aksesori [:id] tidak ada.',
    'not_found' => 'Aksesori tersebut tidak ditemukan.',
    'assoc_users'	 => 'Aksesori ini saat ini memiliki: count item yang dikeluarkan ke pengguna. Silakan periksa di aksesoris dan dan coba lagi. . Silakan periksa di aksesoris dan dan coba lagi. ',

    'create' => array(
        'error'   => 'Aksesori gagal di buat, mohon ulangi kembali.',
        'success' => 'Aksesori sukses di buat.'
    ),

    'update' => array(
        'error'   => 'Aksesori gagal terbaharui, mohon ulangi kembali',
        'success' => 'Aksesori sukses terbaharui.'
    ),

    'delete' => array(
        'confirm'   => 'Apakah anda yakin menghapus aksesori ini?',
        'error'   => 'Terdapat kesalahan pada saat penghapusan aksesori ini. Silahkan coba kembali.',
        'success' => 'Aksesori sukses terhapus.'
    ),

     'checkout' => array(
        'error'   		=> 'Aksesori ini belum dikeluarkan, silahkan coba kembali',
        'success' 		=> 'Aksesori telah berhasil dikeluarkan.',
        'unavailable'   => 'Aksesori tidak tersedia untuk dipinjam atau diambil. Periksa jumlah yang tersedia',
        'user_does_not_exist' => 'Terdapat kesalahan pada user ini. Silahkan coba kembali.',
         'checkout_qty' => array(
            'lte'  => 'Saat ini hanya ada satu aksesori jenis ini yang tersedia, dan Anda mencoba meminjam atau mengambil :checkout_qty. Harap sesuaikan jumlah pinjaman-pengambilan atau total stok aksesori ini dan coba lagi.|Terdapat :number_currently_remaining total aksesori yang tersedia, dan Anda mencoba meminjam atau mengambil :checkout_qty. Harap sesuaikan jumlah pinjaman-pengambilan atau total stok aksesori ini dan coba lagi.',
            ),
           
    ),

    'checkin' => array(
        'error'   		=> 'Aksesoris belum masuk, silahkan coba kembali',
        'success' 		=> 'Aksesoris telah berhasil dimasukkan.',
        'user_does_not_exist' => 'Terdapat kesalahan pada user ini. Silahkan coba kembali.'
    )


);
